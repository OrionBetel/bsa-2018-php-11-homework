<?php

namespace Tests\Feature\Lots;

use App\User;
use App\Entity\{ Currency, Lot };
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddLotTest extends TestCase
{
    use RefreshDatabase;

    public function testAddLotByUnauthenticatedUser()
    {
        $response = $this->json('POST', '/api/v1/lots', [
            'currency_id'     => 1,
            'date_time_open'  => now()->timestamp,
            'date_time_close' => (now()->timestamp + 3600),
            'price'           => 123.45
        ]);

        $response->assertStatus(403)
                 ->assertHeader('Content-Type', 'application/json')
                 ->assertJsonStructure([
                     'error' => [
                         'message',
                         'status_code'
                     ]
                 ]);

        $this->assertDatabaseMissing('lots', ['id' => 1]);
    }

    public function testAddActiveLot()
    {
        $user = factory(User::class)->create([
            'id' => 1
        ]);
        factory(Currency::class)->create([
            'id' => 1
        ]);
        factory(Lot::class)->create([
            'seller_id'   => 1,
            'currency_id' => 1,
            'price'       => 123.45
        ]);

        $response = $this->actingAs($user)->json('POST', '/api/v1/lots', [
            'currency_id'     => 1,
            'date_time_open'  => now()->timestamp,
            'date_time_close' => (now()->timestamp + 3600),
            'price'           => 453.21
        ]);

        $response->assertStatus(400)
                 ->assertHeader('Content-Type', 'application/json')
                 ->assertJsonFragment([
                    'message' => 'You cannot have more than one active sell session of a particular currency.'
                 ]);

        $this->assertDatabaseMissing('lots', ['price' => 453.21]);
    }

    public function testAddLotWithIncorrectTimeClose()
    {
        $user = factory(User::class)->create([
            'id' => 2
        ]);
        factory(Currency::class)->create([
            'id' => 2
        ]);

        $response = $this->actingAs($user)->json('POST', '/api/v1/lots', [
            'currency_id'     => 2,
            'date_time_open'  => now()->timestamp,
            'date_time_close' => (now()->timestamp - 1),
            'price'           => 123.45
        ]);

        $response->assertStatus(400)
                 ->assertHeader('Content-Type', 'application/json')
                 ->assertJsonFragment([
                    'message' => 'The close date and time of your sell session cannot be less then the open date and time.'
                 ]);

        $this->assertDatabaseMissing('lots', [
            'seller_id'   => 2,
            'currency_id' => 2
        ]);
    }

    public function testAddLotWithNegativePrice()
    {
        $user = factory(User::class)->create([
            'id' => 3
        ]);
        factory(Currency::class)->create([
            'id' => 3
        ]);

        $response = $this->actingAs($user)->json('POST', '/api/v1/lots', [
            'currency_id'     => 3,
            'date_time_open'  => now()->timestamp,
            'date_time_close' => (now()->timestamp + 3600),
            'price'           => -1
        ]);

        $response->assertStatus(400)
                 ->assertHeader('Content-Type', 'application/json')
                 ->assertJsonFragment([
                    'message' => 'Lot price cannot be negative.'
                 ]);

        $this->assertDatabaseMissing('lots', [
            'seller_id'   => 3,
            'currency_id' => 3
        ]);
    }

    public function testAddLot()
    {
        $user = factory(User::class)->create([
            'id' => 4
        ]);

        factory(Currency::class)->create([
            'id' => 4
        ]);
        
        $response = $this->actingAs($user)->json('POST', '/api/v1/lots', [
            'currency_id'     => 4,
            'date_time_open'  => now()->timestamp,
            'date_time_close' => (now()->timestamp + 3600),
            'price'           => 123.45
        ]);

        $response->assertStatus(200)
                 ->assertHeader('Content-Type', 'application/json')
                 ->assertJsonStructure([
                    'message',
                    'status_code'
                 ]);

        $this->assertDatabaseHas('lots', [
            'seller_id'   => 4,
            'currency_id' => 4
        ]);
    }
}
