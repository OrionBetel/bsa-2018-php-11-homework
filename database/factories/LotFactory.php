<?php

use Faker\Generator as Faker;

$factory->define(App\Entity\Lot::class, function (Faker $faker) {
    return [
        'currency_id'     => $faker->numberBetween(1, 4),
        'seller_id'       => $faker->numberBetween(1, 4),
        // 'date_time_open'  => $faker->unixTime(),
        // 'date_time_close' => ($faker->unixTime() + 14400),
        'price'           => $faker->randomFloat(2, 0, 999999,99),
    ];
});
