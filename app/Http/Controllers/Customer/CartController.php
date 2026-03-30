<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    private const SESSION_KEY = 'customer_cart';

    // ── Get cart (JSON) ───────────────────────────────────────────────────

    public function index()
    {
        $cart  = session(self::SESSION_KEY, []);
        $items = [];
        $total = 0;

        foreach ($cart as $productId => $qty) {
            $product = Product::with('primaryImage')->find($productId);
            if (! $product || $product->status !== 'active') continue;

            $lineTotal = $product->price * $qty;
            $total    += $lineTotal;

            $items[$productId] = [
                'product_id' => $product->id,
                'name'       => $product->name,
                'price'      => $product->price,
                'quantity'   => $qty,
                'line_total' => $lineTotal,
                'image'      => $product->primaryImage
                    ? asset('storage/' . $product->primaryImage->file_path)
                    : null,
                'vendor_id'  => $product->vendor_id,
            ];
        }

        return response()->json([
            'items'      => $items,
            'total'      => $total,
            'cart_count' => array_sum($cart),
        ]);
    }

    // ── Add ───────────────────────────────────────────────────────────────

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity'   => ['nullable', 'integer', 'min:1'],
        ]);

        $product = Product::with('inventory')->findOrFail($request->product_id);

        if ($product->status !== 'active') {
            return response()->json(['success' => false, 'message' => 'Product is not available.']);
        }

        $qty = max(1, (int) $request->input('quantity', 1));

        // Stock check (skip for services)
        if (! $product->isService()) {
            $available = $product->inventory?->quantity_available ?? 0;
            $inCart    = session(self::SESSION_KEY . '.' . $product->id, 0);

            if (($inCart + $qty) > $available) {
                return response()->json([
                    'success' => false,
                    'message' => "Only {$available} units available (you have {$inCart} in cart).",
                ]);
            }
        }

        // Multi-vendor cart check
        $cart = session(self::SESSION_KEY, []);

        if (! empty($cart)) {
            $firstProductId = array_key_first($cart);
            $firstVendorId  = Product::find($firstProductId)?->vendor_id;

            if ($firstVendorId && $firstVendorId !== $product->vendor_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your cart contains items from a different vendor. Clear the cart first to order from this vendor.',
                ]);
            }
        }

        session([self::SESSION_KEY . '.' . $product->id => ($cart[$product->id] ?? 0) + $qty]);

        $newCart = session(self::SESSION_KEY, []);

        return response()->json([
            'success'    => true,
            'cart_count' => array_sum($newCart),
        ]);
    }

    // ── Update quantity ───────────────────────────────────────────────────

    public function update(Request $request)
    {
        $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity'   => ['required', 'integer', 'min:0'],
        ]);

        $productId = $request->product_id;
        $qty       = (int) $request->quantity;

        if ($qty <= 0) {
            session()->forget(self::SESSION_KEY . '.' . $productId);
        } else {
            session([self::SESSION_KEY . '.' . $productId => $qty]);
        }

        $cart = session(self::SESSION_KEY, []);

        return response()->json([
            'success'    => true,
            'cart_count' => array_sum($cart),
        ]);
    }

    // ── Remove ────────────────────────────────────────────────────────────

    public function remove(Request $request)
    {
        $request->validate(['product_id' => ['required']]);
        session()->forget(self::SESSION_KEY . '.' . $request->product_id);

        $cart = session(self::SESSION_KEY, []);

        return response()->json([
            'success'    => true,
            'cart_count' => array_sum($cart),
        ]);
    }

    // ── Clear ─────────────────────────────────────────────────────────────

    public function clear()
    {
        session()->forget(self::SESSION_KEY);
        return response()->json(['success' => true, 'cart_count' => 0]);
    }

    // ── Checkout page ─────────────────────────────────────────────────────

    public function checkout()
    {
        $cart  = session(self::SESSION_KEY, []);

        if (empty($cart)) {
            return redirect()->route('customer.products.index')
                ->with('warning', 'Your cart is empty.');
        }

        $items   = [];
        $total   = 0;
        $vendor  = null;

        foreach ($cart as $productId => $qty) {
            $product  = Product::with(['vendor', 'primaryImage'])->find($productId);
            if (! $product) continue;

            $vendor    = $product->vendor;
            $lineTotal = $product->price * $qty;
            $total    += $lineTotal;

            $items[] = compact('product', 'qty', 'lineTotal');
        }

        $customer = auth()->user()->customer;

        return view('customer.orders.checkout', compact('items', 'total', 'vendor', 'customer'));
    }
}
