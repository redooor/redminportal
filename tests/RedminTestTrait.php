<?php namespace Redooor\Redminportal\Test;

use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Redooor\Redminportal\App\Models\User;

trait RedminTestTrait
{
    /**
     * Overrides environment with in-memory sqlite database.
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['path.base'] = __DIR__ . '/../src';

        // Setup default database to use sqlite :memory:
        Config::set('database.default', 'testbench');
        Config::set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    /**
     * Sets up environment for each test.
     * Temporarily increase memory limit, run migrations and set Mail::pretend to true.
     */
    public function setUp(): void
    {
        parent::setUp();

        ini_set('memory_limit', '1G'); // Temporarily increase memory limit; the controller tests keep their app alive until the run ends

        /**
         * By default, Laravel keeps a log in memory of all queries that have been run for
         * the current request. Disable logging for test to reduce memory.
         */
        DB::disableQueryLog();

        $this->loadMigrationsFrom(realpath(__DIR__.'/../src/database/migrations'));
    }

    /**
     * Sign in a redminguard user and record the password fingerprint for the session.
     *
     * @param mixed $user User model or user id
     * @return \Redooor\Redminportal\App\Models\User|null
     */
    protected function loginRedminUser($user)
    {
        if (is_object($user)) {
            $id = $user->getAuthIdentifier();
        } else {
            $id = $user;
            $user = User::find($id);
        }

        Auth::guard('redminguard')->loginUsingId($id);

        if ($user != null) {
            $this->storeRedminFingerprint($user);
        }

        return $user;
    }

    /**
     * Store the current password fingerprint for a signed-in user.
     *
     * @param \Redooor\Redminportal\App\Models\User|null $user Defaults to the signed-in user
     * @return void
     */
    protected function storeRedminFingerprint($user = null)
    {
        if ($user === null) {
            $user = Auth::guard('redminguard')->user();
        }

        if ($user === null) {
            return;
        }

        $this->app['session']->put('redmin_fingerprint_user', $user->id);
        $this->app['session']->put('redmin_password_fingerprint', hash('sha256', $user->password));
    }

    /**
     * Appends additional ServiceProvider for the test.
     */
    protected function getPackageProviders($app)
    {
        return [
            'Redooor\Redminportal\RedminportalServiceProvider',
        ];
    }

    /**
     * Appends additional Aliases for the test.
     */
    protected function getPackageAliases($app)
    {
        return [
            'Redminportal' => 'Redooor\Redminportal\Facades\Redminportal',
        ];
    }
}
