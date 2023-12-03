<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Merchant;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * You don't need to do anything here. This is just to help
 */
class ApiService
{
    /**
     * Create a new discount code for an affiliate
     *
     * @param Merchant $merchant
     *
     * @return array{id: int, code: string}
     */
    public function createDiscountCode(Merchant $merchant): array
    {
        return [
            'id' => rand(0, 100000),
            'code' => Str::uuid()
        ];
    }

    /**
     * Send a payout to an email
     *
     * @param  string $email
     * @param  float $amount
     * @return void
     * @throws RuntimeException
     */
    public function sendPayout(string $email, float $amount)
    {
        //

        if (Str::startsWith($email, 'success')) {
            // Simulate a successful payout
            // In a real-world scenario, you would handle the actual payout logic here
            return;
        }

        // If the email does not start with 'success', simulate a payout failure
        // In a real-world scenario, you would handle the failure and potentially retry or log the error
        throw new RuntimeException('Payout failed.');
    }
}
