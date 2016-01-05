<?php

use Logaretm\Depo\Tests\Models\Task;

$factory->define(Task::class, function (Faker\Generator $faker) {
    return [
        'body' => $faker->paragraph,
        'completed' => $faker->boolean()
    ];
});
