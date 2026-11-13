<?php namespace Redooor\Redminportal\Test;

use \Orchestra\Testbench\TestCase as TestBenchTestCase;

class RedminTestCase extends TestBenchTestCase
{
    use RedminTestTrait {
        getEnvironmentSetUp as protected getRedminEnvironmentSetUp;
    }

    /**
     * Overrides environment with in-memory sqlite database and test administrator credentials.
     */
    protected function getEnvironmentSetUp($app)
    {
        putenv('REDMIN_ADMIN_EMAIL=admin@admin.com');
        putenv('REDMIN_ADMIN_PASSWORD=admin');

        $this->getRedminEnvironmentSetUp($app);
    }

    /**
     * Clears the test administrator credentials after each test.
     */
    protected function tearDown(): void
    {
        putenv('REDMIN_ADMIN_EMAIL');
        putenv('REDMIN_ADMIN_PASSWORD');

        parent::tearDown();
    }
}
