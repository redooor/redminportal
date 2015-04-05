<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Redooor\Redminportal\App\Models\User;
use Redooor\Redminportal\App\Models\Group;

class UserGroupSeeder extends Seeder
{
    public function run()
    {
        DB::table('users')->delete();
        DB::table('groups')->delete();
        DB::table('users_groups')->delete();
        
        $user = new User;
        $user->email        = 'admin@admin.com';
        $user->password     = \Hash::make("admin");
        $user->first_name   = 'System';
        $user->last_name    = 'Admin';
        $user->activated    = 1;
        $user->save();
        
        $admin_group = new Group;
        $admin_group->name = 'Admin';
        $admin_group->permissions = json_encode(array(
            'admin.view' => 1,
            'admin.create' => 1,
            'admin.delete' => 1,
            'admin.update' => 1
        ));
        $admin_group->save();
        
        $user_group = new Group;
        $user_group->name = 'User';
        $user_group->permissions = json_encode(array(
            'admin.view' => 0,
            'admin.create' => 0,
            'admin.delete' => 0,
            'admin.update' => 0
        ));
        $user_group->save();

        // Assign user permissions
        $user->groups()->save($admin_group);
    }
}
