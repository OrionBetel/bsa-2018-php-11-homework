<?php

use Faker\Generator as Faker;

$factory->define(App\Entity\Wallet::class, function (Faker $faker) {
    return [
        'user_id' => $faker->unique()->numberBetween(1, 4),
    ];
});
