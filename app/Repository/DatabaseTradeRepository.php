<?php

namespace App\Repository;

use App\Entity\Trade;
use App\Repository\Contracts\TradeRepository;

class DatabaseTradeRepository implements TradeRepository
{
    public function add(Trade $trade) : Trade
    {
        $trade->save();

        return $trade;
    }
}
