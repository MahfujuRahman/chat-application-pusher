<?php

namespace App\Modules\Management\Message\Seeder;

use Illuminate\Database\Seeder as SeederClass;
use Faker\Factory as Faker;

class Seeder extends SeederClass
{
    /**
     * Run the database seeds.
     php artisan db:seed --class="App\Modules\Management\Message\Seeder\Seeder"
     */
    static $model = \App\Modules\Management\Message\Models\ConversationModel::class;

    public function run(): void
    {
        $faker = Faker::create();
        self::$model::truncate();

        for ($i = 1; $i <= 3; $i++) {
            self::$model::create([
                'creator' => 1,
                'participant' => $faker->numberBetween(1, 3),
                'last_updated' => $faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d H:i:s'),
            ]);
        }
    }
}
