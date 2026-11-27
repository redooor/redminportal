<?php namespace Redooor\Redminportal\Test;

use Database\Seeders\RedminSeeder;
use Illuminate\Support\Facades\DB;
use Redooor\Redminportal\App\Models\Bundle;
use Redooor\Redminportal\App\Models\Order;
use Redooor\Redminportal\App\Models\Product;
use Redooor\Redminportal\App\Models\User;

/**
 * Covers the validation of the selected products and bundles on the order form.
 */
class OrderInputValidationTest extends RedminBrowserTestCase
{
    private $product;
    private $bundle;
    private $email;

    public function setUp(): void
    {
        parent::setUp();

        $this->seed(RedminSeeder::class);
        $this->loginRedminUser(1);

        $this->email = User::find(1)->email;

        $this->product = new Product;
        $this->product->name = 'Validation product';
        $this->product->sku = 'VALIDATION-PRODUCT';
        $this->product->short_description = 'Body';
        $this->product->active = true;
        $this->product->save();

        $this->bundle = new Bundle;
        $this->bundle->name = 'Validation bundle';
        $this->bundle->sku = 'VALIDATION-BUNDLE';
        $this->bundle->short_description = 'Body';
        $this->bundle->active = true;
        $this->bundle->save();
    }

    public function testMalformedJsonItemIsRejected()
    {
        $this->assertRejectedItem('not-json', 'productError');
    }

    public function testJsonArrayItemIsRejected()
    {
        $this->assertRejectedItem(
            json_encode(array(array('id' => $this->product->id, 'quantity' => 1))),
            'productError'
        );
    }

    public function testMissingIdIsRejected()
    {
        $this->assertRejectedItem(json_encode(array('quantity' => 1)), 'productError');
    }

    public function testZeroIdIsRejected()
    {
        $this->assertRejectedItem(json_encode(array('id' => 0, 'quantity' => 1)), 'productError');
    }

    public function testNonNumericQuantityIsRejected()
    {
        $this->assertRejectedItem(
            json_encode(array('id' => $this->product->id, 'quantity' => 'not-a-number')),
            'productError'
        );
    }

    public function testZeroQuantityIsRejected()
    {
        $this->assertRejectedItem(json_encode(array('id' => $this->product->id, 'quantity' => 0)), 'productError');
    }

    public function testNegativeQuantityIsRejected()
    {
        $this->assertRejectedItem(json_encode(array('id' => $this->product->id, 'quantity' => -1)), 'productError');
    }

    public function testQuantityAboveMaximumIsRejected()
    {
        $this->assertRejectedItem(json_encode(array('id' => $this->product->id, 'quantity' => 1001)), 'productError');
    }

    public function testMalformedBundleItemIsRejected()
    {
        $this->assertRejectedItem('not-json', 'bundleError', 'selected_bundles');
    }

    public function testInvalidBundleRejectsTheValidProductToo()
    {
        $input = $this->orderInput();
        $input['selected_products'] = array($this->productItem(2));
        $input['selected_bundles'] = array('not-json');

        $this->call('POST', '/admin/orders/store', $input);

        $this->assertRedirectedTo('/admin/orders/create');
        $this->assertSessionHasErrors('bundleError');
        $this->assertSame(0, Order::count());
        $this->assertSame(0, DB::table('order_product')->count());
        $this->assertSame(0, DB::table('bundle_order')->count());
    }

    public function testMaximumQuantityIsAccepted()
    {
        $input = $this->orderInput();
        $input['selected_products'] = array($this->productItem(1000));

        $this->call('POST', '/admin/orders/store', $input);

        $this->assertRedirectedTo('/admin/orders');
        $this->assertSame(1, Order::count());
        $this->assertSame(1000, DB::table('order_product')->count());
    }

    public function testNestedPricelistValueIsIgnoredWithoutAnError()
    {
        $input = $this->orderInput();
        $input['pricelist_id'] = array(array(1, 2), array('id' => 1));

        $this->call('POST', '/admin/orders/store', $input);

        $this->assertRedirectedTo('/admin/orders/create');
        $this->assertSessionHasErrors('productError');
        $this->assertSame(0, Order::count());
    }

    public function testSuppliedSelectionThatIsNotAListIsRejected()
    {
        $input = $this->orderInput();
        $input['selected_products'] = 'not-json';
        $input['selected_bundles'] = array(json_encode(array('id' => $this->bundle->id, 'quantity' => 1)));

        $this->call('POST', '/admin/orders/store', $input);

        $this->assertRedirectedTo('/admin/orders/create');
        $this->assertSessionHasErrors('productError');
        $this->assertSame(0, Order::count());
        $this->assertSame(0, DB::table('bundle_order')->count());
    }

    public function testEmptySelectionStringIsTreatedAsMissing()
    {
        $input = $this->orderInput();
        $input['selected_products'] = '';
        $input['selected_bundles'] = array(json_encode(array('id' => $this->bundle->id, 'quantity' => 1)));

        $this->call('POST', '/admin/orders/store', $input);

        $this->assertRedirectedTo('/admin/orders');
        $this->assertSame(1, Order::count());
    }

    private function assertRejectedItem($item, $errorKey, $field = 'selected_products')
    {
        $input = $this->orderInput();
        $input[$field] = array($item);

        $this->call('POST', '/admin/orders/store', $input);

        $this->assertRedirectedTo('/admin/orders/create');
        $this->assertSessionHasErrors($errorKey);
        $this->assertSame(0, Order::count());
        $this->assertSame(0, DB::table('order_product')->count());
        $this->assertSame(0, DB::table('bundle_order')->count());
    }

    private function orderInput()
    {
        return array(
            'payment_status' => 'Completed',
            'paid' => 1.99,
            'transaction_id' => 'TX-VALIDATION-1',
            'email' => $this->email,
        );
    }

    private function productItem($quantity)
    {
        return json_encode(array('id' => $this->product->id, 'quantity' => $quantity));
    }
}
