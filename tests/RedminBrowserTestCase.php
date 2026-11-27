<?php namespace Redooor\Redminportal\Test;

use Orchestra\Testbench\BrowserKit\TestCase;
use Illuminate\Support\Facades\Config;

class RedminBrowserTestCase extends TestCase
{
    use RedminTestTrait {
        getEnvironmentSetUp as protected getRedminEnvironmentSetUp;
        setUp as protected setUpRedminTestTrait;
    }

    public $baseUrl = 'http://localhost';

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
