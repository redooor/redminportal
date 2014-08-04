<?php

class RedminTestCase extends \Illuminate\Foundation\Testing\TestCase {

    /**
     * Creates the application.
     *
     * @return \Symfony\Component\HttpKernel\HttpKernelInterface
     */
    public function createApplication()
    {
        ini_set('memory_limit','256M'); // Temporarily increase memory limit to 256MB

        $unitTesting = true;

        $testEnvironment = 'testing';

        return require __DIR__.'/../../../../bootstrap/start.php';
    }

    /**
     * Default preparation for each test
     */
    public function setUp()
    {
        parent::setUp();

        $this->prepareForTests();
    }

    /**
     * Migrates the database and set the mailer to 'pretend'.
     * This will cause the tests to run quickly.
     */
    private function prepareForTests()
    {
        //Artisan::call('migrate');
        Artisan::call('migrate', array('--package' => 'Cartalyst/Sentry'));
        Artisan::call('migrate', array('--bench' => 'Redooor/Redminportal'));
        Mail::pretend(true);
    }

}
