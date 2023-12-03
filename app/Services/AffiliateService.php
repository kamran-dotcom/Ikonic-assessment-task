<?php

namespace App\Services;

use App\Exceptions\AffiliateCreateException;
use App\Mail\AffiliateCreated;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
class AffiliateService
{
    public function __construct(
        protected ApiService $apiService
    ) {
        $this->apiService = $apiService;
    }

    /**
     * Create a new affiliate for the merchant with the given commission rate.
     *
     * @param  Merchant $merchant
     * @param  string $email
     * @param  string $name
     * @param  float $commissionRate
     * @return Affiliate
     */
    public function register(Merchant $merchant, string $email, string $name, float $commissionRate): Affiliate
    {
        // Check if the email is already in use by a merchant
        $merchantWithEmail = User::where('email', $email)->where('type', User::TYPE_MERCHANT)->exists();
        if ($merchantWithEmail) {
            throw new AffiliateCreateException('Email is already in use by a merchant.');
        }

        // Check if the email is already in use by an affiliate
        $affiliateWithEmail = User::where('email', $email)->where('type', User::TYPE_AFFILIATE)->exists();
        if ($affiliateWithEmail) {
            throw new AffiliateCreateException('Email is already in use by an affiliate.');
        }

        // Create a new user
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt(Str::random(16)),
            'type' => User::TYPE_AFFILIATE,
        ]);

        // Create a discount code for the affiliate
        $discountCode = $this->apiService->createDiscountCode($merchant);

        // Create the affiliate
        $affiliate = Affiliate::create([
            'user_id' => $user->id,
            'merchant_id' => $merchant->id,
            'commission_rate' => $commissionRate,
            'discount_code' => $discountCode['code'], // Use the 'code' field from the API response
        ]);

        // Send an email to the affiliate
        Mail::to($user->email)->send(new AffiliateCreated($affiliate));

        return $affiliate;
    }
}
