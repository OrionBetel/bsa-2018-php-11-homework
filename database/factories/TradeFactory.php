<?php

use Faker\Generator as Faker;

$factory->define(App\Entity\Trade::class, function (Faker $faker) {
    return [
        'lot_id'  => $faker->numberBetween(1, 4),
        'user_id' => $faker->numberBetween(1, 4),
        'amount'  => $faker->randomFloat(2, 0, 999999,99),
    ];
});
