<?php

namespace App\Request;

use App\Request\Contracts\CreateWalletRequest;

class CreateWallet implements CreateWalletRequest
{
    protected $userId;

    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }
    
    public function getUserId() : int
    {
        return $this->userId;
    }
}
