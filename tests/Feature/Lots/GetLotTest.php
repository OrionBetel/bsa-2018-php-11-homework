<?php

namespace Tests\Feature\Lots;

use App\User;
use App\Entity\{ Currency, Lot, Wallet, Money };
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetLotTest extends TestCase
{
    use RefreshDatabase;

    public function testGetUnexistentLot()
    {
        $response = $this->json('GET', '/api/v1/lots/1');

        $response->assertStatus(400)
                 ->assertHeader('Content-Type', 'application/json')
                 ->assertJsonFragment([
                     'message' => 'The requested lot does not exist.',
                 ]);
    }

    public function testGetLot()
    {
        factory(Currency::class)->create([
            'name' => 'bitcoin',
        ]);
        factory(User::class)->create();
        factory(Wallet::class)->create([
            'user_id' => 1,
        ]);
        factory(Money::class)->create([
            'wallet_id'   => 1,
            'currency_id' => 1
        ]);
        factory(Lot::class)->create([
            'currency_id' => 1,
            'seller_id'   => 1,
            'price'       => 987.21
        ]);

        $response = $this->json('GET', '/api/v1/lots/1');

        $response->assertStatus(200)
                 ->assertHeader('Content-Type', 'application/json')
                 ->assertJsonFragment([
                     'id'            => 1,
                     'currency_name' => 'bitcoin',
                     'price'         => '987,21'  
                 ]);
    }
}
