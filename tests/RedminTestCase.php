<?php

use Orchestra\Testbench\TestCase as TestBenchTestCase;

class RedminTestCase extends TestBenchTestCase {

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

    public function setUp()
    {
        parent::setUp();

        //$this->app['router']->enableFilters();

        ini_set('memory_limit','256M'); // Temporarily increase memory limit to 256MB

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

    protected function getPackageProviders()
    {
        return array('Redooor\Redminportal\RedminportalServiceProvider');
    }

    protected function getPackageAliases()
    {
        return array(
            'Redminportal' => 'Redooor\Redminportal\Facades\Redminportal'
        );
    }

}
