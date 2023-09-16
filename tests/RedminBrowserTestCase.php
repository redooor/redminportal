<?php namespace Redooor\Redminportal\Test;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Orchestra\Testbench\BrowserKit\TestCase;

class RedminBrowserTestCase extends TestCase
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
        
        // Set session for tests, otherwise Request::old will not work
        Config::set('session', [
            'driver'          => 'array',
            'lifetime'        => 120,
            'expire_on_close' => false,
            'encrypt'         => false,
            'lottery'         => [2, 100],
            'path'            => '/',
            'domain'          => 'localhost',
            'secure'          => false
        ]);
        
        /* Required for Request::old to work */
        $kernel = app('Illuminate\Contracts\Http\Kernel');
        $kernel->pushMiddleware('Illuminate\Session\Middleware\StartSession');
    }
    
    /**
     * Sets up environment for each test.
     * Temporariliy increase memory limit, run migrations and set Mail::pretend to true.
     */
    public function setUp(): void
    {
        parent::setUp();

        ini_set('memory_limit', '400M'); // Temporarily increase memory limit to 400MB
        
        /**
         * By default, Laravel keeps a log in memory of all queries that have been run for
         * the current request. Disable logging for test to reduce memory.
         */
        DB::disableQueryLog();

        $this->loadMigrationsFrom([
            '--database' => 'testing',
            '--path' => realpath(__DIR__.'/../src/database/migrations'),
        ]);
    }

    /**
     * Points base path to testbench's fixture.
     */
    protected function getApplicationPaths()
    {
        $basePath = realpath(__DIR__.'/../vendor/orchestra/testbench/fixture');

        return array(
            'app'     => "{$basePath}/app",
            'public'  => "{$basePath}/public",
            'base'    => $basePath,
            'storage' => "{$basePath}/storage",
        );
    }
    
    /**
     * Appends additional ServiceProvider for the test.
     */
    protected function getPackageProviders($app)
    {
        return [
            'Redooor\Redminportal\RedminportalServiceProvider',
            'Collective\Html\HtmlServiceProvider'
        ];
    }

    /**
     * Appends additional Aliases for the test.
     */
    protected function getPackageAliases($app)
    {
        return [
            'Redminportal' => 'Redooor\Redminportal\Facades\Redminportal',
            'Form'      => 'Collective\Html\FormFacade',
            'HTML'      => 'Collective\Html\HtmlFacade'
        ];
    }
}
