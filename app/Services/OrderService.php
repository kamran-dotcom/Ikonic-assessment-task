<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;

class OrderService
{
    public function __construct(
        protected AffiliateService $affiliateService
    ) {
        $this->affiliateService = $affiliateService;
    }

    /**
     * Process an order and log any commissions.
     * This should create a new affiliate if the customer_email is not already associated with one.
     * This method should also ignore duplicates based on order_id.
     *
     * @param  array{order_id: string, subtotal_price: float, merchant_domain: string, discount_code: string, customer_email: string, customer_name: string} $data
     * @return void
     */
    public function processOrder(array $data)
    {
        $existingOrder = Order::where('external_order_id', $data['order_id'])->first();

        if ($existingOrder) {
            // If the order already exists, you can handle it accordingly.
            // For example, update the existing order or log a message.
            // For this test, we'll simply return.
            return;
        }
    
        // Register affiliate using AffiliateService
        $affiliate = $this->affiliateService->register(
            Merchant::where('domain', $data['merchant_domain'])->first(),
            $data['customer_email'],
            $data['customer_name'],
            0.1
        );
    
        // Create the order
    
        $order = Order::create([
            'external_order_id' => $data['order_id'],
            'subtotal' => $data['subtotal_price'],
            'merchant_id' => $affiliate->merchant->id,
            'affiliate_id' => $affiliate->id,
            'commission_owed' => $data['subtotal_price'] * $affiliate->commission_rate,
        ]);
    
        return $order;
    }
}
