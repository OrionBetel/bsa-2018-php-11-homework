<?php

use Faker\Generator as Faker;

$factory->define(App\Entity\Money::class, function (Faker $faker) {
    return [
        'wallet_id'   => $faker->numberBetween(1, 4),
        'currency_id' => $faker->numberBetween(1, 4),
        'amount'      => $faker->randomFloat(2, 0, 999999,99),
    ];
});
