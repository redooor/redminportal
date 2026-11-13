<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Redooor\Redminportal\App\Models\User;
use Redooor\Redminportal\App\Models\Group;
use RuntimeException;

class UserGroupSeeder extends Seeder
{
    public function run()
    {
        $password = $this->environment('REDMIN_ADMIN_PASSWORD');
        $generated = false;

        if ($password === null || $password === '') {
            $password = Str::password(20, true, true, false);
            $generated = true;
        } else {
            $minLength = config('redminportal::password.min_length', 10);

            if (mb_strlen($password) < $minLength) {
                throw new RuntimeException(
                    "REDMIN_ADMIN_PASSWORD must be at least {$minLength} characters."
                );
            }
        }

        if (DB::table('users')->exists()) {
            if (isset($this->command)) {
                $this->command->warn('The users table already contains records. No users or groups were created.');
            }

            return;
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

        $email = $this->environment('REDMIN_ADMIN_EMAIL');

        if ($email === null || $email === '') {
            $email = 'admin@example.com';
        }

        $user = new User;
        $user->email        = $email;
        $user->password     = Hash::make($password);
        $user->first_name   = 'System';
        $user->last_name    = 'Admin';
        $user->activated    = 1;
        $user->save();

        // Assign user permissions
        $user->groups()->save($admin_group);

        if ($generated && isset($this->command) && app()->runningInConsole()) {
            $this->command->info("Administrator: {$email} / {$password}");
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

    /**
     * Read an environment variable set through putenv() or the environment arrays.
     *
     * @param string $name
     * @return string|null
     */
    private function environment($name)
    {
        $value = getenv($name);

        if ($value === false) {
            $value = $_ENV[$name] ?? null;
        }

        if ($value === false || $value === null) {
            $value = $_SERVER[$name] ?? null;
        }

        return $value;
    }
}
