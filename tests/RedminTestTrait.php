<?php namespace Redooor\Redminportal\Test;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

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
        
        /* Required for Request::old to work */
        $app->make(Kernel::class)->pushMiddleware(StartSession::class);
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
