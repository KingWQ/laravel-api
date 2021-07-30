<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\User\User;
use Faker\Generator as Faker;
use Illuminate\Support\Facades\Hash;
use App\Models\User\Address;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(User::class, function (Faker $faker) {
    return [
        'username' => $faker->name,
        'password' => Hash::make(123456),
        'gender'   => $faker->randomKey([0, 1, 2]),
        'mobile'   => $faker->phoneNumber,
        'avatar'   => $faker->imageUrl,
    ];
});

$factory->define(Address::class, function (Faker $faker) {
    return [
        'name'           => $faker->name,
        'user_id'        => 0,
        'province'       => '广东省',
        'city'           => '广州市',
        'county'         => '天河区',
        'address_detail' => $faker->streetAddress,
        'area_code'      => '',
        'postal_code'    => $faker->postcode,
        'tel'            => $faker->phoneNumber,
        'is_default'     => 0
    ];
});

$factory->state(User::class, 'address_default', function () {
    return [];
})->afterCreatingState(User::class, 'address_default', function ($user) {
    factory(Address::class)->create([
        'user_id'    => $user->id,
        'is_default' => 1
    ]);
});
