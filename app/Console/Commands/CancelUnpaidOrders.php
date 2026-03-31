<?php
// app/Console/Commands/CancelUnpaidOrders.php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CancelUnpaidOrders extends Command
{
    protected $signature   = 'app:cancel-unpaid-orders
                                {--minutes=15 : Cancel orders with pending payment older than N minutes}
                                {--dry-run    : Report without cancelling}';

    protected $description = 'Cancel orders whose payment is still pending after a timeout period.';

    public function handle(): int
    {
        $minutes = (int) $this->option('minutes');
        $dryRun  = $this->option('dry-run');
        $cutoff  = now()->subMinutes($minutes);

        $orders = Order::where('status', 'pending')
            ->whereHas('payment', fn ($q) => $q->where('status', 'pending'))
            ->where('created_at', '<', $cutoff)
            ->with('payment', 'user')
            ->get();

        if ($orders->isEmpty()) {
            $this->info('No unpaid orders to cancel.');
            return self::SUCCESS;
        }

        $this->info("Found {$orders->count()} unpaid order(s) older than {$minutes} minutes.");

        if ($dryRun) {
            $this->table(
                ['Order ID', 'Customer', 'Total', 'Created At'],
                $orders->map(fn ($o) => [
                    $o->id,
                    $o->user->email,
                    '$' . $o->total_amount,
                    $o->created_at->toDateTimeString(),
                ])
            );
            $this->warn('[dry-run] No orders were actually cancelled.');
            return self::SUCCESS;
        }

        DB::transaction(function () use ($orders) {
            foreach ($orders as $order) {
                $order->update(['status' => 'cancelled']);
                $order->payment->update(['status' => 'failed']);

                Log::info('[CancelUnpaidOrders] Cancelled order', [
                    'order_id'   => $order->id,
                    'customer'   => $order->user->email,
                    'total'      => $order->total_amount,
                    'created_at' => $order->created_at,
                ]);
            }
        });

        $this->info("✓ Cancelled {$orders->count()} order(s).");

        return self::SUCCESS;
    }
}
