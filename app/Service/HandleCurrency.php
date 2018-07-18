<?php

namespace App\Service;

use App\Entity\Currency;
use App\Request\Contracts\AddCurrencyRequest;
use App\Repository\Contracts\CurrencyRepositoryInterface;
use App\Service\Contracts\CurrencyService;

class HandleCurrency implements CurrencyService
{
    protected $repository;

    public function __construct(CurrencyRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }
    
    public function addCurrency(AddCurrencyRequest $currencyRequest) : Currency
    {
        $currency = new Currency;
        
        $currency->name = $currencyRequest->getName();

        $addedCurrency = $this->repository->add($currency);

        return $addedCurrency;
    }
}
