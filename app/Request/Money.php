<?php

namespace App\Request;

use App\Request\Contracts\MoneyRequest;

class Money implements MoneyRequest
{
    protected $walletId;
    protected $currencyId;
    protected $amount;

    public function __construct(int $walletId, int $currencyId, float $amount)
    {
        $this->walletId = $walletId;
        $this->currencyId = $currencyId;
        $this->amount = $amount;
    }
    
    public function getWalletId() : int
    {
        return $this->walletId;
    }

    public function getCurrencyId() : int
    {
        return $this->currencyId;
    }

    public function getAmount() : float
    {
        return $this->amount;
    }
}
