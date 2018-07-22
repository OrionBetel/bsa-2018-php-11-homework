<?php

namespace App\Http\Controllers;

use Auth;
use Gate;
use Validator;
use Illuminate\Http\Request;
use App\Service\Contracts\MarketService;
use App\Request\BuyLot;
use App\Entity\Trade;

class TradesController extends Controller
{
    protected $marketService;

    public function __construct(MarketService $marketService)
    {
        $this->marketService = $marketService;
    }
    
    public function buyCurrency(Request $request)
    {
        if (Gate::denies('create', Trade::class)) {
            return response()->json([
                'error' => [
                    'message'     => 'Only authenticated users can buy currency.',
                    'status_code' => 403
                ]
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'lot_id' => 'required|integer|exists:lots,id',
            'amount' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => [
                    'message'     => 'There were error(s) in provided data.',
                    'status_code' => 400
                ]
            ], 400);
        }

        try {
            $this->marketService->buyLot(new BuyLot(
                Auth::id(),
                $request->input('lot_id'),
                $request->input('amount')
            ));
        } catch (\Exception $e) {
            return response()->json([
                'error' => [
                    'message'     => $e->getMessage(),
                    'status_code' => 400
                ]
            ], 400);
        }
        
        return response()->json([
            'message'     => 'Currency was bought.',
            'status_code' => 201
        ], 201);
    }
}
