<?php

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Status;

class StatusesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $uid=[1,2,3];
        $faker=app(Faker\Generator::class);
        $status=factory(Status::class)->times(100)->make()->each(function ($status) use($faker,$uid){
            $status->user_id = $faker->randomElement($uid);
            Status::insert($status->toArray());
        });
    }
}
