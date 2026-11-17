<?php namespace Redooor\Redminportal\Test;

use Database\Seeders\RedminSeeder;
use Illuminate\Support\Facades\Auth;
use Redooor\Redminportal\App\Models\Order;

/**
 * The order Revision History loads a fragment into an accessible modal instead of embedding an iframe.
 */
class OrderRevisionModalMarkupTest extends RedminBrowserTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->seed(RedminSeeder::class);
        Auth::guard('redminguard')->loginUsingId(1);

        Order::flushEventListeners();
        Order::boot();

        $order = new Order;
        $order->user_id = 1;
        $order->paid = 10.99;
        $order->transaction_id = 'TXN-' . uniqid();
        $order->payment_status = 'Pending';
        $order->save();
    }

    public function testOrderViewUsesFragmentModalWithoutIframe()
    {
        $response = $this->call('GET', '/admin/orders');

        $response->assertOk();
        $html = $response->getContent();

        $this->assertStringNotContainsString('<iframe', $html);
        $this->assertStringContainsString('data-fragment-modal="#revision-modal-window"', $html);
        $this->assertStringContainsString('data-fragment-body', $html);
        $this->assertStringContainsString('aria-labelledby="revision-modal-title"', $html);
        $this->assertStringContainsString('data-fragment-loading=', $html);
        $this->assertStringContainsString('data-fragment-loaded=', $html);
        $this->assertStringContainsString('data-fragment-error=', $html);
        $this->assertStringContainsString('data-fragment-unknown=', $html);
    }

    public function testMasterLayoutLoadsTheFragmentScript()
    {
        $layout = file_get_contents(dirname(__DIR__) . '/src/resources/views/layouts/master.blade.php');

        $this->assertStringContainsString('js/redmin-fragment.min.js', $layout);
    }

    public function testModalPartialWithoutTitleIdHasNoAriaLabelledby()
    {
        $html = view('redminportal::partials.modal-window', [
            'modal_id' => 'plain-modal',
            'modal_title' => 'Plain title',
            'modal_body' => 'Body',
        ])->render();

        $this->assertStringContainsString('<div id="plain-modal" class="modal fade" tabindex="-1">', $html);
        $this->assertStringNotContainsString('aria-labelledby', $html);
    }
}
