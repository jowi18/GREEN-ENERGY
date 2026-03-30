<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\InventoryService;
use App\Services\PayPalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    private const CART_KEY = 'customer_cart';

    public function __construct(
        private readonly InventoryService $inventory,
        private readonly PayPalService    $paypal,
    ) {}

    private function customer()
    {
        return auth()->user()->customer;
    }

    // ── Order list ────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $customer = $this->customer();

        $query = Order::with(['vendor', 'items'])
            ->forCustomer($customer->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->latest()->paginate(15)->withQueryString();

        return view('customer.orders.index', compact('orders'));
    }

    // ── Order detail ──────────────────────────────────────────────────────

    public function show(Order $order)
    {
        $this->authorizeOrder($order);
        $order->load(['vendor', 'items.product', 'delivery']);

        return view('customer.orders.show', compact('order'));
    }

    // ── Place order ───────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $data = $request->validate([
            'shipping_address_line1' => ['required', 'string', 'max:255'],
            'shipping_address_line2' => ['nullable', 'string', 'max:255'],
            'shipping_city'          => ['required', 'string', 'max:100'],
            'shipping_province'      => ['required', 'string', 'max:100'],
            'shipping_postal_code'   => ['required', 'string', 'max:20'],
            'payment_method'         => ['required', 'in:cash_on_delivery,paypal'],
            'notes'                  => ['nullable', 'string', 'max:1000'],
        ]);

        $cart = session(self::CART_KEY, []);

        if (empty($cart)) {
            return redirect()->route('customer.products.index')
                ->with('warning', 'Your cart is empty.');
        }

        $customer = $this->customer();

        // ── Build order in a transaction ──────────────────────────────────
        $order = DB::transaction(function () use ($cart, $data, $customer) {
            $subtotal = 0;
            $items    = [];
            $vendorId = null;

            foreach ($cart as $productId => $qty) {
                $product  = Product::with('inventory')->findOrFail($productId);
                $vendorId = $product->vendor_id;

                if (! $product->isService() && $product->inventory) {
                    $this->inventory->reserve($product->inventory, $qty);
                }

                $lineTotal = $product->price * $qty;
                $subtotal += $lineTotal;

                $items[] = compact('product', 'qty', 'lineTotal');
            }

            $order = Order::create([
                'customer_id'            => $customer->id,
                'vendor_id'              => $vendorId,
                'status'                 => 'pending',
                'subtotal'               => $subtotal,
                'discount_amount'        => 0,
                'shipping_fee'           => 0,
                'tax_amount'             => 0,
                'total_amount'           => $subtotal,
                'currency'               => 'PHP',
                'payment_method'         => $data['payment_method'],
                'payment_status'         => 'unpaid',
                'shipping_address_line1' => $data['shipping_address_line1'],
                'shipping_address_line2' => $data['shipping_address_line2'] ?? null,
                'shipping_city'          => $data['shipping_city'],
                'shipping_province'      => $data['shipping_province'],
                'shipping_postal_code'   => $data['shipping_postal_code'],
                'shipping_country'       => 'Philippines',
                'notes'                  => $data['notes'] ?? null,
            ]);

            foreach ($items as $item) {
                OrderItem::create([
                    'order_id'       => $order->id,
                    'product_id'     => $item['product']->id,
                    'product_name'   => $item['product']->name,
                    'quantity'       => $item['qty'],
                    'unit_price'     => $item['product']->price,
                    'discount_amount'=> 0,
                    'total_price'    => $item['lineTotal'],
                    'warranty_months'=> $item['product']->warranty_months,
                ]);
            }

            return $order;
        });

        // Clear cart — done before PayPal redirect so it doesn't persist
        session()->forget(self::CART_KEY);

        // ── COD: done, show order ──────────────────────────────────────────
        if ($data['payment_method'] === 'cash_on_delivery') {
            return redirect()
                ->route('customer.orders.show', $order)
                ->with('success', "Order #{$order->order_number} placed! Pay on delivery.");
        }

        // ── PayPal: create order and redirect ──────────────────────────────
        try {
            // Build line items for the PayPal order
            $lineItems = $order->items->map(fn ($item) => [
                'name'        => $item->product_name,
                'description' => "Qty {$item->quantity}",
                'quantity'    => $item->quantity,
                'price'       => $item->unit_price,
            ])->all();

            $result = $this->paypal->createPaymentWithItems(
                items:     $lineItems,
                currency:  'USD',  // PayPal sandbox requires USD
                returnUrl: route('customer.orders.paypal.success', ['order' => $order->id]),
                cancelUrl: route('customer.orders.paypal.cancel',  ['order' => $order->id]),
            );

            if (! $result['success']) {
                throw new \RuntimeException($result['message'] ?? 'PayPal order creation failed.');
            }

            // Store PayPal order ID for capture on return
            $order->update(['paypal_order_id' => $result['order_id']]);

            return redirect($result['approval_url']);

        } catch (\Exception $e) {
            Log::error('Customer PayPal order creation failed', [
                'order_id' => $order->id,
                'error'    => $e->getMessage(),
            ]);

            // Mark order as failed and restore stock
            DB::transaction(function () use ($order) {
                foreach ($order->items as $item) {
                    if ($item->product && ! $item->product->isService() && $item->product->inventory) {
                        $this->inventory->release($item->product->inventory, $item->quantity);
                    }
                }
                $order->update(['status' => 'cancelled', 'payment_status' => 'failed']);
            });

            return redirect()
                ->route('customer.checkout')
                ->with('error', 'PayPal is unavailable right now. Please try Cash on Delivery or try again later.');
        }
    }

    // ── PayPal success (return URL) ───────────────────────────────────────

    public function paypalSuccess(Request $request, Order $order)
    {
        $this->authorizeOrder($order);

        // Guard: already captured (user refreshed)
        if ($order->payment_status === 'paid') {
            return redirect()
                ->route('customer.orders.show', $order)
                ->with('success', "Payment already confirmed for Order #{$order->order_number}.");
        }

        Log::info('Customer PayPal return', [
            'order_id' => $order->id,
            'token'    => $request->query('token'),
            'PayerID'  => $request->query('PayerID'),
        ]);

        // PayPal API call OUTSIDE any DB transaction
        $captureResult = $this->paypal->capturePayment(
            $order->paypal_order_id ?? $request->query('token')
        );

        if (! $captureResult['success']) {
            Log::error('Customer PayPal capture failed', [
                'order_id' => $order->id,
                'message'  => $captureResult['message'],
            ]);

            return redirect()
                ->route('customer.orders.show', $order)
                ->with('error', 'Payment could not be confirmed: ' . $captureResult['message']);
        }

        // Persist payment in DB transaction (no HTTP calls inside)
        DB::transaction(function () use ($order, $captureResult) {
            $order->update([
                'payment_status'   => 'paid',
                'status'           => 'paid',
                'paypal_order_id'  => $captureResult['order']['id'] ?? $order->paypal_order_id,
                'paypal_payer_id'  => $captureResult['payer_id'],
                'paid_at'          => now(),
            ]);
        });

        return redirect()
            ->route('customer.orders.show', $order)
            ->with('success', "Payment confirmed! Order #{$order->order_number} is now being prepared.");
    }

    // ── PayPal cancel (cancel URL) ────────────────────────────────────────

    public function paypalCancel(Order $order)
    {
        $this->authorizeOrder($order);

        // Release stock reservations and cancel order
        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                if ($item->product && ! $item->product->isService() && $item->product->inventory) {
                    $this->inventory->release($item->product->inventory, $item->quantity);
                }
            }
            $order->update(['status' => 'cancelled', 'payment_status' => 'cancelled']);
        });

        return redirect()
            ->route('customer.checkout')
            ->with('warning', 'PayPal payment was cancelled. Your cart has been restored — please try again.');
    }

    // ── Cancel order ──────────────────────────────────────────────────────

    public function cancel(Request $request, Order $order)
    {
        $this->authorizeOrder($order);

        if (! in_array($order->status, ['pending', 'paid'])) {
            return back()->with('error', 'This order can no longer be cancelled.');
        }

        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                if ($item->product && ! $item->product->isService()) {
                    $inv = $item->product->inventory;
                    if ($inv) $this->inventory->release($inv, $item->quantity);
                }
            }
            $order->update(['status' => 'cancelled']);
        });

        return back()->with('success', "Order #{$order->order_number} cancelled.");
    }

    // ── Helper ────────────────────────────────────────────────────────────

    private function authorizeOrder(Order $order): void
    {
        if ($order->customer_id !== $this->customer()->id) {
            abort(403);
        }
    }
}
