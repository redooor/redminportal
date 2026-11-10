<?php namespace Redooor\Redminportal\Test;

use PHPUnit\Framework\TestCase;

/**
 * The login and admin routes must encrypt the session cookie like Laravel's own "web" group does. Otherwise the cookie
 * they set is plain, the "web" group (used by any host route, such as a 404 fallback) cannot read it, starts a new
 * session and replaces the cookie, which logs the admin out.
 */
class SessionCookieEncryptionTest extends TestCase
{
    public function testSessionGroupEncryptsCookiesBeforeStartingTheSession()
    {
        $source = file_get_contents(dirname(__DIR__) . '/src/RedminportalServiceProvider.php');

        preg_match("/middlewareGroup\('redminsession', \[(.*?)\]\)/s", $source, $group);

        $this->assertNotEmpty($group, 'redminsession group not found');

        $encrypt = strpos($group[1], 'EncryptCookies::class');
        $queued = strpos($group[1], 'AddQueuedCookiesToResponse::class');
        $session = strpos($group[1], 'StartSession::class');

        $this->assertNotFalse($encrypt, 'redminsession does not encrypt cookies');
        $this->assertLessThan($queued, $encrypt);
        $this->assertLessThan($session, $encrypt);
    }
}
