<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class SubscriptionActivatedNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly Subscription $subscription) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $vendor = $this->subscription->vendor;
        $plan   = $this->subscription->plan;

        return (new MailMessage)
            ->subject('🎉 Subscription Activated — ' . config('app.name'))
            ->greeting('Welcome to the platform, ' . $vendor->owner_first_name . '!')
            ->line('Your **' . $plan->name . '** subscription is now active.')
            ->line('**Plan:** ' . $plan->name)
            ->line('**Amount paid:** ' . strtoupper($plan->currency) . ' ' . number_format($this->subscription->amount_paid, 2))
            ->line('**Valid until:** ' . $this->subscription->expires_at->format('F d, Y'))
            ->action('Open Vendor Portal', route('vendor.dashboard'))
            ->line('You now have full access to your vendor dashboard, POS system, inventory management, and online storefront.')
            ->salutation('The ' . config('app.name') . ' Team');
    }
}
