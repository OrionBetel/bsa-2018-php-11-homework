<?php

namespace App\Repository\Contracts;

use App\Entity\Contracts\Currency;

interface CurrencyRepository
{
    public function add(Currency $currency) : Currency;

    public function getById(int $id) : Currency;

    /**
     * @return Currency[]
     */
    public function findAll();
}