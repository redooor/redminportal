<?php

use Orchestra\Testbench\TestCase as TestBenchTestCase;

class RedminTestCase extends TestBenchTestCase
{
    /**
     * Overrides environment with in-memory sqlite database.
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['path.base'] = __DIR__ . '/../src';

        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', array(
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ));
    }
    
    /**
     * Sets up environment for each test.
     * Temporariliy increase memory limit, run migrations and set Mail::pretend to true.
     */
    public function setUp()
    {
        parent::setUp();

        //$this->app['router']->enableFilters();

        ini_set('memory_limit', '350M'); // Temporarily increase memory limit to 350MB
        
        /**
         * By default, Laravel keeps a log in memory of all queries that have been run for 
         * the current request. Disable logging for test to reduce memory.
         */
        DB::connection()->disableQueryLog();

        $artisan = $this->app->make('artisan');
        $artisan->call('migrate', [
            '--database' => 'testbench',
            '--path'     => '../src/migrations',
        ]);
        $artisan->call('migrate', [
            '--database' => 'testbench',
            '--package'  => 'Cartalyst/Sentry',
            '--path'     => '../vendor/cartalyst/sentry/src/migrations',
        ]);

        Mail::pretend(true);
    }

    /**
     * Points base path to testbench's fixture.
     */
    protected function getApplicationPaths()
    {
        $basePath = realpath(__DIR__.'/../vendor/orchestra/testbench/src/fixture');

        return array(
            'app'     => "{$basePath}/app",
            'public'  => realpath(__DIR__.'/fixture'),
            'base'    => $basePath,
            'storage' => "{$basePath}/app/storage",
        );
    }
    
    /**
     * Appends additional ServiceProvider for the test.
     */
    protected function getPackageProviders()
    {
        return array('Redooor\Redminportal\RedminportalServiceProvider');
    }

    /**
     * Appends additional Aliases for the test.
     */
    protected function getPackageAliases()
    {
        return array(
            'Redminportal' => 'Redooor\Redminportal\Facades\Redminportal'
        );
    }
}
