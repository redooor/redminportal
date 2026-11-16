<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Image;
use Redooor\Redminportal\App\Models\Product;

/**
 * Covers the failure redirects for removing images from product variants.
 */
class ProductVariantImgremoveTest extends BaseControllerTest
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
            'create' => array(
                'name' => 'This is title',
                'short_description' => 'This is body',
                'category_id' => 1,
                'sku' => 'UNIQUESKU001',
                'cn_name' => 'This is cn name',
                'cn_short_description' => 'This is cn short description',
                'cn_long_description' => 'This is cn long description',
                'weight_unit' => 'kg',
                'volume_unit' => 'm',
                'length' => 9.99,
                'width' => 8.44,
                'height' => 2.33
            ),
            'edit' => array(
                'id' => 1,
                'name' => 'This is title',
                'short_description' => 'This is body',
                'category_id' => 1,
                'sku' => 'UNIQUESKU001',
                'cn_name' => 'This is cn name',
                'cn_short_description' => 'This is cn short description',
                'cn_long_description' => 'This is cn long description',
                'weight_unit' => 'g',
                'volume_unit' => 'mm',
                'length' => 8.99,
                'width' => 7.44,
                'height' => 1.33
            )
        );

        $this->configureImageUploads();
    }

    public function tearDown(): void
    {
        $this->cleanupImageUploads();

        parent::tearDown();
    }

    /**
     * Test (Fail): a missing image with an existing parent redirects to the parent edit page
     */
    public function testMissingImageWithExistingParentRedirectsToParentEdit()
    {
        $parent = $this->createProductFixture('Missing image parent', 'MISSING-IMAGE-PARENT');
        $variant = $this->createProductFixture('Missing image variant', 'MISSING-IMAGE-VARIANT');
        $parent->variants()->attach($variant->id);
        $image = $this->attachImage($variant);

        $this->call('POST', $this->page . '/variant-imgremove/' . $parent->id . '/999999');

        $this->assertResponseStatus(302);
        $this->assertRedirectedTo('/admin/products/edit/' . $parent->id);
        $this->assertErrorMessage('errorDeleteImage', 'The image cannot be deleted at this time.');
        $this->assertSame(1, Image::count());
        $this->assertNotNull(Image::find($image->id));
    }

    /**
     * Test (Fail): a missing parent redirects to the product index
     */
    public function testMissingParentRedirectsToProductIndex()
    {
        $owner = $this->createProductFixture('Orphan image owner', 'ORPHAN-IMAGE-OWNER');
        $variant = $this->createProductFixture('Orphan image variant', 'ORPHAN-IMAGE-VARIANT');
        $owner->variants()->attach($variant->id);
        $image = $this->attachImage($variant);

        $this->call('POST', $this->page . '/variant-imgremove/999999/' . $image->id);

        $this->assertResponseStatus(302);
        $this->assertRedirectedTo('/admin/products');
        $this->assertErrorMessage('errorDeleteImage', 'The image cannot be deleted at this time.');
        $this->assertSame(1, Image::count());
        $this->assertNotNull(Image::find($image->id));
    }

    /**
     * Test (Fail): an image belonging to another product's variant is not deleted
     */
    public function testImageFromAnotherProductVariantIsNotDeleted()
    {
        $parent = $this->createProductFixture('Target parent', 'TARGET-PARENT');
        $otherParent = $this->createProductFixture('Other parent', 'OTHER-PARENT');
        $otherVariant = $this->createProductFixture('Other variant', 'OTHER-VARIANT');
        $otherParent->variants()->attach($otherVariant->id);
        $image = $this->attachImage($otherVariant);

        $this->call('POST', $this->page . '/variant-imgremove/' . $parent->id . '/' . $image->id);

        $this->assertResponseStatus(302);
        $this->assertRedirectedTo('/admin/products/edit/' . $parent->id);
        $this->assertErrorMessage('errorDeleteImage', 'The image cannot be deleted at this time.');
        $this->assertSame(1, Image::count());
        $this->assertNotNull(Image::find($image->id));
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
}
