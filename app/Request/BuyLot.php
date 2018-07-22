<?php

namespace App\Request;

use App\Request\Contracts\BuyLotRequest;

class BuyLot implements BuyLotRequest
{
    protected $userId;
    protected $lotId;
    protected $amount;

    public function __construct(int $userId, int $lotId, float $amount)
    {
        $this->userId = $userId;
        $this->lotId = $lotId;
        $this->amount = $amount;
    }
    
    public function getUserId() : int
    {
        return $this->userId;
    }

    public function getLotId() : int
    {
        return $this->lotId;
    }

    public function getAmount() : float
    {
        return $this->amount;
    }
}
