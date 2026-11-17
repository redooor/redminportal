<?php namespace Redooor\Redminportal\Test;

use Database\Seeders\RedminSeeder;
use Illuminate\Support\Facades\Auth;
use Redooor\Redminportal\App\Models\Order;

/**
 * The built fragment module and its translations stay in sync with the
 * events and data attributes the fragment markup relies on.
 */
class FragmentModuleMarkupTest extends RedminTestCase
{
    public function testBuiltFragmentModuleContainsTheNewEventsAndAttributes()
    {
        $path = dirname(__DIR__, 2) . '/src/public/js/redmin-fragment.min.js';

        $this->assertFileExists($path);
        $script = file_get_contents($path);

        $this->assertStringContainsString('redmin:fragment-loaded', $script);
        $this->assertStringContainsString('redmin:fragment-clear', $script);
        $this->assertStringContainsString('data-fragment-form', $script);
        $this->assertStringContainsString('data-fragment-post', $script);
    }

    public function testFragmentUnknownTranslationExistsInAllLanguages()
    {
        $expected = [
            'en' => 'The result is unknown. Check the list before trying again.',
            'zh-cn' => '结果未知。请先检查列表，然后再试。',
            'zh-tw' => '結果未知。請先檢查列表，然後再試。',
        ];

        $original = app()->getLocale();

        foreach ($expected as $locale => $message) {
            app()->setLocale($locale);
            $this->assertSame($message, trans('redminportal::messages.fragment_unknown'));
        }

        app()->setLocale($original);
    }

    public function testOrderRevisionModalMarkupEmitsTheUnknownMessage()
    {
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

        $response = $this->call('GET', '/admin/orders');

        $response->assertOk();
        $this->assertStringContainsString('data-fragment-unknown=', $response->getContent());
    }
}
