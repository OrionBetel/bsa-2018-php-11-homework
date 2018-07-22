<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

use App\Repository\Contracts\{
    CurrencyRepository,
    UserRepository,
    WalletRepository,
    MoneyRepository,
    TradeRepository,
    LotRepository
};
use App\Repository\{
    DatabaseCurrencyRepository,
    DatabaseUserRepository,
    DatabaseWalletRepository,
    DatabaseMoneyRepository,
    DatabaseTradeRepository,
    DatabaseLotRepository
};

use App\Request\Contracts\{
    AddCurrencyRequest,
    CreateWalletRequest,
    MoneyRequest,
    BuyLotRequest,
    AddLotRequest
};
use App\Request\{
    AddCurrency,
    CreateWallet,
    Money,
    BuyLot,
    AddLot
};

use App\Service\Contracts\{
    CurrencyService,
    WalletService,
    MarketService
};
use App\Service\{
    HandleCurrency,
    HandleWallet,
    HandleMarket
};

use App\Response\Contracts\LotResponse;
use App\Response\CustomLotResponse;

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

        $this->app->bind(
            MoneyRequest::class,
            Money::class
        );

        $this->app->bind(
            BuyLotRequest::class,
            BuyLot::class
        );

        $this->app->bind(
            AddLotRequest::class,
            AddLot::class
        );

        $this->app->bind(
            CurrencyService::class,
            HandleCurrency::class
        );

        $this->app->bind(
            WalletService::class,
            HandleWallet::class
        );

        $this->app->bind(
            MarketService::class,
            HandleMarket::class
        );

        $this->app->bind(
            LotResponse::class,
            CustomLotResponse::class
        );
    }
}
