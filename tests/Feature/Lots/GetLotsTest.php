<?php

namespace Tests\Feature\Lots;

use App\User;
use App\Entity\{ Currency, Lot, Wallet, Money };
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetLotTest extends TestCase
{
    use RefreshDatabase;

    public function testGetLot()
    {
        for ($i = 1; $i < 5; $i++) {
            factory(Currency::class)->create([
                'name' => ('coin' . $i),
            ]);

            factory(User::class)->create();

            factory(Wallet::class)->create([
                'user_id' => $i,
            ]);

            factory(Money::class)->create([
                'wallet_id'   => $i,
                'currency_id' => $i
            ]);

            factory(Lot::class)->create([
                'currency_id' => $i,
                'seller_id'   => $i,
                'price'       => ($i * 100)
            ]);
        }
        
        $response = $this->json('GET', '/api/v1/lots');

        $response->assertStatus(200)
                 ->assertHeader('Content-Type', 'application/json');
        
        $this->assertCount(4, json_decode($response->getContent(), true));
    }
}
