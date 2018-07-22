<?php

namespace Tests\Browser\Task3;

use App\User;
use App\Entity\Currency;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AddFormTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function testPageHasAddForm()
    {
        $this->browse(
            function (Browser $browser) {
                $user = factory(User::class)->create();

                for ($i = 1; $i < 5; $i++) {
                    factory(Currency::class)->create([
                        'name' => ('CurrencyName' . $i),
                    ]);
                }

                $browser
                    ->loginAs($user)
                    ->visit('/market/lots/add')
                    ->assertSee('Currency')
                    ->assertSee('Price')
                    ->assertSee('Start sell')
                    ->assertSee('End sell')
                    ->assertPresent('select')
                    ->assertPresent('input[name="price"]')
                    ->assertPresent('input[name="date-open"]')
                    ->assertPresent('input[name="time-open"]')
                    ->assertPresent('input[name="date-close"]')
                    ->assertPresent('input[name="time-close"]')
                    ->assertPresent('button[type="submit"]');
            }
        );
    }

    public function testAddFormWorks()
    {
        $this->browse(
            function (Browser $browser) {
                $user = factory(User::class)->create();

                for ($i = 1; $i < 5; $i++) {
                    factory(Currency::class)->create([
                        'name' => ('CurrencyName' . $i),
                    ]);
                }

                $browser
                    ->loginAs($user)
                    ->visit('/market/lots/add')
                    ->select('currency-id')
                    ->type('price', '123.45')
                    ->keys('#date-open', '07-22-2018')
                    ->keys('#time-open', '08:00:00')
                    ->keys('#date-close', '07-23-2018')
                    ->keys('#time-close', '08:00 am')
                    ->click('button[type="submit"]')
                    ->assertSee('Lot has been added successfully!');
            }
        );
    }
}
