<?php namespace Redooor\Redminportal\Test;

use Orchestra\Testbench\BrowserKit\TestCase;

class RedminBrowserTestCase extends TestCase
{
    use RedminTestTrait;

    public $baseUrl = 'http://localhost';
}
