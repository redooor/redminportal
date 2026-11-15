<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Category;
use Redooor\Redminportal\App\Models\Image;
use Redooor\Redminportal\App\Models\Order;
use Redooor\Redminportal\App\Models\Product;
use Redooor\Redminportal\App\Models\Tag;
use Redooor\Redminportal\App\Models\Translation;

/**
 * Pins the behaviour before the controller refactor; do not edit to make a refactor pass.
 */
class ProductCharacterizationTest extends BaseControllerTest
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
     * Test (Pass): create then update keeps one row and saves the main fields
     */
    public function testCharacterizationCreateThenUpdate()
    {
        $this->postStore($this->productInput(array(
            'name' => 'Original product',
            'sku' => 'ORIGINAL-PRODUCT',
            'short_description' => 'Original short',
            'long_description' => 'Original long',
            'price' => '19.99',
            'featured' => '1',
            'active' => '1',
            'weight' => '1.5',
            'length' => '2.5',
            'width' => '3.5',
            'height' => '4.5'
        )));

        $this->assertRedirectedTo('/admin/products');
        $this->assertSame(1, Product::count());

        $product = Product::first();
        $this->assertSame('Original product', $product->name);
        $this->assertSame('ORIGINAL-PRODUCT', $product->sku);
        $this->assertSame('Original short', $product->short_description);
        $this->assertSame('Original long', $product->long_description);
        $this->assertSame(19.99, (float) $product->price);
        $this->assertSame(1, (int) $product->featured);
        $this->assertSame(1, (int) $product->active);
        $this->assertSame(1, (int) $product->category_id);
        $this->assertSame(1.5, (float) $product->weight);
        $this->assertSame(2.5, (float) $product->length);
        $this->assertSame(3.5, (float) $product->width);
        $this->assertSame(4.5, (float) $product->height);

        $update = $this->productInput(array(
            'id' => $product->id,
            'name' => 'Updated product',
            'sku' => 'UPDATED-PRODUCT',
            'short_description' => 'Updated short',
            'long_description' => 'Updated long',
            'price' => '29.99',
            'featured' => '',
            'active' => ''
        ));
        unset($update['weight'], $update['length'], $update['width'], $update['height']);

        $this->postStore($update);

        $this->assertRedirectedTo('/admin/products');
        $this->assertSame(1, Product::count());

        $product = Product::first();
        $this->assertSame('Updated product', $product->name);
        $this->assertSame('UPDATED-PRODUCT', $product->sku);
        $this->assertSame('Updated short', $product->short_description);
        $this->assertSame('Updated long', $product->long_description);
        $this->assertSame(29.99, (float) $product->price);
        $this->assertSame(0, (int) $product->featured);
        $this->assertSame(0, (int) $product->active);
        $this->assertNull($product->weight);
        $this->assertNull($product->length);
        $this->assertNull($product->width);
        $this->assertNull($product->height);
    }

    /**
     * Test (Fail): invalid input with no id redirects to create
     */
    public function testCharacterizationValidationRedirectWhenIdOmitted()
    {
        $this->assertValidationFailure(
            '/admin/products/create',
            $this->productInput(array('name' => '')),
            array('name' => 'The name field is required.')
        );
    }

    /**
     * Test (Fail): invalid input with a nonexistent id redirects to edit/5 before the not-found check
     */
    public function testCharacterizationValidationRedirectWhenIdIsFive()
    {
        $this->assertValidationFailure(
            '/admin/products/edit/5',
            $this->productInput(array('id' => '5', 'name' => '')),
            array('name' => 'The name field is required.')
        );
    }

    /**
     * Test (Fail): an empty id is converted to null by the HTTP middleware, so it redirects to create
     */
    public function testCharacterizationValidationRedirectWhenIdIsEmptyString()
    {
        $this->assertValidationFailure(
            '/admin/products/create',
            $this->productInput(array('id' => '', 'name' => '')),
            array('name' => 'The name field is required.')
        );
    }

    /**
     * Test (Fail): an id of zero is falsy for Product redirects, so it redirects to create
     */
    public function testCharacterizationValidationRedirectWhenIdIsZero()
    {
        $this->assertValidationFailure(
            '/admin/products/create',
            $this->productInput(array('id' => '0', 'name' => '')),
            array('name' => 'The name field is required.')
        );
    }

    /**
     * Test (Fail): invalid input with an existing id redirects to edit/<id>
     */
    public function testCharacterizationValidationRedirectWhenIdExists()
    {
        $product = $this->createProduct('Existing product', 'EXISTING-PRODUCT');

        $this->assertValidationFailure(
            '/admin/products/edit/' . $product->id,
            $this->productInput(array('id' => $product->id, 'name' => '')),
            array('name' => 'The name field is required.')
        );
    }

    /**
     * Test (Fail): valid input with a nonexistent id gives the errorNoSuchProduct message
     */
    public function testCharacterizationNonexistentIdWithValidFields()
    {
        $this->postStore($this->productInput(array(
            'id' => '5',
            'name' => 'Valid product',
            'sku' => 'VALID-PRODUCT'
        )));

        $this->assertRedirectedTo('/admin/products');
        $this->assertErrorMessage(
            'errorNoSuchProduct',
            'The product cannot be found because it does not exist or may have been deleted.'
        );
        $this->assertSame(0, Product::count());
    }

    /**
     * Test (Fail): name is required
     */
    public function testCharacterizationMissingName()
    {
        $this->assertValidationFailure(
            '/admin/products/create',
            $this->productInput(array('name' => '')),
            array('name' => 'The name field is required.')
        );
    }

    /**
     * Test (Fail): short_description is required
     */
    public function testCharacterizationMissingShortDescription()
    {
        $this->assertValidationFailure(
            '/admin/products/create',
            $this->productInput(array('short_description' => '')),
            array('short_description' => 'The short description field is required.')
        );
    }

    /**
     * Test (Fail): sku is required
     */
    public function testCharacterizationMissingSku()
    {
        $this->assertValidationFailure(
            '/admin/products/create',
            $this->productInput(array('sku' => '')),
            array('sku' => 'The sku field is required.')
        );
    }

    /**
     * Test (Fail): category_id is required
     */
    public function testCharacterizationMissingCategoryId()
    {
        $this->assertValidationFailure(
            '/admin/products/create',
            $this->productInput(array('category_id' => '')),
            array('category_id' => 'The category id field is required.')
        );
    }

    /**
     * Test (Fail): category_id min:1 uses the custom "The category field is required." message
     */
    public function testCharacterizationCategoryIdZero()
    {
        $this->assertValidationFailure(
            '/admin/products/create',
            $this->productInput(array('category_id' => '0')),
            array('category_id' => 'The category field is required.')
        );
    }

    /**
     * Test (Fail): category_id must be numeric
     */
    public function testCharacterizationCategoryIdNonNumeric()
    {
        $this->assertValidationFailure(
            '/admin/products/create',
            $this->productInput(array('category_id' => 'abc')),
            array('category_id' => 'The category id field must be a number.')
        );
    }

    /**
     * Test (Fail): sku has an alpha_dash rule
     */
    public function testCharacterizationInvalidSkuFormat()
    {
        $this->assertValidationFailure(
            '/admin/products/create',
            $this->productInput(array('sku' => 'bad sku!')),
            array('sku' => 'The sku field must only contain letters, numbers, dashes, and underscores.')
        );
    }

    /**
     * Test (Fail): price has a numeric rule
     */
    public function testCharacterizationInvalidPrice()
    {
        $this->assertValidationFailure(
            '/admin/products/create',
            $this->productInput(array('price' => 'abc')),
            array('price' => 'The price field must be a number.')
        );
    }

    /**
     * Test (Fail): weight, length, width and height each have a numeric rule
     */
    public function testCharacterizationInvalidDimensions()
    {
        $this->assertValidationFailure(
            '/admin/products/create',
            $this->productInput(array(
                'weight' => 'heavy',
                'length' => 'long',
                'width' => 'wide',
                'height' => 'tall'
            )),
            array(
                'weight' => 'The weight field must be a number.',
                'length' => 'The length field must be a number.',
                'width' => 'The width field must be a number.',
                'height' => 'The height field must be a number.'
            )
        );
    }

    /**
     * Test (Fail): tags has a regex rule
     */
    public function testCharacterizationInvalidTagsFormat()
    {
        $this->assertValidationFailure(
            '/admin/products/create',
            $this->productInput(array('tags' => 'a!b')),
            array('tags' => 'The tags field format is invalid.')
        );
    }

    /**
     * Test (Fail): old input keeps scalar values but never an uploaded file
     */
    public function testCharacterizationOldInputWithFile()
    {
        $this->assertValidationFailure(
            '/admin/products/create',
            $this->productInput(array('name' => '', 'short_description' => 'Old short', 'sku' => 'OLD-PRODUCT-SKU')),
            array('name' => 'The name field is required.'),
            array('images' => array($this->uploadedImage('old-input.png')))
        );

        $this->assertOldInput(array('short_description' => 'Old short', 'sku' => 'OLD-PRODUCT-SKU', 'category_id' => 1));
        $this->assertSame(0, Image::count());
    }

    /**
     * Test (Fail): a JSON Accept header still redirects with session errors in the default bag
     */
    public function testCharacterizationJsonRequestStillRedirectsWithDefaultBag()
    {
        $this->postStoreAsJson($this->productInput(array(
            'name' => '',
            '_error_bag' => 'custom'
        )));

        $this->assertResponseStatus(302);
        $this->assertRedirectedTo('/admin/products/create');
        $this->assertSessionHasErrors('name');
        $this->assertErrorMessage('name', 'The name field is required.');
        $this->assertErrorBagIsDefaultOnly();
    }

    /**
     * Test (Pass): an unchanged name on update passes and another row's name fails
     */
    public function testCharacterizationUniqueNameOnUpdate()
    {
        $first = $this->createProduct('First product', 'FIRST-PRODUCT-SKU');
        $this->createProduct('Second product', 'SECOND-PRODUCT-SKU');

        $this->postStore($this->productInput(array(
            'id' => $first->id,
            'name' => 'First product',
            'sku' => 'FIRST-PRODUCT-SKU',
            'short_description' => 'Changed short'
        )));

        $this->assertRedirectedTo('/admin/products');
        $this->assertSame('Changed short', Product::find($first->id)->short_description);

        $this->postStore($this->productInput(array(
            'id' => $first->id,
            'name' => 'Second product',
            'sku' => 'FIRST-PRODUCT-SKU',
            'short_description' => 'Changed again'
        )));

        $this->assertRedirectedTo('/admin/products/edit/' . $first->id);
        $this->assertErrorMessage('name', 'The name has already been taken.');
        $this->assertSame('Changed short', Product::find($first->id)->short_description);
    }

    /**
     * Test (Pass): an unchanged sku on update passes and another row's sku fails
     */
    public function testCharacterizationUniqueSkuOnUpdate()
    {
        $first = $this->createProduct('First product', 'FIRST-PRODUCT-SKU');
        $this->createProduct('Second product', 'SECOND-PRODUCT-SKU');

        $this->postStore($this->productInput(array(
            'id' => $first->id,
            'name' => 'First product',
            'sku' => 'FIRST-PRODUCT-SKU',
            'short_description' => 'Changed short'
        )));

        $this->assertRedirectedTo('/admin/products');
        $this->assertSame('Changed short', Product::find($first->id)->short_description);

        $this->postStore($this->productInput(array(
            'id' => $first->id,
            'name' => 'First product',
            'sku' => 'SECOND-PRODUCT-SKU',
            'short_description' => 'Changed again'
        )));

        $this->assertRedirectedTo('/admin/products/edit/' . $first->id);
        $this->assertErrorMessage('sku', 'The sku has already been taken.');
        $this->assertSame('Changed short', Product::find($first->id)->short_description);
    }

    /**
     * Test (Pass): create writes one cn translation row with the exact decoded content
     */
    public function testCharacterizationTranslationCreated()
    {
        $this->postStore($this->productInput(array(
            'name' => 'Translated product',
            'sku' => 'TRANSLATED-PRODUCT',
            'cn_name' => 'CN name',
            'cn_short_description' => 'CN short',
            'cn_long_description' => 'CN long'
        )));

        $this->assertRedirectedTo('/admin/products');

        $this->assertTranslations(Product::first(), array(
            'cn' => array(
                'name' => 'CN name',
                'short_description' => 'CN short',
                'long_description' => 'CN long'
            )
        ));
    }

    /**
     * Test (Pass): update reuses the translation row, even when it already existed
     */
    public function testCharacterizationTranslationUpdatedInPlace()
    {
        $this->postStore($this->productInput(array(
            'name' => 'Translation owner',
            'sku' => 'TRANSLATION-OWNER',
            'cn_name' => 'CN name',
            'cn_short_description' => 'CN short',
            'cn_long_description' => 'CN long'
        )));

        $product = Product::first();
        $translation = $product->translations()->first();
        $translation->content = json_encode(array(
            'name' => 'pre-existing',
            'short_description' => 'pre-existing short',
            'long_description' => 'pre-existing long'
        ));
        $translation->save();

        $this->postStore($this->productInput(array(
            'id' => $product->id,
            'name' => 'Translation owner',
            'sku' => 'TRANSLATION-OWNER',
            'cn_name' => 'Updated CN name',
            'cn_short_description' => 'Updated CN short',
            'cn_long_description' => 'Updated CN long'
        )));

        $this->assertRedirectedTo('/admin/products');
        $this->assertSame(1, Product::count());

        $translations = $this->assertTranslations(Product::find($product->id), array(
            'cn' => array(
                'name' => 'Updated CN name',
                'short_description' => 'Updated CN short',
                'long_description' => 'Updated CN long'
            )
        ));

        $this->assertSame($translation->id, $translations->first()->id);
    }

    /**
     * Test (Pass): missing translated inputs store null values
     */
    public function testCharacterizationMissingTranslatedInputs()
    {
        $this->postStore($this->productInput(array(
            'name' => 'No translations',
            'sku' => 'NO-TRANSLATIONS'
        )));

        $this->assertRedirectedTo('/admin/products');

        $this->assertTranslations(Product::first(), array(
            'cn' => array(
                'name' => null,
                'short_description' => null,
                'long_description' => null
            )
        ));
    }

    /**
     * Test (Pass): a comma separated tags input attaches the new tags
     */
    public function testCharacterizationTagsAttached()
    {
        $this->postStore($this->productInput(array(
            'name' => 'Tagged product',
            'sku' => 'TAGGED-PRODUCT',
            'tags' => 'alpha,beta'
        )));

        $this->assertRedirectedTo('/admin/products');
        $this->assertSame(array('alpha', 'beta'), $this->tagNames(Product::first()));
    }

    /**
     * Test (Pass): a second update with new tags replaces the old ones
     */
    public function testCharacterizationSecondUpdateWithNewTagsReplacesOldTags()
    {
        $this->postStore($this->productInput(array(
            'name' => 'Tagged product',
            'sku' => 'TAGGED-PRODUCT',
            'tags' => 'alpha,beta'
        )));

        $product = Product::first();

        $this->postStore($this->productInput(array(
            'id' => $product->id,
            'name' => 'Tagged product updated',
            'sku' => 'TAGGED-PRODUCT',
            'tags' => 'gamma'
        )));

        $this->assertRedirectedTo('/admin/products');
        $this->assertSame(array('gamma'), $this->tagNames(Product::find($product->id)));
    }

    /**
     * Test (Fail): an empty tags string becomes null and fails the regex rule, saving nothing
     */
    public function testCharacterizationEmptyTagsStringFailsValidation()
    {
        $this->postStore($this->productInput(array(
            'name' => 'Tagged product',
            'sku' => 'TAGGED-PRODUCT',
            'tags' => 'alpha'
        )));

        $product = Product::first();

        $this->assertValidationFailure(
            '/admin/products/edit/' . $product->id,
            $this->productInput(array(
                'id' => $product->id,
                'name' => 'Tagged product updated',
                'sku' => 'TAGGED-PRODUCT',
                'tags' => ''
            )),
            array('tags' => 'The tags field format is invalid.')
        );

        $this->assertSame('Tagged product', Product::find($product->id)->name);
        $this->assertSame(array('alpha'), $this->tagNames(Product::find($product->id)));
    }

    /**
     * Test (Pass): a tags value of "0" keeps the old tags and still saves the product
     */
    public function testCharacterizationZeroTagsStringKeepsOldTags()
    {
        $this->postStore($this->productInput(array(
            'name' => 'Tagged product',
            'sku' => 'TAGGED-PRODUCT',
            'tags' => 'alpha'
        )));

        $product = Product::first();

        $this->postStore($this->productInput(array(
            'id' => $product->id,
            'name' => 'Tagged product updated',
            'sku' => 'TAGGED-PRODUCT',
            'tags' => '0'
        )));

        $this->assertRedirectedTo('/admin/products');
        $this->assertSame('Tagged product updated', Product::find($product->id)->name);
        $this->assertSame(array('alpha'), $this->tagNames(Product::find($product->id)));
    }

    /**
     * Test (Fail): a tags array fails the regex rule, saving nothing and keeping the old tags
     */
    public function testCharacterizationArrayTagsFailsValidation()
    {
        $this->postStore($this->productInput(array(
            'name' => 'Tagged product',
            'sku' => 'TAGGED-PRODUCT',
            'tags' => 'alpha'
        )));

        $product = Product::first();

        $this->assertValidationFailure(
            '/admin/products/edit/' . $product->id,
            $this->productInput(array(
                'id' => $product->id,
                'name' => 'Tagged product updated',
                'sku' => 'TAGGED-PRODUCT',
                'tags' => array()
            )),
            array('tags' => 'The tags field format is invalid.')
        );

        $this->assertSame('Tagged product', Product::find($product->id)->name);
        $this->assertSame(array('alpha'), $this->tagNames(Product::find($product->id)));
    }

    /**
     * Test (Pass): no tags input keeps the old tags
     */
    public function testCharacterizationNoTagsInputKeepsOldTags()
    {
        $this->postStore($this->productInput(array(
            'name' => 'Tagged product',
            'sku' => 'TAGGED-PRODUCT',
            'tags' => 'alpha'
        )));

        $product = Product::first();

        $this->postStore($this->productInput(array(
            'id' => $product->id,
            'name' => 'Tagged product updated',
            'sku' => 'TAGGED-PRODUCT'
        )));

        $this->assertRedirectedTo('/admin/products');
        $this->assertSame('Tagged product updated', Product::find($product->id)->name);
        $this->assertSame(array('alpha'), $this->tagNames(Product::find($product->id)));
    }

    /**
     * Test (Pass): getEdit passes product, translated, categories, tagString, imagine and units
     */
    public function testCharacterizationGetEditViewData()
    {
        $product = $this->createProduct('View product', 'VIEW-PRODUCT-SKU');
        $translation = new Translation;
        $translation->lang = 'cn';
        $translation->content = json_encode(array(
            'name' => 'View CN name',
            'short_description' => 'View CN short',
            'long_description' => 'View CN long'
        ));
        $product->translations()->save($translation);
        Tag::addTag($product, 'alpha');
        Tag::addTag($product, 'beta');

        $this->call('GET', '/admin/products/edit/' . $product->id);

        $this->assertResponseOk();
        $this->assertViewHas('product', function ($value) use ($product) {
            return $value instanceof Product && $value->id == $product->id;
        });
        $this->assertViewHas('translated', function ($value) {
            return isset($value['cn']) && $value['cn']->name === 'View CN name';
        });
        $this->assertViewHas('categories');
        $this->assertViewHas('tagString', 'alpha,beta');
        $this->assertViewHas('imagine');
        $this->assertViewHas('weight_units');
        $this->assertViewHas('volume_units');
    }

    /**
     * Test (Fail): getEdit for a nonexistent id uses the exact errorNoSuchProduct message
     */
    public function testCharacterizationGetEditMissing()
    {
        $this->call('GET', '/admin/products/edit/999');

        $this->assertRedirectedTo('/admin/products');
        $this->assertErrorMessage(
            'errorNoSuchProduct',
            'The product cannot be found because it does not exist or may have been deleted.'
        );
    }

    /**
     * Test (Pass): two files in images[] attach two Image rows
     */
    public function testCharacterizationImagesArrayAttachesTwoRows()
    {
        $this->postStore(
            $this->productInput(array('name' => 'Image product', 'sku' => 'IMAGE-PRODUCT-SKU')),
            array('images' => array(
                $this->uploadedImage('first.png'),
                $this->uploadedImage('second.png')
            ))
        );

        $this->assertRedirectedTo('/admin/products');
        $this->assertSame(1, Product::count());
        $this->assertSame(2, Image::count());

        $product = Product::first();
        $this->assertSame(2, $product->images()->count());
        $this->assertSame(Product::class, Image::first()->imageable_type);
        $this->assertSame($product->id, Image::first()->imageable_id);
    }

    /**
     * Test (Pass): the legacy single image field attaches one Image row
     */
    public function testCharacterizationLegacySingleImageAttachesOneRow()
    {
        $this->postStore(
            $this->productInput(array('name' => 'Legacy product', 'sku' => 'LEGACY-PRODUCT-SKU')),
            array('image' => $this->uploadedImage('legacy.png'))
        );

        $this->assertRedirectedTo('/admin/products');
        $this->assertSame(1, Product::count());
        $this->assertSame(1, Image::count());
        $this->assertSame(Product::class, Image::first()->imageable_type);
    }

    /**
     * Test (Fail): a validation failure creates no Image rows
     */
    public function testCharacterizationValidationFailureCreatesNoImages()
    {
        $this->assertValidationFailure(
            '/admin/products/create',
            $this->productInput(array('name' => '')),
            array('name' => 'The name field is required.'),
            array('images' => array($this->uploadedImage('not-saved.png')))
        );

        $this->assertSame(0, Product::count());
        $this->assertSame(0, Image::count());
    }

    /**
     * Test (Pass): getImgremove deletes an image owned by the product and redirects to edit
     */
    public function testCharacterizationImgremoveSuccess()
    {
        $product = $this->createProduct('Image remove product', 'IMAGE-REMOVE-PRODUCT');
        $image = $this->attachImage($product);

        $this->call('POST', '/admin/products/imgremove/' . $image->id);

        $this->assertRedirectedTo('/admin/products/edit/' . $product->id);
        $this->assertNull(Image::find($image->id));
    }

    /**
     * Test (Fail): an image owned by another model cannot be removed through products
     */
    public function testCharacterizationImgremoveWrongOwner()
    {
        $category = $this->createModel(Category::class, array(
            'name' => 'Product image owner ' . uniqid(),
            'short_description' => 'Short description',
            'order' => 1
        ));
        $image = $this->attachImage($category);

        $this->call('POST', '/admin/products/imgremove/' . $image->id);

        $this->assertRedirectedTo('/admin/products');
        $this->assertErrorMessage('errorDeleteImage', 'The image cannot be deleted at this time.');
        $this->assertNotNull(Image::find($image->id));
    }

    /**
     * Test (Fail): a nonexistent image id gives the errorDeleteImage message
     */
    public function testCharacterizationImgremoveMissing()
    {
        $this->call('POST', '/admin/products/imgremove/999999');

        $this->assertRedirectedTo('/admin/products');
        $this->assertErrorMessage('errorDeleteImage', 'The image cannot be deleted at this time.');
    }

    /**
     * Test (Pass): getDelete removes the product, its variants and its tags
     */
    public function testCharacterizationGetDelete()
    {
        $product = $this->createProduct('Deletable product', 'DELETABLE-PRODUCT');
        $variant = $this->createProduct('Deletable variant', 'DELETABLE-VARIANT');
        $product->variants()->attach($variant->id);
        Tag::addTag($product, 'alpha');

        $this->call('POST', '/admin/products/delete/' . $product->id, array(), array(), array(), array(
            'HTTP_REFERER' => '/admin/products'
        ));

        $this->assertRedirectedTo('/admin/products');
        $this->assertNull(Product::find($product->id));
        $this->assertNull(Product::find($variant->id));
        $this->assertSame(0, Product::count());
    }

    /**
     * Test (Fail): getDelete for a missing product uses the deleteError message
     */
    public function testCharacterizationGetDeleteMissing()
    {
        $this->call('POST', '/admin/products/delete/999', array(), array(), array(), array(
            'HTTP_REFERER' => '/admin/products'
        ));

        $this->assertRedirectedTo('/admin/products');
        $this->assertErrorMessage(
            'errorDeleteRecord',
            'We are having problem deleting this record. Please try again.'
        );
        $this->assertSame(0, Product::count());
    }

    /**
     * Test (Pass): getCreateVariant passes product, product_id and the unit lists
     */
    public function testCharacterizationGetCreateVariantViewData()
    {
        $category = $this->createCategory('Create variant category');
        $parent = $this->createProduct('Variant parent', 'VARIANT-PARENT', $category->id);

        $this->call('GET', '/admin/products/create-variant/' . $parent->id);

        $this->assertResponseOk();
        $this->assertViewHas('product', function ($value) use ($parent) {
            return $value instanceof Product && $value->id == $parent->id;
        });
        $this->assertViewHas('product_id', $parent->id);
        $this->assertViewHas('weight_units');
        $this->assertViewHas('volume_units');
    }

    /**
     * Test (Fail): getCreateVariant for a missing product uses errorNoSuchProduct
     */
    public function testCharacterizationGetCreateVariantMissing()
    {
        $this->call('GET', '/admin/products/create-variant/999');

        $this->assertRedirectedTo('/admin/products');
        $this->assertErrorMessage(
            'errorNoSuchProduct',
            'The product cannot be found because it does not exist or may have been deleted.'
        );
    }

    /**
     * Test (Pass): getListVariants passes variantParent, variants and imagine
     */
    public function testCharacterizationGetListVariantsViewData()
    {
        $parent = $this->createProduct('List parent', 'LIST-PARENT');
        $variant = $this->createProduct('List variant', 'LIST-VARIANT');
        $parent->variants()->attach($variant->id);

        $this->call('GET', '/admin/products/list-variants/' . $parent->id);

        $this->assertResponseOk();
        $this->assertViewHas('variantParent', function ($value) use ($parent) {
            return $value instanceof Product && $value->id == $parent->id;
        });
        $this->assertViewHas('variants', function ($value) use ($variant) {
            return $value->count() === 1 && (int) $value->first()->id === (int) $variant->id;
        });
        $this->assertViewHas('imagine');
    }

    /**
     * Test (Fail): getListVariants for a missing product uses errorNoSuchProduct
     */
    public function testCharacterizationGetListVariantsMissing()
    {
        $this->call('GET', '/admin/products/list-variants/999');

        $this->assertRedirectedTo('/admin/products');
        $this->assertErrorMessage(
            'errorNoSuchProduct',
            'The product cannot be found because it does not exist or may have been deleted.'
        );
    }

    /**
     * Test (Pass): getViewVariant passes product, translated and imagine
     */
    public function testCharacterizationGetViewVariantViewData()
    {
        $category = $this->createCategory('View variant category');
        $variant = $this->createProduct('View variant', 'VIEW-VARIANT', $category->id);
        $translation = new Translation;
        $translation->lang = 'cn';
        $translation->content = json_encode(array(
            'name' => 'Variant CN name',
            'short_description' => 'Variant CN short',
            'long_description' => 'Variant CN long'
        ));
        $variant->translations()->save($translation);

        $this->call('GET', '/admin/products/view-variant/' . $variant->id);

        $this->assertResponseOk();
        $this->assertViewHas('product', function ($value) use ($variant) {
            return $value instanceof Product && $value->id == $variant->id;
        });
        $this->assertViewHas('translated', function ($value) {
            return isset($value['cn']) && $value['cn']->name === 'Variant CN name';
        });
        $this->assertViewHas('imagine');
    }

    /**
     * Test (Fail): getViewVariant for a missing product uses errorNoSuchProduct
     */
    public function testCharacterizationGetViewVariantMissing()
    {
        $this->call('GET', '/admin/products/view-variant/999');

        $this->assertRedirectedTo('/admin/products');
        $this->assertErrorMessage(
            'errorNoSuchProduct',
            'The product cannot be found because it does not exist or may have been deleted.'
        );
    }

    /**
     * Test (Pass): getEditVariant passes product_id, parent, product, translated, tagString and imagine
     */
    public function testCharacterizationGetEditVariantViewData()
    {
        $category = $this->createCategory('Edit variant category');
        $parent = $this->createProduct('Edit variant parent', 'EDIT-VARIANT-PARENT', $category->id);
        $variant = $this->createProduct('Edit variant', 'EDIT-VARIANT', $category->id);
        $parent->variants()->attach($variant->id);
        Tag::addTag($variant, 'alpha');

        $this->call('GET', '/admin/products/edit-variant/' . $parent->id . '/' . $variant->id);

        $this->assertResponseOk();
        $this->assertViewHas('product_id', $parent->id);
        $this->assertViewHas('parent', function ($value) use ($parent) {
            return $value instanceof Product && $value->id == $parent->id;
        });
        $this->assertViewHas('product', function ($value) use ($variant) {
            return $value instanceof Product && $value->id == $variant->id;
        });
        $this->assertViewHas('translated');
        $this->assertViewHas('tagString', 'alpha');
        $this->assertViewHas('imagine');
        $this->assertViewHas('weight_units');
        $this->assertViewHas('volume_units');
    }

    /**
     * Test (Fail): getEditVariant with a missing variant uses errorNoSuchProduct
     */
    public function testCharacterizationGetEditVariantMissingVariant()
    {
        $parent = $this->createProduct('Missing variant parent', 'MISSING-VARIANT-PARENT');

        $this->call('GET', '/admin/products/edit-variant/' . $parent->id . '/999');

        $this->assertRedirectedTo('/admin/products');
        $this->assertErrorMessage(
            'errorNoSuchProduct',
            'The product cannot be found because it does not exist or may have been deleted.'
        );
    }

    /**
     * Test (Fail): getEditVariant with a missing parent uses errorNoSuchProduct
     */
    public function testCharacterizationGetEditVariantMissingParent()
    {
        $variant = $this->createProduct('Orphan variant', 'ORPHAN-VARIANT');

        $this->call('GET', '/admin/products/edit-variant/999/' . $variant->id);

        $this->assertRedirectedTo('/admin/products');
        $this->assertErrorMessage(
            'errorNoSuchProduct',
            'The product cannot be found because it does not exist or may have been deleted.'
        );
    }

    /**
     * Test (Fail): the validation redirect for a plain create is /admin/products/create
     */
    public function testCharacterizationVariantValidationRedirectCreate()
    {
        $this->assertValidationFailure(
            '/admin/products/create',
            $this->productInput(array('name' => '')),
            array('name' => 'The name field is required.')
        );
    }

    /**
     * Test (Fail): the validation redirect for a plain edit is /admin/products/edit/<id>
     */
    public function testCharacterizationVariantValidationRedirectEdit()
    {
        $this->assertValidationFailure(
            '/admin/products/edit/5',
            $this->productInput(array('id' => '5', 'name' => '')),
            array('name' => 'The name field is required.')
        );
    }

    /**
     * Test (Fail): the validation redirect for a variant create is /admin/products/create-variant/<product_id>
     */
    public function testCharacterizationVariantValidationRedirectCreateVariant()
    {
        $this->assertValidationFailure(
            '/admin/products/create-variant/5',
            $this->productInput(array('product_id' => '5', 'name' => '')),
            array('name' => 'The name field is required.')
        );
    }

    /**
     * Test (Fail): the validation redirect for a variant edit is /admin/products/edit-variant/<product_id>/<id>
     */
    public function testCharacterizationVariantValidationRedirectEditVariant()
    {
        $this->assertValidationFailure(
            '/admin/products/edit-variant/5/7',
            $this->productInput(array('product_id' => '5', 'id' => '7', 'name' => '')),
            array('name' => 'The name field is required.')
        );
    }

    /**
     * Test (Fail): product_id "0" is falsy, so a validation failure redirects to create
     */
    public function testCharacterizationVariantValidationRedirectWhenProductIdIsZero()
    {
        $this->assertValidationFailure(
            '/admin/products/create',
            $this->productInput(array('product_id' => '0', 'name' => '')),
            array('name' => 'The name field is required.')
        );
    }

    /**
     * Test (Fail): product_id "0" with an id is falsy, so it redirects to edit/<id>
     */
    public function testCharacterizationVariantValidationRedirectWhenProductIdIsZeroAndIdIsSet()
    {
        $this->assertValidationFailure(
            '/admin/products/edit/5',
            $this->productInput(array('product_id' => '0', 'id' => '5', 'name' => '')),
            array('name' => 'The name field is required.')
        );
    }

    /**
     * Test (Fail): id "0" is falsy for the redirect, so a variant create redirects to create-variant
     */
    public function testCharacterizationVariantValidationRedirectWhenIdIsZero()
    {
        $this->assertValidationFailure(
            '/admin/products/create-variant/5',
            $this->productInput(array('product_id' => '5', 'id' => '0', 'name' => '')),
            array('name' => 'The name field is required.')
        );
    }

    /**
     * Test (Fail): id "0" is still isset for model selection, so valid fields give errorNoSuchProduct
     */
    public function testCharacterizationZeroIdWithValidFieldsUsesFindZero()
    {
        $this->postStore($this->productInput(array(
            'id' => '0',
            'name' => 'Zero id product',
            'sku' => 'ZERO-ID-PRODUCT'
        )));

        $this->assertRedirectedTo('/admin/products');
        $this->assertErrorMessage(
            'errorNoSuchProduct',
            'The product cannot be found because it does not exist or may have been deleted.'
        );
        $this->assertSame(0, Product::count());
    }

    /**
     * Test (Fail): id "0" is still passed to the unique ignore, so another row's name still fails
     */
    public function testCharacterizationZeroIdUsesUniqueIgnore()
    {
        $this->createProduct('Existing zero id name', 'EXISTING-ZERO-ID');

        $this->assertValidationFailure(
            '/admin/products/create',
            $this->productInput(array(
                'id' => '0',
                'name' => 'Existing zero id name',
                'sku' => 'ZERO-ID-UNIQUE'
            )),
            array('name' => 'The name has already been taken.')
        );

        $this->assertSame(1, Product::count());
    }

    /**
     * Test (Pass): product_id "0" is falsy, so valid fields create a standalone product
     */
    public function testCharacterizationZeroProductIdWithValidFieldsCreatesStandaloneProduct()
    {
        $this->postStore($this->productInput(array(
            'product_id' => '0',
            'name' => 'Zero product id product',
            'sku' => 'ZERO-PRODUCT-ID'
        )));

        $this->assertRedirectedTo('/admin/products');
        $this->assertSame(1, Product::count());

        $product = Product::first();
        $this->assertSame('Zero product id product', $product->name);
        $this->assertSame(0, $product->variantParents()->count());
    }

    /**
     * Test (Pass): a variant create links the new product to its parent and redirects to the variant
     */
    public function testCharacterizationVariantCreatedAndLinkedToParent()
    {
        $parent = $this->createProduct('Link parent', 'LINK-PARENT');

        $this->postStore($this->productInput(array(
            'product_id' => $parent->id,
            'name' => 'Linked variant',
            'sku' => 'LINKED-VARIANT'
        )));

        $variant = Product::where('sku', 'LINKED-VARIANT')->first();
        $this->assertNotNull($variant);
        $this->assertRedirectedTo('/admin/products/view-variant/' . $variant->id);
        $this->assertSame(2, Product::count());
        $this->assertSame(1, $parent->variants()->count());
        $this->assertSame($variant->id, (int) $parent->variants()->first()->id);
        $this->assertSame(1, $variant->variantParents()->count());
        $this->assertSame(1, (int) $variant->category_id);
    }

    /**
     * Test (Pass): a variant update changes the same row, keeps one parent link and redirects to the variant
     */
    public function testCharacterizationVariantUpdatedInPlace()
    {
        $parent = $this->createProduct('Update variant parent', 'UPDATE-VARIANT-PARENT');
        $variant = $this->createProduct('Update variant', 'UPDATE-VARIANT');
        $parent->variants()->attach($variant->id);

        $this->postStore($this->productInput(array(
            'product_id' => $parent->id,
            'id' => $variant->id,
            'name' => 'Updated variant name',
            'sku' => 'UPDATED-VARIANT',
            'short_description' => 'Updated variant short'
        )));

        $this->assertRedirectedTo('/admin/products/view-variant/' . $variant->id);
        $this->assertSame(2, Product::count());
        $this->assertSame('Updated variant name', Product::find($variant->id)->name);
        $this->assertSame('UPDATED-VARIANT', Product::find($variant->id)->sku);
        $this->assertSame('Updated variant short', Product::find($variant->id)->short_description);
        $this->assertSame(1, $parent->variants()->count());
    }

    /**
     * Test (Fail): valid fields with a missing parent product give errorDeleteRecord
     */
    public function testCharacterizationVariantMissingParentProduct()
    {
        $this->postStore($this->productInput(array(
            'product_id' => '999',
            'name' => 'Orphan variant',
            'sku' => 'ORPHAN-VARIANT-SKU'
        )));

        $this->assertRedirectedTo('/admin/products');
        $this->assertErrorMessage(
            'errorDeleteRecord',
            'The product cannot be found because it does not exist or may have been deleted.'
        );
        $this->assertSame(0, Product::count());
    }

    /**
     * Test (Pass): saving a parent product propagates its category to existing variants
     */
    public function testCharacterizationCategoryPropagatesFromParentToVariants()
    {
        $firstCategory = $this->createModel(Category::class, array(
            'name' => 'First product category',
            'short_description' => 'Short',
            'order' => 1
        ));
        $secondCategory = $this->createModel(Category::class, array(
            'name' => 'Second product category',
            'short_description' => 'Short',
            'order' => 2
        ));

        $parent = $this->createProduct('Propagation parent', 'PROPAGATION-PARENT', $firstCategory->id);
        $variant = $this->createProduct('Propagation variant', 'PROPAGATION-VARIANT', $firstCategory->id);
        $parent->variants()->attach($variant->id);

        $this->postStore($this->productInput(array(
            'id' => $parent->id,
            'name' => 'Propagation parent',
            'sku' => 'PROPAGATION-PARENT',
            'category_id' => $secondCategory->id
        )));

        $this->assertRedirectedTo('/admin/products');
        $this->assertSame($secondCategory->id, (int) Product::find($parent->id)->category_id);
        $this->assertSame($secondCategory->id, (int) Product::find($variant->id)->category_id);
    }

    /**
     * Test (Pass): an unchanged variant sku on update passes and another variant's sku fails
     */
    public function testCharacterizationVariantUniqueSkuOnUpdate()
    {
        $parent = $this->createProduct('Unique variant parent', 'UNIQUE-VARIANT-PARENT');
        $first = $this->createProduct('Unique variant one', 'UNIQUE-VARIANT-ONE');
        $second = $this->createProduct('Unique variant two', 'UNIQUE-VARIANT-TWO');
        $parent->variants()->attach($first->id);
        $parent->variants()->attach($second->id);

        $this->postStore($this->productInput(array(
            'product_id' => $parent->id,
            'id' => $first->id,
            'name' => 'Unique variant one',
            'sku' => 'UNIQUE-VARIANT-ONE',
            'short_description' => 'Changed variant short'
        )));

        $this->assertRedirectedTo('/admin/products/view-variant/' . $first->id);
        $this->assertSame('Changed variant short', Product::find($first->id)->short_description);

        $this->postStore($this->productInput(array(
            'product_id' => $parent->id,
            'id' => $first->id,
            'name' => 'Unique variant one',
            'sku' => 'UNIQUE-VARIANT-TWO',
            'short_description' => 'Changed again'
        )));

        $this->assertRedirectedTo('/admin/products/edit-variant/' . $parent->id . '/' . $first->id);
        $this->assertErrorMessage('sku', 'The sku has already been taken.');
        $this->assertSame('Changed variant short', Product::find($first->id)->short_description);
    }

    /**
     * Test (Fail): getDeleteVariantJson for a missing product returns status false and the delete message
     */
    public function testCharacterizationDeleteVariantJsonMissing()
    {
        $response = $this->call('POST', '/admin/products/delete-variant-json/999');

        $this->assertSame(
            json_encode(array(
                'status' => false,
                'message' => 'We are having problem deleting this record. Please try again.'
            )),
            $response->getContent()
        );
    }

    /**
     * Test (Fail): getDeleteVariantJson refuses a product that has been ordered
     */
    public function testCharacterizationDeleteVariantJsonAlreadyOrdered()
    {
        $product = $this->createProduct('Ordered variant json', 'ORDERED-VARIANT-JSON');
        $this->createOrderForProduct($product);

        $response = $this->call('POST', '/admin/products/delete-variant-json/' . $product->id);

        $this->assertSame(
            json_encode(array(
                'status' => false,
                'message' => 'You cannot delete this product because it has been ordered. Please delete the order first.'
            )),
            $response->getContent()
        );
        $this->assertNotNull(Product::find($product->id));
    }

    /**
     * Test (Pass): getDeleteVariantJson deletes the product and returns the success message
     */
    public function testCharacterizationDeleteVariantJsonSuccess()
    {
        $product = $this->createProduct('Deletable variant json', 'DELETABLE-VARIANT-JSON');

        $response = $this->call('POST', '/admin/products/delete-variant-json/' . $product->id);

        $this->assertSame(
            json_encode(array(
                'status' => true,
                'message' => 'The record has been deleted successfully.'
            )),
            $response->getContent()
        );
        $this->assertNull(Product::find($product->id));
    }

    /**
     * Test (Pass): getVariantImgremove deletes a variant image and redirects to the variant edit page
     */
    public function testCharacterizationVariantImgremoveSuccess()
    {
        $parent = $this->createProduct('Variant image parent', 'VARIANT-IMAGE-PARENT');
        $variant = $this->createProduct('Variant image variant', 'VARIANT-IMAGE-VARIANT');
        $parent->variants()->attach($variant->id);
        $image = $this->attachImage($variant);

        $this->call('POST', '/admin/products/variant-imgremove/' . $parent->id . '/' . $image->id);

        $this->assertRedirectedTo('/admin/products/edit-variant/' . $parent->id . '/' . $variant->id);
        $this->assertNull(Image::find($image->id));
    }

    /**
     * Test (Fail): getVariantImgremove refuses an image that does not belong to a variant
     */
    public function testCharacterizationVariantImgremoveWrongOwner()
    {
        $parent = $this->createProduct('Wrong owner parent', 'WRONG-OWNER-PARENT');
        $variant = $this->createProduct('Wrong owner variant', 'WRONG-OWNER-VARIANT');
        $parent->variants()->attach($variant->id);
        $image = $this->attachImage($parent);

        $this->call('POST', '/admin/products/variant-imgremove/' . $parent->id . '/' . $image->id);

        $this->assertRedirectedTo('/admin/products/edit-variant/' . $parent->id . '/' . $image->id);
        $this->assertErrorMessage('errorDeleteImage', 'The image cannot be deleted at this time.');
        $this->assertNotNull(Image::find($image->id));
    }

    /**
     * Test (Fail): getVariantImgremove for a missing image gives errorDeleteImage
     */
    public function testCharacterizationVariantImgremoveMissing()
    {
        $parent = $this->createProduct('Missing image parent', 'MISSING-IMAGE-PARENT');

        $this->call('POST', '/admin/products/variant-imgremove/' . $parent->id . '/999999');

        $this->assertRedirectedTo('/admin/products/edit-variant/' . $parent->id . '/999999');
        $this->assertErrorMessage('errorDeleteImage', 'The image cannot be deleted at this time.');
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

    /**
     * Create a category fixture
     */
    private function createCategory($name, $order = 1)
    {
        return $this->createModel(Category::class, array(
            'name' => $name,
            'short_description' => 'Short',
            'order' => $order
        ));
    }

    /**
     * Create a product fixture
     */
    private function createProduct($name, $sku, $categoryId = 1)
    {
        return $this->createModel(Product::class, array(
            'name' => $name,
            'sku' => $sku,
            'short_description' => 'Short',
            'category_id' => $categoryId,
            'active' => true
        ));
    }

    /**
     * Create an order that contains the given product
     */
    private function createOrderForProduct($product)
    {
        $order = new Order;
        $order->user_id = 1;
        $order->paid = 10.99;
        $order->transaction_id = 'CHARACTERIZATION-ORDER';
        $order->payment_status = 'Completed';
        $order->save();
        $order->products()->attach($product->id);

        return $order;
    }
}
