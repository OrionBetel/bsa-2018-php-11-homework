<?php

namespace Tests\Feature\Trades;

use App\User;
use App\Entity\{ Currency, Lot, Wallet, Money };
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use App\Mail\TradeCreated;
use Tests\TestCase;

class BuyLotTest extends TestCase
{
    use RefreshDatabase;

    public function testBuyLotByUnauthenticatedUser()
    {
        $response = $this->json('POST', '/api/v1/trades', [
            'lot_id' => 1,
            'amount' => 123.45,
        ]);

        $response->assertStatus(403)
                 ->assertHeader('Content-Type', 'application/json')
                 ->assertJsonFragment([
                    'message' => 'Only authenticated users can buy currency.',
                 ]);

        $this->assertDatabaseMissing('trades', ['id' => 1]);
    }

    public function testBuyOwnLot()
    {
        $user = factory(User::class)->create();

        factory(Wallet::class)->create([
            'user_id' => 1,
        ]);
        factory(Currency::class)->create([
            'id' => 1,
        ]);
        factory(Lot::class)->create([
            'seller_id'   => 1,
            'currency_id' => 1,
        ]);
        
        $response = $this->actingAs($user)->json('POST', '/api/v1/trades', [
            'lot_id' => 1,
            'amount' => 123.45,
        ]);

        $response->assertStatus(400)
                 ->assertHeader('Content-Type', 'application/json')
                 ->assertJsonFragment([
                    'message' => 'You cannot buy your own lots.',
                 ]);

        $this->assertDatabaseMissing('trades', ['id' => 1]);
    }

    public function testBuyMoreThanLotHas()
    {
        $seller = factory(User::class)->create([
            'id' => 2,
        ]);
        $buyer = factory(User::class)->create([
            'id' => 3,
        ]);

        factory(Currency::class)->create([
            'id' => 1,
        ]);
        factory(Wallet::class)->create([
            'user_id' => 2,
        ]);
        factory(Wallet::class)->create([
            'user_id' => 3,
        ]);
        factory(Money::class)->create([
            'currency_id' => 1,
            'wallet_id'   => 2,
            'amount'      => 100,
        ]);
        factory(Lot::class)->create([
            'seller_id'   => 2,
            'currency_id' => 1,
        ]);
        
        $response = $this->actingAs($buyer)->json('POST', '/api/v1/trades', [
            'lot_id' => 2,
            'amount' => 123.45,
        ]);

        $response->assertStatus(400)
                 ->assertHeader('Content-Type', 'application/json')
                 ->assertJsonFragment([
                    'message' => 'You cannot buy more currency than lot contains.',
                 ]);

        $this->assertDatabaseMissing('trades', ['id' => 1]);
    }

    public function testBuyNegativeAmount()
    {
        $seller = factory(User::class)->create([
            'id' => 4,
        ]);
        $buyer = factory(User::class)->create([
            'id' => 5,
        ]);

        factory(Currency::class)->create([
            'id' => 1,
        ]);
        factory(Wallet::class)->create([
            'user_id' => 4,
        ]);
        factory(Wallet::class)->create([
            'user_id' => 5,
        ]);
        factory(Money::class)->create([
            'currency_id' => 1,
            'wallet_id'   => 4,
            'amount'      => 123.45,
        ]);
        factory(Lot::class)->create([
            'seller_id'   => 4,
            'currency_id' => 1,
        ]);
        
        $response = $this->actingAs($buyer)->json('POST', '/api/v1/trades', [
            'lot_id' => 3,
            'amount' => -1,
        ]);

        $response->assertStatus(400)
                 ->assertHeader('Content-Type', 'application/json')
                 ->assertJsonFragment([
                    'message' => 'You must buy at least one unit of currency.',
                 ]);

        $this->assertDatabaseMissing('trades', ['id' => 1]);
    }

    public function testBuyInactiveLot()
    {
        $seller = factory(User::class)->create([
            'id' => 6,
        ]);
        $buyer = factory(User::class)->create([
            'id' => 7,
        ]);

        factory(Currency::class)->create([
            'id' => 1,
        ]);
        factory(Wallet::class)->create([
            'user_id' => 6,
        ]);
        factory(Wallet::class)->create([
            'user_id' => 7,
        ]);
        factory(Money::class)->create([
            'currency_id' => 1,
            'wallet_id'   => 6,
            'amount'      => 123.45,
        ]);
        factory(Lot::class)->create([
            'seller_id'       => 6,
            'currency_id'     => 1,
            'date_time_close' => now()->toDateTimeString()
        ]);
        
        sleep(1);

        $response = $this->actingAs($buyer)->json('POST', '/api/v1/trades', [
            'lot_id' => 4,
            'amount' => 100,
        ]);

        $response->assertStatus(400)
                 ->assertHeader('Content-Type', 'application/json')
                 ->assertJsonFragment([
                    'message' => 'You cannot buy from a closed lot.',
                 ]);

        $this->assertDatabaseMissing('trades', ['id' => 1]);
    }

    public function testBuyLot()
    {
        $seller = factory(User::class)->create([
            'id' => 8,
        ]);
        $buyer = factory(User::class)->create([
            'id' => 9,
        ]);

        factory(Currency::class)->create([
            'id' => 1,
        ]);
        factory(Wallet::class)->create([
            'user_id' => 8,
        ]);
        factory(Wallet::class)->create([
            'user_id' => 9,
        ]);
        factory(Money::class)->create([
            'currency_id' => 1,
            'wallet_id'   => 8,
            'amount'      => 123.45,
        ]);
        factory(Money::class)->create([
            'currency_id' => 1,
            'wallet_id'   => 9,
            'amount'      => 100,
        ]);
        factory(Lot::class)->create([
            'seller_id'   => 8,
            'currency_id' => 1,
        ]);

        $response = $this->actingAs($buyer)->json('POST', '/api/v1/trades', [
            'lot_id' => 5,
            'amount' => 100,
        ]);

        $response->assertStatus(201)
                 ->assertHeader('Content-Type', 'application/json')
                 ->assertJsonFragment([
                    'message' => 'Currency was bought.',
                 ]);

        $this->assertDatabaseHas('trades', ['id' => 1]);
        $this->assertDatabaseHas('money', [
            'wallet_id' => 8,
            'amount'    => 23.45,
        ]);
        $this->assertDatabaseHas('money', [
            'wallet_id' => 9,
            'amount'    => 200,
        ]);

        Mail::fake();
        Mail::queue(TradeCreated::class, function ($mail) {
            return $mail->trade->id == 1 && $mail->seller->id == 8;
        });
    }
}
