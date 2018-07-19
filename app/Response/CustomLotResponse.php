<?php

namespace App\Response;

use App\Entity\Lot;
use App\Repository\Contracts\{ WalletRepository, MoneyRepository };
use Carbon\Carbon;
use App\Response\Contracts\LotResponse;

class CustomLotResponse implements LotResponse
{
    protected $lot;
    protected $walletRepository;
    protected $moneyRepository;

    public function __construct(
        Lot $lot,
        WalletRepository $walletRepository,
        MoneyRepository $moneyRepository
    )
    {
        $this->lot              = $lot;
        $this->walletRepository = $walletRepository;
        $this->moneyRepository  = $moneyRepository;
    }
    
    /**
     * An identifier of lot
     *
     * @return int
     */
    public function getId() : int
    {
        return $lot->id;
    }

    public function getUserName() : string
    {
        return $lot->user->name;
    }

    public function getCurrencyName() : string
    {
        return $lot->currency->name;
    }

    /**
     * All amount of currency that user has in the wallet.
     *
     * @return float
     */
    public function getAmount() : float
    {
        $currency = $lot->currency;
        $wallet = $this->walletRepository->findByUser($lot->seller_id);
        
        $money = $this->moneyRepository->findByWalletAndCurrency($wallet->id, $currency->id);
        
        return $money->amount;
    }

    /**
     * Format: yyyy/mm/dd hh:mm:ss
     *
     * @return string
     */
    public function getDateTimeOpen() : string
    {
        return Carbon::createFromTimestamp($lot->getDateTimeOpen())->format('Y/m/d H:i:s');
    }

    /**
     * Format: yyyy/mm/dd hh:mm:ss
     *
     * @return string
     */
    public function getDateTimeClose() : string
    {
        return Carbon::createFromTimestamp($lot->getDateTimeClose())->format('Y/m/d H:i:s');
    }

    /**
     * Price per one amount of currency.
     *
     * Format: 00,00
     *
     * @return string
     */
    public function getPrice() : string
    {
        return number_format($lot->price, 2, ',', ' ');
    }
}
