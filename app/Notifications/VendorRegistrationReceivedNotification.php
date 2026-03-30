<?php
// ─────────────────────────────────────────────────────────────────────────────
// File: app/Notifications/VendorRegistrationReceivedNotification.php
// ─────────────────────────────────────────────────────────────────────────────
namespace App\Notifications;

use App\Models\Vendor;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class VendorRegistrationReceivedNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly Vendor $vendor) {}

    public function via($notifiable): array { return ['mail']; }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Application Received — ' . config('app.name'))
            ->greeting('Hello, ' . $this->vendor->owner_first_name . '!')
            ->line('We have received your vendor application for **' . $this->vendor->business_name . '**.')
            ->line('Our team will review your submitted documents and get back to you within 3–5 business days.')
            ->line('You will receive an email notification once your application has been reviewed.')
            ->salutation('The ' . config('app.name') . ' Team');
    }
}
