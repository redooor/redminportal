<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Bundle;
use Redooor\Redminportal\App\Models\Category;
use Redooor\Redminportal\App\Models\Product;

/**
 * Covers the bundle name uniqueness table and the category exists rule.
 */
class BundleValidationFixTest extends BaseControllerTest
{
    use CharacterizationSupport;

    protected $category;
    protected $product;

    public function setUp(): void
    {
        parent::setUp();

        $this->page = '/admin/bundles';
        $this->viewhas = array(
            'singular' => 'bundle',
            'plural' => 'models'
        );

        $this->category = $this->createModel(Category::class, array(
            'name' => 'Bundle validation category',
            'short_description' => 'Short',
            'order' => 1
        ));

        $this->product = $this->createModel(Product::class, array(
            'name' => 'Validation product',
            'sku' => 'VALIDATION-PRODUCT',
            'short_description' => 'Short',
            'category_id' => $this->category->id,
            'active' => true
        ));

        $this->input = array(
            'create' => $this->bundleInput(),
            'edit' => $this->bundleInput(array('id' => 1))
        );
    }

    /**
     * Test (Pass): a product with the same name does not block a bundle name
     */
    public function testProductNameDoesNotBlockBundleName()
    {
        $this->postStore($this->bundleInput(array(
            'name' => $this->product->name,
            'sku' => 'PRODUCT-NAME-BUNDLE'
        )));

        $this->assertRedirectedTo('/admin/bundles');
        $this->response->assertSessionHasNoErrors();
        $this->assertSame(1, Bundle::count());
        $this->assertSame($this->product->name, Bundle::first()->name);
    }

    /**
     * Test (Fail): a second bundle with the same name is rejected
     */
    public function testDuplicateBundleNameIsRejected()
    {
        $this->postStore($this->bundleInput(array(
            'name' => 'Duplicate bundle',
            'sku' => 'DUPLICATE-ONE'
        )));

        $this->assertRedirectedTo('/admin/bundles');
        $this->assertSame(1, Bundle::count());

        $this->assertValidationFailure(
            '/admin/bundles/create',
            $this->bundleInput(array(
                'name' => 'Duplicate bundle',
                'sku' => 'DUPLICATE-TWO'
            )),
            array('name' => 'The name has already been taken.')
        );

        $this->assertSame(1, Bundle::count());
    }

    /**
     * Test (Pass): an unchanged bundle name still passes on edit
     */
    public function testUnchangedNameIsAcceptedOnEdit()
    {
        $bundle = $this->createModel(Bundle::class, array(
            'name' => 'Unchanged bundle',
            'sku' => 'UNCHANGED-BUNDLE',
            'short_description' => 'Short',
            'category_id' => $this->category->id
        ));

        $this->postStore($this->bundleInput(array(
            'id' => $bundle->id,
            'name' => 'Unchanged bundle',
            'sku' => 'UNCHANGED-BUNDLE',
            'short_description' => 'Changed short'
        )));

        $this->assertRedirectedTo('/admin/bundles');
        $this->response->assertSessionHasNoErrors();
        $this->assertSame(1, Bundle::count());
        $this->assertSame('Unchanged bundle', Bundle::find($bundle->id)->name);
        $this->assertSame('Changed short', Bundle::find($bundle->id)->short_description);
    }

    /**
     * Test (Fail): a non-numeric category_id is rejected
     */
    public function testNonNumericCategoryIdIsRejected()
    {
        $this->assertValidationFailure(
            '/admin/bundles/create',
            $this->bundleInput(array(
                'name' => 'Invalid category bundle',
                'sku' => 'INVALID-CATEGORY',
                'category_id' => 'abc'
            )),
            array('category_id' => 'The category id field must be an integer.')
        );

        $this->assertSame(
            array('The category id field must be an integer.'),
            $this->defaultErrors()->get('category_id')
        );

        $this->assertSame(0, Bundle::count());
    }

    /**
     * Test (Fail): a numeric category_id that does not exist is rejected
     */
    public function testNonexistentCategoryIdIsRejected()
    {
        $this->assertValidationFailure(
            '/admin/bundles/create',
            $this->bundleInput(array(
                'name' => 'Missing category bundle',
                'sku' => 'MISSING-CATEGORY',
                'category_id' => '99999'
            )),
            array('category_id' => 'The selected category id is invalid.')
        );

        $this->assertSame(0, Bundle::count());
    }

    /**
     * Test (Pass): an existing category is accepted and stored
     */
    public function testExistingCategoryIsAccepted()
    {
        $this->postStore($this->bundleInput(array(
            'name' => 'Existing category bundle',
            'sku' => 'EXISTING-CATEGORY'
        )));

        $this->assertRedirectedTo('/admin/bundles');
        $this->response->assertSessionHasNoErrors();
        $this->assertSame(1, Bundle::count());
        $this->assertSame($this->category->id, (int) Bundle::first()->category_id);
    }

    /**
     * Build a valid bundle input, optionally overridden or with keys removed
     */
    private function bundleInput(array $overrides = array(), array $remove = array())
    {
        $input = array_merge(array(
            'name' => 'Bundle name',
            'short_description' => 'Bundle short',
            'long_description' => 'Bundle long',
            'sku' => 'BUNDLE-SKU',
            'category_id' => $this->category->id,
            'price' => '12.50',
            'product_id' => array($this->product->id),
            'pricelist_id' => array()
        ), $overrides);

        foreach ($remove as $key) {
            unset($input[$key]);
        }

        return $input;
    }
}
