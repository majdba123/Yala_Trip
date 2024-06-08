<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ExpireSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'expire:subscriptions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire subscriptions that have reached their end date';
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $subscriptions = user_subscription::where('end_date', '<=', now())->where('status', 'active')->get();
        foreach ($subscriptions as $subscription) {
            $subscription->status = 'expired';
            $subscription->save();
        }
        $this->info('Expired subscriptions updated successfully!');
    }
}
