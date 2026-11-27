<?php namespace Redooor\Redminportal\Test;

use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * The redminsession group must read and write cookies in the same format as
 * Laravel's web group so a host can share cookies between the two.
 */
class AdminCookieSharingTest extends RedminBrowserTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        EncryptCookies::flushState();

        $this->registerCookieRoutes();
    }

    protected function tearDown(): void
    {
        EncryptCookies::flushState();

        parent::tearDown();
    }

    public function testLoginTokenCookieIsEncryptedAndDecryptable()
    {
        $this->call('GET', '/login')->assertOk();

        $cookie = $this->responseCookie('XSRF-TOKEN');
        $this->assertNotNull($cookie, 'The login page sets no token cookie');

        $this->app['session']->start();
        $token = $this->app['session']->token();

        $this->assertNotSame($token, $cookie->getValue(), 'The token cookie is not encrypted');

        $decrypted = $this->app['encrypter']->decrypt($cookie->getValue(), false);
        $this->assertIsString($decrypted, 'The token cookie does not decrypt');
        $this->assertStringContainsString($token, $decrypted);
    }

    public function testCookieSetByWebRouteIsReadableOnRedminsessionRoute()
    {
        $this->call('GET', '/test-cookies/web-set')->assertOk();

        $cookie = $this->responseCookie('shared_cookie');
        $this->assertNotNull($cookie, 'The web route sets no shared cookie');

        $response = $this->call('GET', '/test-cookies/redmin-read', [], [
            'shared_cookie' => $cookie->getValue(),
        ]);

        $response->assertOk();
        $this->assertSame('redmin-read:from-web', $response->getContent());
    }

    public function testCookieSetByRedminsessionRouteIsReadableOnWebRoute()
    {
        $this->call('GET', '/test-cookies/redmin-set')->assertOk();

        $cookie = $this->responseCookie('shared_cookie');
        $this->assertNotNull($cookie, 'The redminsession route sets no shared cookie');

        $response = $this->call('GET', '/test-cookies/web-read', [], [
            'shared_cookie' => $cookie->getValue(),
        ]);

        $response->assertOk();
        $this->assertSame('web-read:from-redmin', $response->getContent());
    }

    public function testHostCookieExceptionIsHonouredByRedminsessionGroup()
    {
        EncryptCookies::except(['plain_cookie']);

        $response = $this->call('GET', '/test-cookies/redmin-read-plain', [], [
            'plain_cookie' => 'plain-value',
        ]);

        $response->assertOk();
        $this->assertSame('plain:plain-value', $response->getContent());
    }

    private function registerCookieRoutes()
    {
        Route::middleware('web')->get('/test-cookies/web-set', function () {
            return response('web-set')->withCookie(cookie('shared_cookie', 'from-web', 60));
        });

        Route::middleware('web')->get('/test-cookies/web-read', function (Request $request) {
            return response('web-read:' . $request->cookie('shared_cookie'));
        });

        Route::middleware('redminsession')->get('/test-cookies/redmin-set', function () {
            return response('redmin-set')->withCookie(cookie('shared_cookie', 'from-redmin', 60));
        });

        Route::middleware('redminsession')->get('/test-cookies/redmin-read', function (Request $request) {
            return response('redmin-read:' . $request->cookie('shared_cookie'));
        });

        Route::middleware('redminsession')->get('/test-cookies/redmin-read-plain', function (Request $request) {
            return response('plain:' . $request->cookie('plain_cookie'));
        });
    }

    private function responseCookie($name)
    {
        foreach ($this->response->headers->getCookies() as $cookie) {
            if ($cookie->getName() === $name) {
                return $cookie;
            }
        }

        return null;
    }
}
