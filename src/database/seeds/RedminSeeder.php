<?php

use Illuminate\Database\Seeder;

class RedminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UserGroupSeeder::class);
    }
}
