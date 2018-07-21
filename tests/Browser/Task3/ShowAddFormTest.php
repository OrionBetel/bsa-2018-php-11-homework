<?php

namespace Tests\Browser\Task3;

use App\User;
use App\Entity\Currency;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ShowAddFormTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function testPageHasAddingForm()
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
                    ->assertPresent('input[type="number"]')
                    ->assertPresent('input[type="date"]')
                    ->assertPresent('input[type="time"]')
                    ->assertPresent('button[type="submit"]')
                    ->assertSelectHasOptions('select', [
                        'CurrencyName1',
                        'CurrencyName2',
                        'CurrencyName3',
                        'CurrencyName4',
                    ]);
            }
        );
    }
}
