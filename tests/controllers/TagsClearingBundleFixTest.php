<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Bundle;
use Redooor\Redminportal\App\Models\Category;
use Redooor\Redminportal\App\Models\Product;

/**
 * Covers clearing and keeping tags on bundle save.
 */
class TagsClearingBundleFixTest extends BaseControllerTest
{
    use CharacterizationSupport;

    protected $seedProduct;

    public function setUp(): void
    {
        parent::setUp();

        $this->createModel(Category::class, array(
            'name' => 'Bundle category',
            'short_description' => 'Short',
            'order' => 1
        ));

        $this->seedProduct = $this->createModel(Product::class, array(
            'name' => 'Bundled product',
            'sku' => 'BUNDLED-PRODUCT',
            'short_description' => 'Short',
            'category_id' => 1,
            'active' => true
        ));

        $this->page = '/admin/bundles';
        $this->viewhas = array(
            'singular' => 'bundle',
            'plural' => 'models'
        );
        $this->input = array(
            'create' => $this->bundleInput(array(
                'name' => 'Base bundle',
                'sku' => 'BASE-BUNDLE'
            )),
            'edit' => $this->bundleInput(array(
                'id' => 1,
                'name' => 'Base bundle',
                'sku' => 'BASE-BUNDLE'
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
     * Test (Pass): omitted tags are kept, empty and null clear them and values replace them
     */
    public function testOmittedTagsAreKeptAndEmptyOrNullTagsClearThem()
    {
        $this->postStore($this->bundleInput(array(
            'name' => 'Tagged bundle',
            'sku' => 'TAGGED-BUNDLE',
            'tags' => 'alpha,beta'
        )));

        $this->assertRedirectedTo('/admin/bundles');
        $bundle = Bundle::first();
        $this->assertSame(array('alpha', 'beta'), $this->tagNames($bundle));

        $this->postStore($this->bundleInput(array(
            'id' => $bundle->id,
            'name' => 'Tagged bundle updated',
            'sku' => 'TAGGED-BUNDLE'
        )));

        $this->assertRedirectedTo('/admin/bundles');
        $this->assertSame(array('alpha', 'beta'), $this->tagNames(Bundle::find($bundle->id)));

        $this->postStore($this->bundleInput(array(
            'id' => $bundle->id,
            'name' => 'Tagged bundle updated',
            'sku' => 'TAGGED-BUNDLE',
            'tags' => ''
        )));

        $this->assertRedirectedTo('/admin/bundles');
        $this->assertSame(array(), $this->tagNames(Bundle::find($bundle->id)));

        $this->postStore($this->bundleInput(array(
            'id' => $bundle->id,
            'name' => 'Tagged bundle updated',
            'sku' => 'TAGGED-BUNDLE',
            'tags' => 'gamma,delta'
        )));

        $this->assertRedirectedTo('/admin/bundles');
        $this->assertSame(array('gamma', 'delta'), $this->tagNames(Bundle::find($bundle->id)));

        $this->postStore($this->bundleInput(array(
            'id' => $bundle->id,
            'name' => 'Tagged bundle updated',
            'sku' => 'TAGGED-BUNDLE',
            'tags' => null
        )));

        $this->assertRedirectedTo('/admin/bundles');
        $this->assertSame(array(), $this->tagNames(Bundle::find($bundle->id)));
    }

    /**
     * Build a valid bundle input
     */
    private function bundleInput(array $overrides = array())
    {
        return array_merge(array(
            'name' => 'Bundle name',
            'short_description' => 'Bundle short',
            'long_description' => 'Bundle long',
            'sku' => 'BUNDLE-SKU',
            'category_id' => 1,
            'price' => '12.50',
            'product_id' => array($this->seedProduct->id),
            'pricelist_id' => array()
        ), $overrides);
    }
}
