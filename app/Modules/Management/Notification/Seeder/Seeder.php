<?php
namespace App\Modules\Management\Notification\Seeder;

use Illuminate\Database\Seeder as SeederClass;
use Faker\Factory as Faker;

class Seeder extends SeederClass
{
    /**
     * Run the database seeds.
     php artisan db:seed --class="App\Modules\Management\Notification\Seeder\Seeder"
     */
    static $model = \App\Modules\Management\Notification\Models\Model::class;

    public function run(): void
    {
        $faker = Faker::create();
        self::$model::truncate();

        for ($i = 1; $i <= 100; $i++) {
            self::$model::create([                'type' => null,
                'title' => $faker->sentence,
                'link' => $faker->sentence,
            ]);
        }
    }
}