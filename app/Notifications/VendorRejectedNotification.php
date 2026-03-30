
<?php
// ─────────────────────────────────────────────────────────────────────────────
// File: app/Notifications/VendorApprovedNotification.php
// ─────────────────────────────────────────────────────────────────────────────

use App\Models\Vendor;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;


class VendorRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Vendor $vendor,
        public readonly string $reason,
    ) {}

    public function via($notifiable): array { return ['mail']; }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Application Update — ' . config('app.name'))
            ->greeting('Hello, ' . $this->vendor->owner_first_name . ',')
            ->line('After reviewing your vendor application for **' . $this->vendor->business_name . '**, we were unable to approve it at this time.')
            ->line('**Reason:** ' . $this->reason)
            ->line('If you believe this is an error or have questions, please contact our support team.')
            ->action('Contact Support', config('app.url') . '/contact')
            ->salutation('The ' . config('app.name') . ' Team');
    }
}
