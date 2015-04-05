<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class RedminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Eloquent::unguard();

        $this->call('UserGroupSeeder');
    }
}
