<?php

namespace App\Http\Controllers;

use Auth;
use Gate;
use Validator;
use App\User;
use App\Entity\Lot;
use App\Service\Contracts\MarketService;
use Illuminate\Http\Request;
use App\Request\Contracts\AddLotRequest;
use App\Request\AddLot;
use App\Http\Controllers\Controller;

class LotsController extends Controller
{
    protected $marketService;

    public function __construct(MarketService $marketService)
    {
        $this->marketService = $marketService;
    }
    
    public function addLot(Request $request)
    {
        if (Gate::denies('create', Lot::class)) {
            return response()->json([
                'error' => [
                    'message'     => 'Only authenticated users can add lots.',
                    'status_code' => 403
                ]
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'currency_id'     => 'required|integer|exists:currencies,id',
            'date_time_open'  => 'required|integer',
            'date_time_close' => 'required|integer',
            'price'           => 'required|numeric'
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
            $this->marketService->addLot(new AddLot(
                $request->input('currency_id'),
                Auth::id(),
                $request->input('date_time_open'),
                $request->input('date_time_close'),
                $request->input('price')
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
            'message'     => 'Lot was added.',
            'status_code' => 200
        ], 200);
    }
}
