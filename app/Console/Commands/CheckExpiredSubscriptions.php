<?php

namespace App\Console\Commands;

use App\Services\SubscriptionService;
use Illuminate\Console\Command;

class CheckExpiredSubscriptions extends Command
{
    protected $signature   = 'subscriptions:expire';
    protected $description = 'Expire subscriptions past their end date and downgrade vendor status';

    public function __construct(private readonly SubscriptionService $subscriptionService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $count = $this->subscriptionService->expireStale();

        $count > 0
            ? $this->info("Expired {$count} subscription(s) and downgraded vendor status.")
            : $this->info('No subscriptions to expire.');

        return Command::SUCCESS;
    }
}
