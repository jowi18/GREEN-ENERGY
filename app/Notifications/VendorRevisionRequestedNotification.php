<?php

namespace App\Notifications;

use App\Models\Vendor;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
// ─────────────────────────────────────────────────────────────────────────────
// File: app/Notifications/VendorRevisionRequestedNotification.php
// ─────────────────────────────────────────────────────────────────────────────

class VendorRevisionRequestedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Vendor $vendor,
        public readonly string $notes,
    ) {}

    public function via($notifiable): array { return ['mail']; }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Action Required: Application Revision — ' . config('app.name'))
            ->greeting('Hello, ' . $this->vendor->owner_first_name . ',')
            ->line('Our team has reviewed your application for **' . $this->vendor->business_name . '** and requires some revisions before we can proceed.')
            ->line('**Admin Notes:** ' . $this->notes)
            ->line('Please log in to your account, review the feedback on your documents, and re-submit the required files.')
            ->action('Update Application', route('vendor.register.status'))
            ->salutation('The ' . config('app.name') . ' Team');
    }
}
