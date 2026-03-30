<?php

namespace App\Services;

use App\Models\Delivery;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\InventoryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderManagementService
{
    // Valid forward transitions per status
    private const TRANSITIONS = [
        'pending'    => ['paid', 'processing', 'cancelled'],
        'paid'       => ['processing', 'cancelled'],
        'processing' => ['shipped', 'cancelled'],
        'shipped'    => ['delivered', 'cancelled'],
        'delivered'  => ['completed'],
        'completed'  => [],
        'cancelled'  => [],
    ];

    public function __construct(
        private readonly InventoryService $inventory
    ) {}

    // ──────────────────────────────────────────────────────────────────────
    // STATUS UPDATES
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Transition an order to a new status.
     * Validates the transition, fulfils stock on shipped, releases on cancel.
     */
    public function updateStatus(Order $order, string $newStatus, ?string $notes = null): Order
    {
        $allowed = self::TRANSITIONS[$order->status] ?? [];

        if (! in_array($newStatus, $allowed)) {
            throw new \RuntimeException(
                "Cannot move order from \"{$order->status}\" to \"{$newStatus}\"."
            );
        }

        return DB::transaction(function () use ($order, $newStatus, $notes) {

            $updates = ['status' => $newStatus];

            if ($notes) {
                $updates['vendor_notes'] = $notes;
            }

            // When shipping — deduct reserved stock (fulfil)
            if ($newStatus === 'shipped') {
                foreach ($order->items as $item) {
                    if ($item->product && ! $item->product->isService()) {
                        $inv = $item->product->inventory;
                        if ($inv) {
                            $this->inventory->fulfil(
                                inventory:     $inv,
                                quantity:      $item->quantity,
                                movementType:  'sale',
                                notes:         "Order #{$order->order_number}",
                                performedBy:   auth()->id(),
                                referenceType: Order::class,
                                referenceId:   $order->id,
                            );
                        }
                    }
                }
            }

            // When completing — set warranty start dates on items
            if ($newStatus === 'completed') {
                $updates['completed_at'] = now();

                foreach ($order->items as $item) {
                    if ($item->warranty_months) {
                        $expiresAt = now()->addMonths($item->warranty_months);

                        $item->update([
                            'warranty_starts_at'  => now(),
                            'warranty_expires_at' => $expiresAt->year < 2038
                                ? $expiresAt
                                : null,
                        ]);
                    }
                }
            }

            // When cancelling — release stock reservations
            if ($newStatus === 'cancelled') {
                foreach ($order->items as $item) {
                    if ($item->product && ! $item->product->isService()) {
                        $inv = $item->product->inventory;
                        if ($inv && $inv->quantity_reserved >= $item->quantity) {
                            $this->inventory->release($inv, $item->quantity);
                        }
                    }
                }
            }

            // Mark as paid when moving to paid manually
            if ($newStatus === 'paid' && ! $order->paid_at) {
                $updates['paid_at']        = now();
                $updates['payment_status'] = 'paid';
            }

            $order->update($updates);

            Log::info("Order #{$order->order_number} → {$newStatus}", [
                'order_id'    => $order->id,
                'vendor_id'   => $order->vendor_id,
                'performed_by'=> auth()->id(),
            ]);

            return $order->fresh();
        });
    }

    // ──────────────────────────────────────────────────────────────────────
    // DELIVERY ASSIGNMENT
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Create or update delivery record for an order.
     */
    public function assignDelivery(
        Order   $order,
        string  $courierName,
        ?string $trackingNumber  = null,
        ?string $estimatedDate   = null,
        ?string $notes           = null
    ): Delivery {
        return DB::transaction(function () use (
            $order, $courierName, $trackingNumber, $estimatedDate, $notes
        ) {
            $delivery = Delivery::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'vendor_id'      => $order->vendor_id,
                    'customer_id'    => $order->customer_id,
                    'courier_name'   => $courierName,
                    'tracking_number'=> $trackingNumber,
                    'estimated_date' => $estimatedDate,
                    'notes'          => $notes,
                    'status'         => 'pending',
                ]
            );

            // Auto-advance order to shipped when delivery is assigned
            if ($order->status === 'processing') {
                $this->updateStatus($order, 'shipped');
            }

            return $delivery;
        });
    }

    /**
     * Update delivery tracking status.
     */
    public function updateDeliveryStatus(
        Delivery $delivery,
        string   $status,
        ?string  $notes = null
    ): Delivery {
        $delivery->update([
            'status'       => $status,
            'notes'        => $notes ?? $delivery->notes,
            'shipped_at'   => $status === 'shipped'   ? now() : $delivery->shipped_at,
            'delivered_at' => $status === 'delivered' ? now() : $delivery->delivered_at,
        ]);

        // Sync order status
        $order = $delivery->order;

        if ($status === 'delivered' && $order->status === 'shipped') {
            $this->updateStatus($order, 'delivered');
        }

        return $delivery->fresh();
    }

    // ──────────────────────────────────────────────────────────────────────
    // ALLOWED TRANSITIONS HELPER
    // ──────────────────────────────────────────────────────────────────────

    public function allowedTransitions(string $currentStatus): array
    {
        return self::TRANSITIONS[$currentStatus] ?? [];
    }

    public function canTransition(string $from, string $to): bool
    {
        return in_array($to, self::TRANSITIONS[$from] ?? []);
    }
}
