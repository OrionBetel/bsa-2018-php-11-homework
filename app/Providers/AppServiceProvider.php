<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

use App\Repository\Contracts\CurrencyRepository;
use App\Repository\Contracts\UserRepository;
use App\Repository\Contracts\WalletRepository;
use App\Repository\Contracts\MoneyRepository;
use App\Repository\Contracts\TradeRepository;
use App\Repository\Contracts\LotRepository;
use App\Request\Contracts\AddCurrencyRequest;
use App\Request\Contracts\CreateWalletRequest;

use App\Repository\DatabaseCurrencyRepository;
use App\Repository\DatabaseUserRepository;
use App\Repository\DatabaseWalletRepository;
use App\Repository\DatabaseMoneyRepository;
use App\Repository\DatabaseTradeRepository;
use App\Repository\DatabaseLotRepository;
use App\Request\AddCurrency;
use App\Request\CreateWallet;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            CurrencyRepository::class,
            DatabaseCurrencyRepository::class
        );

        $this->app->bind(
            UserRepository::class,
            DatabaseUserRepository::class
        );

        $this->app->bind(
            WalletRepository::class,
            DatabaseWalletRepository::class
        );

        $this->app->bind(
            MoneyRepository::class,
            DatabaseMoneyRepository::class
        );

        $this->app->bind(
            TradeRepository::class,
            DatabaseTradeRepository::class
        );

        $this->app->bind(
            LotRepository::class,
            DatabaseLotRepository::class
        );

        $this->app->bind(
            AddCurrencyRequest::class,
            AddCurrency::class
        );

        $this->app->bind(
            CreateWalletRequest::class,
            CreateWallet::class
        );
    }
}
