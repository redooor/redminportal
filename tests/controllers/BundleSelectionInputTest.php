<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Bundle;
use Redooor\Redminportal\App\Models\Membership;
use Redooor\Redminportal\App\Models\Module;
use Redooor\Redminportal\App\Models\Pricelist;
use Redooor\Redminportal\App\Models\Product;

/**
 * Covers missing, null and scalar product/pricelist selections on bundle store.
 */
class BundleSelectionInputTest extends BaseControllerTest
{
    use CharacterizationSupport;

    public function setUp(): void
    {
        parent::setUp();

        $this->page = '/admin/bundles';
        $this->viewhas = array(
            'singular' => 'bundle',
            'plural' => 'models'
        );

        $product = $this->createProductFixture('Base bundle product', 'BASE-BUNDLE-PRODUCT');

        $this->input = array(
            'create' => $this->bundleInput(array('product_id' => array($product->id))),
            'edit' => $this->bundleInput(array(
                'id' => 1,
                'product_id' => array($product->id)
            ))
        );

        $this->configureImageUploads();
    }

    public function tearDown(): void
    {
        $this->cleanupImageUploads();

        parent::tearDown();
    }

    /**
     * Test (Pass): product_id omitted but a valid pricelist is selected
     */
    public function testProductOmittedWithValidPricelistSaves()
    {
        $pricelist = $this->createPricelistFixture('Omitted product');

        $this->postStore($this->bundleInput(
            array('pricelist_id' => array($pricelist->id)),
            array('product_id')
        ));

        $this->assertRedirectedTo('/admin/bundles');
        $this->assertSame(1, Bundle::count());

        $bundle = Bundle::first();
        $this->assertSame(array($pricelist->id), $this->pivotIds($bundle, 'pricelists'));
        $this->assertSame(array(), $this->pivotIds($bundle, 'products'));
    }

    /**
     * Test (Pass): pricelist_id omitted but a valid product is selected
     */
    public function testPricelistOmittedWithValidProductSaves()
    {
        $product = $this->createProductFixture('Selected product', 'SELECTED-PRODUCT');

        $this->postStore($this->bundleInput(
            array('product_id' => array($product->id)),
            array('pricelist_id')
        ));

        $this->assertRedirectedTo('/admin/bundles');
        $this->assertSame(1, Bundle::count());

        $bundle = Bundle::first();
        $this->assertSame(array($product->id), $this->pivotIds($bundle, 'products'));
        $this->assertSame(array(), $this->pivotIds($bundle, 'pricelists'));
    }

    /**
     * Test (Fail): both fields omitted gives applyToError instead of a 500
     */
    public function testBothFieldsOmittedGivesApplyToError()
    {
        $this->postStore($this->bundleInput(
            array(),
            array('product_id', 'pricelist_id')
        ));

        $this->assertResponseStatus(302);
        $this->assertRedirectedTo('/admin/bundles/create');
        $this->assertErrorMessage(
            'applyToError',
            'You have not selected any Product or Membership/Module under Bundled Items.'
        );
        $this->assertSame(0, Bundle::count());
        $this->assertOldInput(array('name' => 'Bundle name'));
    }

    /**
     * Test (Fail): explicit null values give applyToError instead of a 500
     */
    public function testExplicitNullValuesGiveApplyToError()
    {
        $this->postStore($this->bundleInput(array(
            'product_id' => null,
            'pricelist_id' => null
        )));

        $this->assertResponseStatus(302);
        $this->assertRedirectedTo('/admin/bundles/create');
        $this->assertErrorMessage(
            'applyToError',
            'You have not selected any Product or Membership/Module under Bundled Items.'
        );
        $this->assertSame(0, Bundle::count());
    }

    /**
     * Test (Fail): a scalar product_id gives a validation error instead of a 500
     */
    public function testScalarProductIdGivesValidationError()
    {
        $this->postStore($this->bundleInput(array('product_id' => 1)));

        $this->assertResponseStatus(302);
        $this->assertRedirectedTo('/admin/bundles/create');
        $this->assertSessionHasErrors('product_id');
        $this->assertErrorMessage('product_id', 'The product id field must be an array.');
        $this->assertSame(0, Bundle::count());
    }

    /**
     * Build a valid bundle input, optionally removing keys
     */
    private function bundleInput(array $overrides = array(), array $remove = array())
    {
        $input = array_merge(array(
            'name' => 'Bundle name',
            'short_description' => 'Bundle short',
            'long_description' => 'Bundle long',
            'sku' => 'BUNDLE-SKU',
            'category_id' => 1,
            'price' => '12.50',
            'product_id' => array(1),
            'pricelist_id' => array()
        ), $overrides);

        foreach ($remove as $key) {
            unset($input[$key]);
        }

        return $input;
    }

    /**
     * Create a product fixture
     */
    private function createProductFixture($name, $sku)
    {
        return $this->createModel(Product::class, array(
            'name' => $name,
            'sku' => $sku,
            'short_description' => 'Short',
            'category_id' => 1,
            'active' => true
        ));
    }

    /**
     * Create a module, membership and pricelist fixture
     */
    private function createPricelistFixture($label)
    {
        $membership = new Membership;
        $membership->name = $label . ' membership';
        $membership->rank = 1;
        $membership->save();

        $module = new Module;
        $module->name = $label . ' module';
        $module->sku = strtoupper($label) . '-MODULE-SKU';
        $module->short_description = 'Short';
        $module->category_id = 1;
        $module->save();

        $pricelist = new Pricelist;
        $pricelist->module_id = $module->id;
        $pricelist->membership_id = $membership->id;
        $pricelist->price = 10.00;
        $pricelist->active = true;
        $pricelist->save();

        return $pricelist;
    }

    /**
     * Related pivot ids ordered by id
     */
    private function pivotIds($model, $relation)
    {
        $table = $model->$relation()->getRelated()->getTable();

        return $model->$relation()->orderBy($table . '.id')->pluck($table . '.id')->all();
    }
}
