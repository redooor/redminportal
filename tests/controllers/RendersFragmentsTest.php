<?php namespace Redooor\Redminportal\Test;

use Illuminate\Support\Facades\Route;
use Redooor\Redminportal\App\Http\Concerns\RendersFragments;

/**
 * Test controller for the RendersFragments trait.
 */
class RendersFragmentsTestController
{
    use RendersFragments;

    public function show($view)
    {
        return $this->fragmentResponse('fragment-tests::' . $view);
    }
}

/**
 * Covers the Ajax fragment rendering helper and its full-document guard.
 */
class RendersFragmentsTest extends RedminTestCase
{
    private $viewDirectory;

    public function setUp(): void
    {
        parent::setUp();

        $this->viewDirectory = sys_get_temp_dir() . '/redminportal-fragment-response-' . uniqid();
        mkdir($this->viewDirectory, 0777, true);

        file_put_contents($this->viewDirectory . '/plain.blade.php', '<div id="plain-fragment">Plain fragment</div>');
        file_put_contents($this->viewDirectory . '/doctype.blade.php', '<!DOCTYPE html><html><body>Full document</body></html>');
        file_put_contents($this->viewDirectory . '/indented-html.blade.php', '  <html><body>Indented document</body></html>');

        view()->addNamespace('fragment-tests', $this->viewDirectory);

        Route::get('/redmin-fragment-response/{view}', [RendersFragmentsTestController::class, 'show']);
    }

    public function tearDown(): void
    {
        foreach (glob($this->viewDirectory . '/*.blade.php') as $file) {
            @unlink($file);
        }
        @rmdir($this->viewDirectory);

        parent::tearDown();
    }

    /**
     * Test (Pass): Ajax returns the rendered view with the fragment headers.
     */
    public function testAjaxReturnsAFragment()
    {
        $response = $this->get('/redmin-fragment-response/plain', ['X-Requested-With' => 'XMLHttpRequest']);

        $response->assertOk();
        $response->assertHeader('X-Redmin-Fragment', '1');
        $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $response->assertSee('Plain fragment');
        $response->assertDontSee('<html', false);
    }

    /**
     * Test (Pass): a normal request returns the view without the fragment header.
     */
    public function testNonAjaxReturnsAPage()
    {
        $response = $this->get('/redmin-fragment-response/plain');

        $response->assertOk();
        $response->assertHeaderMissing('X-Redmin-Fragment');
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $response->assertSee('Plain fragment');
    }

    /**
     * Test (Pass): a full document loses the fragment header.
     */
    public function testAjaxFullDoctypeDocumentIsNotAFragment()
    {
        $response = $this->get('/redmin-fragment-response/doctype', ['X-Requested-With' => 'XMLHttpRequest']);

        $response->assertOk();
        $response->assertHeaderMissing('X-Redmin-Fragment');
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $response->assertSee('<!DOCTYPE html>', false);
    }

    /**
     * Test (Pass): leading whitespace before an html document loses the fragment header.
     */
    public function testAjaxIndentedHtmlDocumentIsNotAFragment()
    {
        $response = $this->get('/redmin-fragment-response/indented-html', ['X-Requested-With' => 'XMLHttpRequest']);

        $response->assertOk();
        $response->assertHeaderMissing('X-Redmin-Fragment');
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $response->assertSee('Indented document');
    }
}
