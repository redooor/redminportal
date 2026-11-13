<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Redooor\Redminportal\App\Models\User;
use Redooor\Redminportal\App\Models\Group;
use RuntimeException;

class RedminDemoSeeder extends Seeder
{
    public function run()
    {
        if (! app()->environment('local', 'development', 'testing')) {
            throw new RuntimeException('The demo seeder only runs in the local, development or testing environment.');
        }

        $admin_group = $this->findOrCreateGroup('Admin', [
            'admin.view' => 1,
            'admin.create' => 1,
            'admin.delete' => 1,
            'admin.update' => 1,
        ]);

        $this->findOrCreateGroup('User', [
            'admin.view' => 0,
            'admin.create' => 0,
            'admin.delete' => 0,
            'admin.update' => 0,
        ]);

        $user = User::where('email', 'admin@admin.com')->first();

        if ($user === null) {
            $user = new User;
            $user->email        = 'admin@admin.com';
            $user->password     = Hash::make('admin');
            $user->first_name   = 'System';
            $user->last_name    = 'Admin';
            $user->activated    = 1;
            $user->save();
        }

        if (! $user->groups()->where('groups.id', $admin_group->id)->exists()) {
            $user->groups()->save($admin_group);
        }
    }

    /**
     * Find a group by name or create it with the given permissions.
     *
     * @param string $name
     * @param array $permissions
     * @return \Redooor\Redminportal\App\Models\Group
     */
    private function findOrCreateGroup($name, array $permissions)
    {
        $group = Group::where('name', $name)->first();

        if ($group === null) {
            $group = new Group;
            $group->name = $name;
            $group->permissions = json_encode($permissions);
            $group->save();
        }

        return $group;
    }
}
