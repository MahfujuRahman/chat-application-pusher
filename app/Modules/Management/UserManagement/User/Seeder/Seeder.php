<?php

namespace App\Modules\Management\UserManagement\User\Seeder;

use Illuminate\Database\Seeder as SeederClass;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Hash;

class Seeder extends SeederClass
{
    /**
     * Run the database seeds.
     php artisan db:seed --class="\App\Modules\Management\UserManagement\User\Seeder\Seeder"
     */
    static $model = \App\Modules\Management\UserManagement\User\Models\Model::class;

    public function run(): void
    {
        $faker = Faker::create();
        self::$model::truncate();

        self::$model::create([
            'name' => "Md Abu Ehsan",
            'email' => "ehsan@gmail.com",
            'password' => Hash::make('@12345678'),
            'role_id' => 1,
        ]);

        self::$model::create([
            'name' => "Md Mumin",
            'email' => "mumin@gmail.com",
            'password' => Hash::make('@12345678'),

            'role_id' => 2,
        ]);
        self::$model::create([
            'name' => "Md Nazmul",
            'email' => "nazmul@gmail.com",
            'password' => Hash::make('@12345678'),

            'role_id' => 2,
        ]);
        self::$model::create([
            'name' => "Md Abir",
            'email' => "abir@gmail.com",
            'password' => Hash::make('@12345678'),

            'role_id' => 2,
        ]);
        self::$model::create([
            'name' => "Md Tasnim",
            'email' => "tasnim@gmail.com",
            'password' => Hash::make('@12345678'),

            'role_id' => 2,
        ]);
        self::$model::create([
            'name' => "Md Mahfuz",
            'email' => "mahfuz@gmail.com",
            'password' => Hash::make('@12345678'),

            'role_id' => 2,
        ]);
        self::$model::create([
            'name' => "Md Sahjalal",
            'email' => "sahjalal@gmail.com",
            'password' => Hash::make('@12345678'),

            'role_id' => 2,
        ]);
        self::$model::create([
            'name' => "Md Yamin",
            'email' => "yamin@gmail.com",
            'password' => Hash::make('@12345678'),
            'role_id' => 2,
        ]);
    }
}
