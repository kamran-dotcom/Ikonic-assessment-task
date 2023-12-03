<?php

namespace App\Services;

use App\Jobs\PayoutOrderJob;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;

class MerchantService
{
    /**
     * Register a new user and associated merchant.
     * Hint: Use the password field to store the API key.
     * Hint: Be sure to set the correct user type according to the constants in the User model.
     *
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return Merchant
     */
    public function register(array $data): Merchant
    {
        // TODO: Complete this method
       $user =  User::create([
            'email' => $data['email'],
            'name' => $data['name'],
            'password' => $data['api_key'],
            'type' => User::TYPE_MERCHANT,
        ]);
        $merchant = Merchant::create([
            'domain' => $data['domain'],
            'display_name' => $data['name'],
            'user_id' => $user->id
        ]);

        return $merchant;
        
    }

    /**
     * Update the user
     *
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return void
     */
    public function updateMerchant(User $user, array $data)
    {
        // TODO: Complete this method
        $user->merchant->update([
            'domain' => $data['domain'],
            'display_name' => $data['name'],
        ]);
    }

    /**
     * Find a merchant by their email.
     * Hint: You'll need to look up the user first.
     *
     * @param string $email
     * @return Merchant|null
     */
    public function findMerchantByEmail(string $email): ?Merchant
    {
        return Merchant::whereHas('user', function ($query) use ($email) {
            $query->where('email', $email);
        })->first();
    }

    /**
     * Pay out all of an affiliate's orders.
     * Hint: You'll need to dispatch the job for each unpaid order.
     *
     * @param Affiliate $affiliate
     * @return void
     */
    public function payout(Affiliate $affiliate)
    {
        $unpaidOrders = Order::where('affiliate_id', $affiliate->id)
        ->where('payout_status', Order::STATUS_UNPAID)
        ->get();

        foreach ($unpaidOrders as $order) {
            dispatch(new PayoutOrderJob($order));
        }
    }

    public function getOrderStatistics(string $fromDate, string $toDate): array
    {
        $fromDate = Carbon::parse($fromDate);
        $toDate = Carbon::parse($toDate);

        $orders = Order::where('merchant_id', $this->merchant->id)
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->get();

        $count = $orders->count();
        $revenue = $orders->sum('subtotal');
        $commissionsOwed = $orders->whereNotNull('affiliate_id')->sum('commission_owed');

        return [
            'count' => $count,
            'revenue' => $revenue,
            'commissions_owed' => $commissionsOwed,
        ];
    }
}
