<?php

// ─────────────────────────────────────────────────────────────────────────────
// app/Notifications/SupplierAccountCreatedNotification.php
// ─────────────────────────────────────────────────────────────────────────────

namespace App\Notifications;

use App\Models\Supplier;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SupplierAccountCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Supplier $supplier,
        public readonly string   $plainPassword,
        public readonly string   $vendorName,
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Supplier Account — ' . config('app.name'))
            ->greeting('Hello, ' . $this->supplier->contact_person . '!')
            ->line('**' . $this->vendorName . '** has added you as a supplier on **' . config('app.name') . '**.')
            ->line('A portal account has been created for you so you can manage orders, update your product catalog, and communicate with the vendor directly.')
            ->line('---')
            ->line('**Your login credentials:**')
            ->line('**Email:** ' . $this->supplier->email)
            ->line('**Temporary Password:** `' . $this->plainPassword . '`')
            ->action('Log in to Supplier Portal', route('supplier.dashboard'))
            ->line('For security, please change your password after your first login.')
            ->line('If you were not expecting this invitation, you can safely ignore this email.')
            ->salutation('The ' . config('app.name') . ' Team');
    }
}
