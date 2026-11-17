<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Product;

/**
 * Covers clearing and keeping tags on product save.
 */
class TagsClearingProductFixTest extends BaseControllerTest
{
    use CharacterizationSupport;

    public function setUp(): void
    {
        parent::setUp();

        $this->page = '/admin/products';
        $this->viewhas = array(
            'singular' => 'product',
            'plural' => 'models'
        );
        $this->input = array(
            'create' => $this->productInput(array(
                'name' => 'Base product',
                'sku' => 'BASE-PRODUCT'
            )),
            'edit' => $this->productInput(array(
                'id' => 1,
                'name' => 'Base product',
                'sku' => 'BASE-PRODUCT'
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
        $this->postStore($this->productInput(array(
            'name' => 'Tagged product',
            'sku' => 'TAGGED-PRODUCT',
            'tags' => 'alpha,beta'
        )));

        $this->assertRedirectedTo('/admin/products');
        $product = Product::first();
        $this->assertSame(array('alpha', 'beta'), $this->tagNames($product));

        $this->postStore($this->productInput(array(
            'id' => $product->id,
            'name' => 'Tagged product updated',
            'sku' => 'TAGGED-PRODUCT'
        )));

        $this->assertRedirectedTo('/admin/products');
        $this->assertSame(array('alpha', 'beta'), $this->tagNames(Product::find($product->id)));

        $this->postStore($this->productInput(array(
            'id' => $product->id,
            'name' => 'Tagged product updated',
            'sku' => 'TAGGED-PRODUCT',
            'tags' => ''
        )));

        $this->assertRedirectedTo('/admin/products');
        $this->assertSame(array(), $this->tagNames(Product::find($product->id)));

        $this->postStore($this->productInput(array(
            'id' => $product->id,
            'name' => 'Tagged product updated',
            'sku' => 'TAGGED-PRODUCT',
            'tags' => 'gamma,delta'
        )));

        $this->assertRedirectedTo('/admin/products');
        $this->assertSame(array('gamma', 'delta'), $this->tagNames(Product::find($product->id)));

        $this->postStore($this->productInput(array(
            'id' => $product->id,
            'name' => 'Tagged product updated',
            'sku' => 'TAGGED-PRODUCT',
            'tags' => null
        )));

        $this->assertRedirectedTo('/admin/products');
        $this->assertSame(array(), $this->tagNames(Product::find($product->id)));
    }

    /**
     * Build a valid product input
     */
    private function productInput(array $overrides = array())
    {
        return array_merge(array(
            'name' => 'Product name',
            'short_description' => 'Product short',
            'long_description' => 'Product long',
            'sku' => 'PRODUCT-SKU',
            'category_id' => 1,
            'price' => '12.50',
            'weight' => '1.5',
            'length' => '2.5',
            'width' => '3.5',
            'height' => '4.5',
            'weight_unit' => 'kg',
            'volume_unit' => 'm'
        ), $overrides);
    }
}
