<?php
// ─────────────────────────────────────────────────────────────────────────────
// File: app/Notifications/VendorApprovedNotification.php
// ─────────────────────────────────────────────────────────────────────────────
namespace App\Notifications;

use App\Models\Vendor;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class VendorApprovedNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly Vendor $vendor) {}

    public function via($notifiable): array { return ['mail']; }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('🎉 Application Approved — ' . config('app.name'))
            ->greeting('Congratulations, ' . $this->vendor->owner_first_name . '!')
            ->line('Your vendor application for **' . $this->vendor->business_name . '** has been **approved**.')
            ->line('To activate your vendor portal, please complete your subscription.')
            ->action('Subscribe Now', route('vendor.subscription.index'))
            ->line('Once subscribed, you will have full access to your vendor dashboard, POS system, inventory management, and online storefront.')
            ->salutation('Welcome aboard — The ' . config('app.name') . ' Team');
    }
}






