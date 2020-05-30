<?php
    $factory->define(Model\User::class, function (Faker\Generator $faker) {
        $firstName = $faker->firstNameMale;
        $lastName = $faker->lastName;
        return [
            'name' => $firstName.' '. $faker->lastName,
            'email' => strtolower($firstName).'-'.strtolower($lastName).'@example.com',
            'password' => app('hash')->make('12345'),
            'role' => 'admin',
            'remember_token' => str_random(10),
        ];
    });