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
     * Exclude the Redmin trait from automatic setup, since it is called explicitly below.
     *
     * @param string $use
     * @return bool
     */
    protected function setUpTheTestEnvironmentTraitToBeIgnored(string $use): bool
    {
        if ($use === RedminTestTrait::class) {
            return true;
        }

        return parent::setUpTheTestEnvironmentTraitToBeIgnored($use);
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
