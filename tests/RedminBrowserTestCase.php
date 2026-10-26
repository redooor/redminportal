<?php namespace Redooor\Redminportal\Test;

use Orchestra\Testbench\TestCase;
use Illuminate\Testing\TestResponse;

class RedminBrowserTestCase extends TestCase
{
    use RedminTestTrait;

    protected TestResponse $response;

    public function call($method, $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null): TestResponse
    {
        return $this->response = parent::call($method, $uri, $parameters, $cookies, $files, $server, $content);
    }

    public function assertResponseOk(): void
    {
        $this->response->assertOk();
    }

    public function assertResponseStatus(int $status): void
    {
        $this->response->assertStatus($status);
    }

    public function assertRedirectedTo(string $url): void
    {
        $this->response->assertRedirect($url);
    }

    public function assertSessionHasErrors($keys = [], $format = null, $errorBag = 'default'): void
    {
        $this->response->assertSessionHasErrors($keys, $format, $errorBag);
    }

    public function assertSessionHas($key, $value = null): void
    {
        if ($value !== null) {
            $this->response->assertSessionHas($key, $value);
        } else {
            $this->response->assertSessionHas($key);
        }
    }

    public function assertViewHas($key, $value = null): void
    {
        if ($value !== null) {
            $this->response->assertViewHas($key, $value);
        } else {
            $this->response->assertViewHas($key);
        }
    }
}
