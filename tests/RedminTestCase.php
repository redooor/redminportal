<?php namespace Redooor\Redminportal\Test;

use \Orchestra\Testbench\TestCase as TestBenchTestCase;
use Illuminate\Support\Facades\Config;

class RedminTestCase extends TestBenchTestCase
{
    use RedminTestTrait {
        getEnvironmentSetUp as protected getRedminEnvironmentSetUp;
        setUp as protected setUpRedminTestTrait;
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
     * Lowers the minimum password length to match the test administrator credentials.
     */
    public function setUp(): void
    {
        $this->setUpRedminTestTrait();

        Config::set('redminportal::password.min_length', 5);
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
