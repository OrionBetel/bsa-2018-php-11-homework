<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

use App\Repository\Contracts\CurrencyRepository;
use App\Repository\Contracts\UserRepository;
use App\Repository\Contracts\WalletRepository;

use App\Repository\DatabaseCurrencyRepository;
use App\Repository\DatabaseUserRepository;
use App\Repository\DatabaseWalletRepository;

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
    }
}
