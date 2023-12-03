<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Services\MerchantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MerchantController extends Controller
{
    public function __construct(
        MerchantService $merchantService
    ) {
        $this->merchantService = $merchantService;
    }

    /**
     * Useful order statistics for the merchant API.
     * 
     * @param Request $request Will include a from and to date
     * @return JsonResponse Should be in the form {count: total number of orders in range, commission_owed: amount of unpaid commissions for orders with an affiliate, revenue: sum order subtotals}
     */
    public function orderStats(Request $request): JsonResponse
    {
        $request->validate([
            'from_date' => 'required|date',
            'to_date' => 'required|date',
        ]);
    
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
    
        $orderStats = $this->merchantService->getOrderStatistics($fromDate, $toDate);
    
        // Check if the array keys exist before returning the response
        if (!isset($orderStats['count']) || !isset($orderStats['revenue']) || !isset($orderStats['commissions_owed'])) {
            return response()->json(['error' => 'Incomplete data'], 500);
        }
    
        return response()->json($orderStats);
    }
}
