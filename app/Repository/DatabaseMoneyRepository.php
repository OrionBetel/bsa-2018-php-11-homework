<?php

namespace App\Repository;

use App\Entity\Money;
use App\Repository\Contracts\MoneyRepository;

class DatabaseMoneyRepository implements MoneyRepository
{
    public function save(Money $money) : Money
    {
        $money->save();

        return $money;
    }

    public function findByWalletAndCurrency(int $walletId, int $currencyId) : ?Money
    {
        return Money::where([
            ['wallet_id', '=', $walletId],
            ['currency_id', '=', $currencyId],
        ])->first();
    }
}
