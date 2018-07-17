<?php

namespace App\Repository;

use App\Entity\Currency;
use App\Repository\Contracts\CurrencyRepository;

class DatabaseCurrencyRepository implements CurrencyRepository
{
    public function add(Currency $currency) : Currency
    {
        $currency->save();

        return $currency;
    }

    public function getById(int $id) : ?Currency
    {
        return Currency::find($id);
    }

    public function getCurrencyByName(string $name) : ?Currency
    {
        return Currency::where('name', $name)->first();
    }

    /**
     * @return Currency[]
     */
    public function findAll()
    {
        return Currency::all();    
    }
}
