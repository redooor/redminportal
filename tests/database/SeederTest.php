<?php namespace Redooor\Redminportal\Test;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Redooor\Redminportal\App\Models\Group;
use Redooor\Redminportal\App\Models\User;
use UserGroupSeeder;

class SeederTest extends RedminTestCase
{
    public function tearDown(): void
    {
        $this->clearAdminEnvironment();

        parent::tearDown();
    }

    public function testRandomPasswordIsGeneratedAndPrintedWhenPasswordIsNotSet()
    {
        $this->clearAdminEnvironment();
        $command = $this->fakeCommand();

        $seeder = new UserGroupSeeder;
        $seeder->setCommand($command);
        $seeder->run();

        $this->assertCount(1, $command->infos);
        $this->assertSame(1, preg_match('/^Administrator: admin@example\.com \/ ([A-Za-z0-9]+)$/', $command->infos[0], $matches));

        $password = $matches[1];
        $this->assertSame(20, strlen($password));

        $user = User::where('email', 'admin@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame(1, (int) $user->activated);
        $this->assertTrue(Hash::check($password, $user->password));
        $this->assertSame(1, User::count());
        $this->assertCount(0, $command->warnings);

        $secondCommand = $this->fakeCommand();
        $secondRun = new UserGroupSeeder;
        $secondRun->setCommand($secondCommand);
        $secondRun->run();

        $this->assertCount(0, $secondCommand->infos);
        $this->assertCount(1, $secondCommand->warnings);
    }

    public function testRandomPasswordIsGeneratedWhenPasswordIsEmpty()
    {
        $this->setAdminEnvironment('empty@example.com', '');
        $command = $this->fakeCommand();

        $seeder = new UserGroupSeeder;
        $seeder->setCommand($command);
        $seeder->run();

        $this->assertCount(1, $command->infos);
        $this->assertSame(1, preg_match('/^Administrator: empty@example\.com \/ ([A-Za-z0-9]+)$/', $command->infos[0], $matches));

        $user = User::where('email', 'empty@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue(Hash::check($matches[1], $user->password));
    }

    public function testCredentialsFromEnvironmentAreUsed()
    {
        $this->setAdminEnvironment('owner@example.com', 'custom-password');
        $command = $this->fakeCommand();

        $seeder = new UserGroupSeeder;
        $seeder->setCommand($command);
        $seeder->run();

        $user = User::where('email', 'owner@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame(1, (int) $user->activated);
        $this->assertTrue(Hash::check('custom-password', $user->password));
        $this->assertCount(0, $command->infos);
        $this->assertCount(0, $command->warnings);
    }

    public function testShortEnvironmentPasswordIsRejectedBeforeCreatingAnything()
    {
        Config::set('redminportal::password.min_length', 10);
        $this->setAdminEnvironment('short@example.com', 'short');

        try {
            (new UserGroupSeeder)->run();
            $this->fail('The seeder accepted a password below the minimum length.');
        } catch (\RuntimeException $exception) {
            $this->assertSame(
                'REDMIN_ADMIN_PASSWORD must be at least 10 characters.',
                $exception->getMessage()
            );
        }

        $this->assertSame(0, User::count());
        $this->assertSame(0, Group::count());
    }

    public function testEnvironmentPasswordAtTheMinimumLengthIsAccepted()
    {
        Config::set('redminportal::password.min_length', 10);
        $this->setAdminEnvironment('long@example.com', '0123456789');

        (new UserGroupSeeder)->run();

        $user = User::where('email', 'long@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue(Hash::check('0123456789', $user->password));
    }

    public function testSecondRunDoesNotDeleteOrDuplicateUsers()
    {
        Config::set('redminportal::password.min_length', 10);
        $this->setAdminEnvironment('admin@admin.com', 'admin-password');

        $firstRun = new UserGroupSeeder;
        $firstRun->run();

        $other = $this->createUser('other@example.com', 'other-password');
        $userCount = User::count();

        $command = $this->fakeCommand();
        $secondRun = new UserGroupSeeder;
        $secondRun->setCommand($command);
        $secondRun->run();

        $this->assertSame($userCount, User::count());
        $this->assertSame(1, User::where('email', 'admin@admin.com')->count());
        $this->assertNotNull(User::find($other->id));
        $this->assertSame(1, Group::where('name', 'Admin')->count());
        $this->assertSame(1, Group::where('name', 'User')->count());
        $this->assertCount(1, $command->warnings);
        $this->assertCount(0, $command->infos);
    }

    public function testExistingUsersGroupsAndMembershipsAreNotDeleted()
    {
        $this->clearAdminEnvironment();

        $user = $this->createUser('existing@example.com', 'existing-password');
        $group = $this->createGroup('Custom', array('admin.view' => 0));
        $user->groups()->save($group);

        $command = $this->fakeCommand();
        $seeder = new UserGroupSeeder;
        $seeder->setCommand($command);
        $seeder->run();

        $this->assertSame(1, User::count());
        $this->assertDatabaseHas('users', array('id' => $user->id, 'email' => 'existing@example.com'));
        $this->assertDatabaseHas('groups', array('id' => $group->id, 'name' => 'Custom'));
        $this->assertDatabaseHas('users_groups', array('user_id' => $user->id, 'group_id' => $group->id));
        $this->assertCount(1, $command->warnings);
        $this->assertCount(0, $command->infos);
    }

    public function testExistingGroupsAreKeptWhenCreatingTheAdministrator()
    {
        Config::set('redminportal::password.min_length', 10);
        $this->setAdminEnvironment('admin@example.com', 'admin-password');
        $this->createGroup('Admin', array('admin.view' => 1));
        $custom = $this->createGroup('Custom', array('admin.view' => 0));

        $seeder = new UserGroupSeeder;
        $seeder->run();

        $this->assertSame(1, Group::where('name', 'Admin')->count());
        $this->assertSame(1, Group::where('name', 'User')->count());
        $this->assertNotNull(Group::find($custom->id));
        $this->assertSame(1, User::count());
    }

    private function fakeCommand()
    {
        return new class extends Command {
            public $infos = array();
            public $warnings = array();
            protected $signature = 'test:seeder';

            public function info($string, $verbosity = null)
            {
                $this->infos[] = $string;
            }

            public function warn($string, $verbosity = null)
            {
                $this->warnings[] = $string;
            }
        };
    }

    private function createUser($email, $password)
    {
        $user = new User;
        $user->email = $email;
        $user->password = Hash::make($password);
        $user->first_name = 'Test';
        $user->last_name = 'User';
        $user->activated = 1;
        $user->save();

        return $user;
    }

    private function createGroup($name, $permissions)
    {
        $group = new Group;
        $group->name = $name;
        $group->permissions = json_encode($permissions);
        $group->save();

        return $group;
    }

    private function setAdminEnvironment($email, $password)
    {
        putenv('REDMIN_ADMIN_EMAIL=' . $email);
        putenv('REDMIN_ADMIN_PASSWORD=' . $password);
        $_ENV['REDMIN_ADMIN_EMAIL'] = $email;
        $_ENV['REDMIN_ADMIN_PASSWORD'] = $password;
        $_SERVER['REDMIN_ADMIN_EMAIL'] = $email;
        $_SERVER['REDMIN_ADMIN_PASSWORD'] = $password;
    }

    private function clearAdminEnvironment()
    {
        putenv('REDMIN_ADMIN_EMAIL');
        putenv('REDMIN_ADMIN_PASSWORD');
        unset($_ENV['REDMIN_ADMIN_EMAIL'], $_ENV['REDMIN_ADMIN_PASSWORD']);
        unset($_SERVER['REDMIN_ADMIN_EMAIL'], $_SERVER['REDMIN_ADMIN_PASSWORD']);
    }
}
