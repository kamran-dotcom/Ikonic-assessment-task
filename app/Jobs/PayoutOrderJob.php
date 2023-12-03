<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\ApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class PayoutOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        public Order $order
    ) {}

    /**
     * Use the API service to send a payout of the correct amount.
     * Note: The order status must be paid if the payout is successful, or remain unpaid in the event of an exception.
     *
     * @return void
     */
    public function handle(ApiService $apiService)
    {
        try {
            $apiService->sendPayout($this->order->affiliate->user->email, $this->order->commission_owed);

            // Update the order status to paid if the payout is successful
            DB::transaction(function () {
                $this->order->update(['payout_status' => Order::STATUS_PAID]);
            });
        } catch (RuntimeException $exception) {
            // Log the exception for debugging purposes
            // In a real-world scenario, you would likely want to log and handle errors more gracefully
            report($exception);

            // If an exception is thrown, update the order status to unpaid
            $this->order->update(['payout_status' => Order::STATUS_UNPAID]);

            throw $exception;
        }
    }
}
