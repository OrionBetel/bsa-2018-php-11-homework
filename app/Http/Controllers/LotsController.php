<?php

namespace App\Http\Controllers;

use Auth;
use Gate;
use Validator;
use Carbon\Carbon;
use App\User;
use App\Entity\{ Lot, Currency };
use App\Service\Contracts\MarketService;
use Illuminate\Http\Request;
use App\Request\Contracts\AddLotRequest;
use App\Request\AddLot;
use App\Response\Contracts\LotResponse;
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
            'status_code' => 201
        ], 201);
    }

    public function getLot(int $id)
    {
        try {
            $lot = $this->marketService->getLot($id);
        } catch (\Exception $e) {
            return response()->json([
                'error' => [
                    'message'     => $e->getMessage(),
                    'status_code' => 400
                ]
            ], 400);
        }

        return response()->json([
            'id'              => $lot->getId(),
            'user_name'       => $lot->getUserName(),
            'currency_name'   => $lot->getCurrencyName(),
            'amount'          => $lot->getAmount(),
            'date_time_open'  => $lot->getDateTimeOpen(),
            'date_time_close' => $lot->getDateTimeClose(),
            'price'           => $lot->getPrice(),
        ], 200);
    }

    public function getLots()
    {
        $lots = $this->marketService->getLotList();

        $preparedLots = [];

        foreach ($lots as $lot) {
            $preparedLots[] = [
                'id'              => $lot->getId(),
                'user'            => $lot->getUserName(),
                'currency'        => $lot->getCurrencyName(),
                'amount'          => $lot->getAmount(),
                'date_time_open'  => $lot->getDateTimeOpen(),
                'date_time_close' => $lot->getDateTimeClose(),
                'price'           => $lot->getPrice(),
            ];
        }

        return response()->json($preparedLots);
    }

    public function showAddForm(string $errormsg = null)
    {
        $currencies = array_map(function ($currency) {
            return [
                'id'   => $currency['id'],
                'name' => $currency['name']
            ];
        }, Currency::all()->toArray());

        return view('addLot', [
            'currencies' => $currencies,
            'errormsg'   => $errormsg,
        ]);
    }

    public function addLotFromForm(Request $request)
    {
        if (Gate::denies('create', Lot::class)) {
            return redirect('/');
        }

        // try {
        //     $this->marketService->addLot(new AddLot(
        //         $request->input('currency-id'),
        //         Auth::id(),
        //         Carbon::createFromFormat(
        //             'Y-m-d H:i:s',
        //             ($request->input('date-open') . ' ' . $request->input('time-open')))->timestamp,
        //         Carbon::createFromFormat(
        //             'Y-m-d H:i:s',
        //             ($request->input('date-close') . ' ' . $request->input('time-close') . ':00'))->timestamp,
        //         $request->input('price')
        //     ));
        // } catch (\Exception $e) {
        //     return redirect()->action(
        //         'LotsController@showAddForm', [
        //             'errormsg' => 'Sorry, error has been occurred: ' . $e->getMessage(),
        //         ]
        //     );
        // }

        $this->marketService->addLot(new AddLot(
            $request->input('currency-id'),
            Auth::id(),
            Carbon::parse($request->input('date-open') . ' ' . $request->input('time-open'))->timestamp,
            Carbon::parse($request->input('date-close') . ' ' . $request->input('time-close'))->timestamp,
            $request->input('price')
        ));
        
        return response('Lot has been added successfully!');
    }
}
