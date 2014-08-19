<?php

use Cartalyst\Sentry\Facades\Laravel\Sentry;

class SentrySeeder extends Seeder {

    public function run()
    {
        DB::table('users')->delete();
        DB::table('groups')->delete();
        DB::table('users_groups')->delete();

        Sentry::getUserProvider()->create(array(
            'email'       => 'admin@admin.com',
            'password'    => "admin",
            'first_name'  => 'System',
            'last_name'   => 'Admin',
            'activated'   => 1,
        ));

        Sentry::getGroupProvider()->create(array(
            'name'        => 'Admin',
            'permissions' => array('admin' => 1),
        ));

        Sentry::getGroupProvider()->create(array(
            'name'        => 'User',
            'permissions' => array('user' => 1),
        ));

        // Assign user permissions
        $adminUser  = Sentry::getUserProvider()->findByLogin('admin@admin.com');
        $adminGroup = Sentry::getGroupProvider()->findByName('Admin');
        $adminUser->addGroup($adminGroup);
    }

}
