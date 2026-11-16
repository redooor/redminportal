<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Product;

/**
 * Covers the nullable numeric rules on product dimensions.
 */
class ProductValidationFixTest extends BaseControllerTest
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
            'create' => $this->productInput(),
            'edit' => $this->productInput(array('id' => 1))
        );

        $this->configureImageUploads();
    }

    public function tearDown(): void
    {
        $this->cleanupImageUploads();

        parent::tearDown();
    }

    /**
     * Test (Pass): empty dimension strings are accepted and stored as null
     */
    public function testEmptyDimensionsAreAcceptedAndSaved()
    {
        $this->postStore($this->productInput(array(
            'name' => 'Empty dimensions product',
            'sku' => 'EMPTY-DIMENSIONS',
            'weight' => '',
            'length' => '',
            'width' => '',
            'height' => ''
        )));

        $this->assertRedirectedTo('/admin/products');
        $this->response->assertSessionHasNoErrors();
        $this->assertSame(1, Product::count());

        $product = Product::first();
        $this->assertNull($product->weight);
        $this->assertNull($product->length);
        $this->assertNull($product->width);
        $this->assertNull($product->height);
    }

    /**
     * Test (Pass): omitted dimension fields are accepted and stored as null
     */
    public function testOmittedDimensionsAreAcceptedAndSaved()
    {
        $this->postStore($this->productInput(
            array('name' => 'Omitted dimensions product', 'sku' => 'OMITTED-DIMENSIONS'),
            array('weight', 'length', 'width', 'height')
        ));

        $this->assertRedirectedTo('/admin/products');
        $this->response->assertSessionHasNoErrors();
        $this->assertSame(1, Product::count());

        $product = Product::first();
        $this->assertNull($product->weight);
        $this->assertNull($product->length);
        $this->assertNull($product->width);
        $this->assertNull($product->height);
    }

    /**
     * Test (Pass): numeric dimension values are still saved exactly
     */
    public function testNumericDimensionsAreSavedExactly()
    {
        $this->postStore($this->productInput(array(
            'name' => 'Numeric dimensions product',
            'sku' => 'NUMERIC-DIMENSIONS',
            'weight' => '1.125',
            'length' => '2.25',
            'width' => '3.5',
            'height' => '4.75'
        )));

        $this->assertRedirectedTo('/admin/products');
        $this->response->assertSessionHasNoErrors();
        $this->assertSame(1, Product::count());

        $product = Product::first();
        $this->assertSame(1.125, (float) $product->weight);
        $this->assertSame(2.25, (float) $product->length);
        $this->assertSame(3.5, (float) $product->width);
        $this->assertSame(4.75, (float) $product->height);
    }

    /**
     * Test (Fail): non-numeric dimensions are rejected with the default message
     */
    public function testNonNumericDimensionsAreRejected()
    {
        $this->assertValidationFailure(
            '/admin/products/create',
            $this->productInput(array(
                'name' => 'Invalid dimensions product',
                'sku' => 'INVALID-DIMENSIONS',
                'weight' => 'abc',
                'length' => 'abc',
                'width' => 'abc',
                'height' => 'abc'
            )),
            array(
                'weight' => 'The weight field must be a number.',
                'length' => 'The length field must be a number.',
                'width' => 'The width field must be a number.',
                'height' => 'The height field must be a number.'
            )
        );

        $this->assertSame(0, Product::count());
    }

    /**
     * Test (Pass): an unchanged valid dimension still passes on edit
     */
    public function testUnchangedDimensionsAreAcceptedOnEdit()
    {
        $product = $this->createProduct('Existing dimensions product', 'EXISTING-DIMENSIONS', array(
            'weight' => 1.125,
            'length' => 2.25,
            'width' => 3.5,
            'height' => 4.75
        ));

        $this->postStore($this->productInput(array(
            'id' => $product->id,
            'name' => 'Existing dimensions product',
            'sku' => 'EXISTING-DIMENSIONS',
            'weight' => '1.125',
            'length' => '2.25',
            'width' => '3.5',
            'height' => '4.75',
            'short_description' => 'Changed short'
        )));

        $this->assertRedirectedTo('/admin/products');
        $this->response->assertSessionHasNoErrors();
        $this->assertSame(1, Product::count());

        $product = Product::find($product->id);
        $this->assertSame('Changed short', $product->short_description);
        $this->assertSame(1.125, (float) $product->weight);
        $this->assertSame(2.25, (float) $product->length);
        $this->assertSame(3.5, (float) $product->width);
        $this->assertSame(4.75, (float) $product->height);
    }

    /**
     * Test (Pass): a variant with empty dimension strings is accepted and saved
     */
    public function testVariantEmptyDimensionsAreAcceptedAndSaved()
    {
        $parent = $this->createProduct('Variant parent', 'VARIANT-PARENT');

        $this->postStore($this->productInput(array(
            'product_id' => $parent->id,
            'name' => 'Empty dimensions variant',
            'sku' => 'EMPTY-DIMENSIONS-VARIANT',
            'weight' => '',
            'length' => '',
            'width' => '',
            'height' => ''
        )));

        $variant = Product::where('sku', 'EMPTY-DIMENSIONS-VARIANT')->first();
        $this->assertNotNull($variant);
        $this->assertRedirectedTo('/admin/products/view-variant/' . $variant->id);
        $this->response->assertSessionHasNoErrors();
        $this->assertSame(2, Product::count());
        $this->assertSame(1, $parent->variants()->count());
        $this->assertNull($variant->weight);
        $this->assertNull($variant->length);
        $this->assertNull($variant->width);
        $this->assertNull($variant->height);
    }

    /**
     * Test (Pass): a variant with omitted dimension fields is accepted and saved
     */
    public function testVariantOmittedDimensionsAreAcceptedAndSaved()
    {
        $parent = $this->createProduct('Omitted variant parent', 'OMITTED-VARIANT-PARENT');

        $this->postStore($this->productInput(
            array(
                'product_id' => $parent->id,
                'name' => 'Omitted dimensions variant',
                'sku' => 'OMITTED-DIMENSIONS-VARIANT'
            ),
            array('weight', 'length', 'width', 'height')
        ));

        $variant = Product::where('sku', 'OMITTED-DIMENSIONS-VARIANT')->first();
        $this->assertNotNull($variant);
        $this->assertRedirectedTo('/admin/products/view-variant/' . $variant->id);
        $this->response->assertSessionHasNoErrors();
        $this->assertSame(2, Product::count());
        $this->assertNull($variant->weight);
        $this->assertNull($variant->length);
        $this->assertNull($variant->width);
        $this->assertNull($variant->height);
    }

    /**
     * Test (Pass): an unchanged valid variant dimension still passes on edit
     */
    public function testVariantUnchangedDimensionsAreAcceptedOnEdit()
    {
        $parent = $this->createProduct('Edit variant parent', 'EDIT-VARIANT-PARENT');
        $variant = $this->createProduct('Edit variant', 'EDIT-DIMENSIONS-VARIANT', array(
            'weight' => 1.125,
            'length' => 2.25,
            'width' => 3.5,
            'height' => 4.75
        ));
        $parent->variants()->attach($variant->id);

        $this->postStore($this->productInput(array(
            'product_id' => $parent->id,
            'id' => $variant->id,
            'name' => 'Edit variant',
            'sku' => 'EDIT-DIMENSIONS-VARIANT',
            'weight' => '1.125',
            'length' => '2.25',
            'width' => '3.5',
            'height' => '4.75',
            'short_description' => 'Changed variant short'
        )));

        $this->assertRedirectedTo('/admin/products/view-variant/' . $variant->id);
        $this->response->assertSessionHasNoErrors();
        $this->assertSame(2, Product::count());

        $variant = Product::find($variant->id);
        $this->assertSame('Changed variant short', $variant->short_description);
        $this->assertSame(1.125, (float) $variant->weight);
        $this->assertSame(2.25, (float) $variant->length);
        $this->assertSame(3.5, (float) $variant->width);
        $this->assertSame(4.75, (float) $variant->height);
    }

    /**
     * Build a valid product input, optionally overridden or with keys removed
     */
    private function productInput(array $overrides = array(), array $remove = array())
    {
        $input = array_merge(array(
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

        foreach ($remove as $key) {
            unset($input[$key]);
        }

        return $input;
    }

    /**
     * Create a saved product fixture
     */
    private function createProduct($name, $sku, array $dimensions = array())
    {
        return $this->createModel(Product::class, array_merge(array(
            'name' => $name,
            'sku' => $sku,
            'short_description' => 'Short',
            'category_id' => 1,
            'active' => true
        ), $dimensions));
    }
}
