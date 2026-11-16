<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Bundle;
use Redooor\Redminportal\App\Models\Category;
use Redooor\Redminportal\App\Models\Image;
use Redooor\Redminportal\App\Models\Membership;
use Redooor\Redminportal\App\Models\Module;
use Redooor\Redminportal\App\Models\Pricelist;
use Redooor\Redminportal\App\Models\Product;
use Redooor\Redminportal\App\Models\Tag;
use Redooor\Redminportal\App\Models\Translation;

/**
 * Pins the behaviour before the controller refactor; do not edit to make a refactor pass.
 */
class BundleCharacterizationTest extends BaseControllerTest
{
    use CharacterizationSupport;

    protected $seedProduct;

    public function setUp(): void
    {
        parent::setUp();

        $this->createModel(Category::class, array(
            'name' => 'Bundle characterization category',
            'short_description' => 'Short',
            'order' => 1
        ));

        $this->page = '/admin/bundles';
        $this->viewhas = array(
            'singular' => 'bundle',
            'plural' => 'models'
        );
        $this->input = array(
            'create' => array(
                'name' => 'This is title',
                'short_description' => 'This is body',
                'cn_name' => 'CN name',
                'cn_short_description' => 'CN short body',
                'cn_long_description' => 'CN long body',
                'category_id' => 1,
                'sku' => 'UNIQUESKU001',
                'product_id' => array(1),
                'pricelist_id' => array()
            ),
            'edit' => array(
                'id' => 1,
                'name' => 'This is title',
                'short_description' => 'This is body',
                'cn_name' => 'CN name',
                'cn_short_description' => 'CN short body',
                'cn_long_description' => 'CN long body',
                'category_id' => 1,
                'sku' => 'UNIQUESKU001',
                'product_id' => array(1),
                'pricelist_id' => array()
            )
        );

        $this->seedProduct = $this->createModel(Product::class, array(
            'name' => 'Seeded bundled product',
            'sku' => 'SEEDED-PRODUCT-SKU',
            'short_description' => 'Short',
            'category_id' => 1,
            'active' => true
        ));

        $this->configureImageUploads();
    }

    public function tearDown(): void
    {
        $this->cleanupImageUploads();

        parent::tearDown();
    }

    /**
     * Test (Pass): create then update keeps one row, the main fields and replaces pivots
     */
    public function testCharacterizationCreateThenUpdate()
    {
        $secondProduct = $this->createProductFixture('Second bundled product', 'SECOND-PRODUCT-SKU');

        $this->postStore($this->bundleInput(array(
            'name' => 'Original bundle',
            'sku' => 'ORIGINAL-SKU',
            'short_description' => 'Original short',
            'long_description' => 'Original long',
            'price' => '19.99',
            'featured' => '1',
            'active' => '1'
        )));

        $this->assertRedirectedTo('/admin/bundles');
        $this->assertSame(1, Bundle::count());

        $bundle = Bundle::first();
        $this->assertSame('Original bundle', $bundle->name);
        $this->assertSame('ORIGINAL-SKU', $bundle->sku);
        $this->assertSame('Original short', $bundle->short_description);
        $this->assertSame('Original long', $bundle->long_description);
        $this->assertSame(19.99, (float) $bundle->price);
        $this->assertSame(1, (int) $bundle->featured);
        $this->assertSame(1, (int) $bundle->active);
        $this->assertSame(1, (int) $bundle->category_id);
        $this->assertSame(array($this->seedProduct->id), $this->pivotIds($bundle, 'products'));

        $this->postStore($this->bundleInput(array(
            'id' => $bundle->id,
            'name' => 'Updated bundle',
            'sku' => 'UPDATED-SKU',
            'short_description' => 'Updated short',
            'long_description' => 'Updated long',
            'price' => '29.99',
            'featured' => '',
            'active' => '',
            'category_id' => 1,
            'product_id' => array($secondProduct->id)
        )));

        $this->assertRedirectedTo('/admin/bundles');
        $this->assertSame(1, Bundle::count());

        $bundle = Bundle::first();
        $this->assertSame('Updated bundle', $bundle->name);
        $this->assertSame('UPDATED-SKU', $bundle->sku);
        $this->assertSame('Updated short', $bundle->short_description);
        $this->assertSame('Updated long', $bundle->long_description);
        $this->assertSame(29.99, (float) $bundle->price);
        $this->assertSame(0, (int) $bundle->featured);
        $this->assertSame(0, (int) $bundle->active);
        $this->assertSame(array($secondProduct->id), $this->pivotIds($bundle, 'products'));
    }

    /**
     * Test (Fail): invalid input with no id redirects to create
     */
    public function testCharacterizationValidationRedirectWhenIdOmitted()
    {
        $this->assertValidationFailure(
            '/admin/bundles/create',
            $this->bundleInput(array('name' => '')),
            array('name' => 'The name field is required.')
        );
    }

    /**
     * Test (Fail): invalid input with a nonexistent id redirects to edit/5 before the not-found check
     */
    public function testCharacterizationValidationRedirectWhenIdIsFive()
    {
        $this->assertValidationFailure(
            '/admin/bundles/edit/5',
            $this->bundleInput(array('id' => '5', 'name' => '')),
            array('name' => 'The name field is required.')
        );
    }

    /**
     * Test (Fail): an empty id is converted to null by the HTTP middleware, so it redirects to create
     */
    public function testCharacterizationValidationRedirectWhenIdIsEmptyString()
    {
        $this->assertValidationFailure(
            '/admin/bundles/create',
            $this->bundleInput(array('id' => '', 'name' => '')),
            array('name' => 'The name field is required.')
        );
    }

    /**
     * Test (Fail): an id of zero is present and redirects to edit/0
     */
    public function testCharacterizationValidationRedirectWhenIdIsZero()
    {
        $this->assertValidationFailure(
            '/admin/bundles/edit/0',
            $this->bundleInput(array('id' => '0', 'name' => '')),
            array('name' => 'The name field is required.')
        );
    }

    /**
     * Test (Fail): invalid input with an existing id redirects to edit/<id>
     */
    public function testCharacterizationValidationRedirectWhenIdExists()
    {
        $bundle = $this->createModel(Bundle::class, array(
            'name' => 'Existing bundle',
            'sku' => 'EXISTING-SKU',
            'short_description' => 'Existing short',
            'category_id' => 1
        ));

        $this->assertValidationFailure(
            '/admin/bundles/edit/' . $bundle->id,
            $this->bundleInput(array('id' => $bundle->id, 'name' => '')),
            array('name' => 'The name field is required.')
        );
    }

    /**
     * Test (Fail): valid input with a nonexistent id gives the bundleError not-found message
     */
    public function testCharacterizationNonexistentIdWithValidFields()
    {
        $this->postStore($this->bundleInput(array(
            'id' => '5',
            'name' => 'Valid bundle',
            'sku' => 'VALID-SKU'
        )));

        $this->assertRedirectedTo('/admin/bundles');
        $this->assertErrorMessage(
            'bundleError',
            'The bundle does not exist or may have been deleted.'
        );
        $this->assertSame(0, Bundle::count());
    }

    /**
     * Test (Fail): name is required
     */
    public function testCharacterizationMissingName()
    {
        $this->assertValidationFailure(
            '/admin/bundles/create',
            $this->bundleInput(array('name' => '')),
            array('name' => 'The name field is required.')
        );
    }

    /**
     * Test (Fail): short_description is required
     */
    public function testCharacterizationMissingShortDescription()
    {
        $this->assertValidationFailure(
            '/admin/bundles/create',
            $this->bundleInput(array('short_description' => '')),
            array('short_description' => 'The short description field is required.')
        );
    }

    /**
     * Test (Fail): sku is required
     */
    public function testCharacterizationMissingSku()
    {
        $this->assertValidationFailure(
            '/admin/bundles/create',
            $this->bundleInput(array('sku' => '')),
            array('sku' => 'The sku field is required.')
        );
    }

    /**
     * Test (Fail): category_id is required
     */
    public function testCharacterizationMissingCategoryId()
    {
        $this->assertValidationFailure(
            '/admin/bundles/create',
            $this->bundleInput(array('category_id' => '')),
            array('category_id' => 'The category id field is required.')
        );
    }

    /**
     * Test (Fail): price has a numeric rule and uses the default message
     */
    public function testCharacterizationInvalidPrice()
    {
        $this->assertValidationFailure(
            '/admin/bundles/create',
            $this->bundleInput(array('price' => 'abc')),
            array('price' => 'The price field must be a number.')
        );
    }

    /**
     * Test (Fail): sku has an alpha_dash rule
     */
    public function testCharacterizationInvalidSkuFormat()
    {
        $this->assertValidationFailure(
            '/admin/bundles/create',
            $this->bundleInput(array('sku' => 'bad sku!')),
            array('sku' => 'The sku field must only contain letters, numbers, dashes, and underscores.')
        );
    }

    /**
     * Test (Fail): tags has a regex rule
     */
    public function testCharacterizationInvalidTagsFormat()
    {
        $this->assertValidationFailure(
            '/admin/bundles/create',
            $this->bundleInput(array('tags' => 'a!b')),
            array('tags' => 'The tags field format is invalid.')
        );
    }

    /**
     * Test (Fail): old input keeps scalar values but never an uploaded file
     */
    public function testCharacterizationOldInputWithFile()
    {
        $this->assertValidationFailure(
            '/admin/bundles/create',
            $this->bundleInput(array('name' => '', 'short_description' => 'Old short', 'sku' => 'OLD-SKU')),
            array('name' => 'The name field is required.'),
            array('images' => array($this->uploadedImage('old-input.png')))
        );

        $this->assertOldInput(array('short_description' => 'Old short', 'sku' => 'OLD-SKU', 'category_id' => 1));
        $this->assertSame(0, Image::count());
    }

    /**
     * Test (Fail): a JSON Accept header still redirects with session errors in the default bag
     */
    public function testCharacterizationJsonRequestStillRedirectsWithDefaultBag()
    {
        $this->postStoreAsJson($this->bundleInput(array(
            'name' => '',
            '_error_bag' => 'custom'
        )));

        $this->assertResponseStatus(302);
        $this->assertRedirectedTo('/admin/bundles/create');
        $this->assertSessionHasErrors('name');
        $this->assertErrorMessage('name', 'The name field is required.');
        $this->assertErrorBagIsDefaultOnly();
    }

    /**
     * Test (Pass): a product name does not block a Bundle name because bundles use their own table
     */
    public function testCharacterizationProductNameDoesNotBlockBundleName()
    {
        $this->createProductFixture('Shared product name', 'SHARED-PRODUCT-SKU');

        $this->postStore($this->bundleInput(array('name' => 'Shared product name', 'sku' => 'SHARED-BUNDLE-SKU')));

        $this->assertRedirectedTo('/admin/bundles');
        $this->response->assertSessionHasNoErrors();
        $this->assertSame(1, Bundle::count());
        $this->assertSame('Shared product name', Bundle::first()->name);
    }

    /**
     * Test (Fail): Bundle names are unique among bundles
     */
    public function testCharacterizationBundleNameCannotRepeatAcrossBundles()
    {
        $this->postStore($this->bundleInput(array('name' => 'Duplicate bundle', 'sku' => 'DUPLICATE-ONE')));
        $this->assertRedirectedTo('/admin/bundles');
        $this->assertSame(1, Bundle::count());

        $this->assertValidationFailure(
            '/admin/bundles/create',
            $this->bundleInput(array('name' => 'Duplicate bundle', 'sku' => 'DUPLICATE-TWO')),
            array('name' => 'The name has already been taken.')
        );

        $this->assertSame(1, Bundle::count());
    }

    /**
     * Test (Pass): an unchanged sku on update passes and another bundle's sku fails
     */
    public function testCharacterizationUniqueSkuOnUpdate()
    {
        $first = $this->createModel(Bundle::class, array(
            'name' => 'First bundle',
            'sku' => 'FIRST-BUNDLE-SKU',
            'short_description' => 'First short',
            'category_id' => 1
        ));
        $second = $this->createModel(Bundle::class, array(
            'name' => 'Second bundle',
            'sku' => 'SECOND-BUNDLE-SKU',
            'short_description' => 'Second short',
            'category_id' => 1
        ));

        $this->postStore($this->bundleInput(array(
            'id' => $first->id,
            'name' => 'First bundle',
            'sku' => 'FIRST-BUNDLE-SKU',
            'short_description' => 'Changed short'
        )));

        $this->assertRedirectedTo('/admin/bundles');
        $this->assertSame('Changed short', Bundle::find($first->id)->short_description);

        $this->postStore($this->bundleInput(array(
            'id' => $first->id,
            'name' => 'First bundle',
            'sku' => 'SECOND-BUNDLE-SKU',
            'short_description' => 'Changed again'
        )));

        $this->assertRedirectedTo('/admin/bundles/edit/' . $first->id);
        $this->assertErrorMessage('sku', 'The sku has already been taken.');
        $this->assertSame('Changed short', Bundle::find($first->id)->short_description);
        $this->assertSame('SECOND-BUNDLE-SKU', Bundle::find($second->id)->sku);
    }

    /**
     * Test (Pass): create writes one cn translation row with the exact decoded content
     */
    public function testCharacterizationTranslationCreated()
    {
        $this->postStore($this->bundleInput(array(
            'name' => 'Translated bundle',
            'sku' => 'TRANSLATED-BUNDLE',
            'cn_name' => 'CN name',
            'cn_short_description' => 'CN short',
            'cn_long_description' => 'CN long'
        )));

        $this->assertRedirectedTo('/admin/bundles');

        $this->assertTranslations(Bundle::first(), array(
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
        $this->postStore($this->bundleInput(array(
            'name' => 'Translation owner',
            'sku' => 'TRANSLATION-OWNER',
            'cn_name' => 'CN name',
            'cn_short_description' => 'CN short',
            'cn_long_description' => 'CN long'
        )));

        $bundle = Bundle::first();
        $translation = $bundle->translations()->first();
        $translation->content = json_encode(array(
            'name' => 'pre-existing',
            'short_description' => 'pre-existing short',
            'long_description' => 'pre-existing long'
        ));
        $translation->save();

        $this->postStore($this->bundleInput(array(
            'id' => $bundle->id,
            'name' => 'Translation owner',
            'sku' => 'TRANSLATION-OWNER',
            'cn_name' => 'Updated CN name',
            'cn_short_description' => 'Updated CN short',
            'cn_long_description' => 'Updated CN long'
        )));

        $this->assertRedirectedTo('/admin/bundles');
        $this->assertSame(1, Bundle::count());

        $translations = $this->assertTranslations(Bundle::find($bundle->id), array(
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
        $this->postStore($this->bundleInput(array(
            'name' => 'No translations',
            'sku' => 'NO-TRANSLATIONS'
        )));

        $this->assertRedirectedTo('/admin/bundles');

        $this->assertTranslations(Bundle::first(), array(
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
        $this->postStore($this->bundleInput(array(
            'name' => 'Tagged bundle',
            'sku' => 'TAGGED-BUNDLE',
            'tags' => 'alpha,beta'
        )));

        $this->assertRedirectedTo('/admin/bundles');
        $this->assertSame(array('alpha', 'beta'), $this->tagNames(Bundle::first()));
    }

    /**
     * Test (Pass): a second update with new tags replaces the old ones
     */
    public function testCharacterizationSecondUpdateWithNewTagsReplacesOldTags()
    {
        $this->postStore($this->bundleInput(array(
            'name' => 'Tagged bundle',
            'sku' => 'TAGGED-BUNDLE',
            'tags' => 'alpha,beta'
        )));

        $bundle = Bundle::first();

        $this->postStore($this->bundleInput(array(
            'id' => $bundle->id,
            'name' => 'Tagged bundle updated',
            'sku' => 'TAGGED-BUNDLE',
            'tags' => 'gamma'
        )));

        $this->assertRedirectedTo('/admin/bundles');
        $this->assertSame(array('gamma'), $this->tagNames(Bundle::find($bundle->id)));
    }

    /**
     * Test (Fail): an empty tags string becomes null and fails the regex rule, saving nothing
     */
    public function testCharacterizationEmptyTagsStringFailsValidation()
    {
        $this->postStore($this->bundleInput(array(
            'name' => 'Tagged bundle',
            'sku' => 'TAGGED-BUNDLE',
            'tags' => 'alpha'
        )));

        $bundle = Bundle::first();

        $this->assertValidationFailure(
            '/admin/bundles/edit/' . $bundle->id,
            $this->bundleInput(array(
                'id' => $bundle->id,
                'name' => 'Tagged bundle updated',
                'sku' => 'TAGGED-BUNDLE',
                'tags' => ''
            )),
            array('tags' => 'The tags field format is invalid.')
        );

        $this->assertSame('Tagged bundle', Bundle::find($bundle->id)->name);
        $this->assertSame(array('alpha'), $this->tagNames(Bundle::find($bundle->id)));
    }

    /**
     * Test (Pass): a tags value of "0" keeps the old tags and still saves the bundle
     */
    public function testCharacterizationZeroTagsStringKeepsOldTags()
    {
        $this->postStore($this->bundleInput(array(
            'name' => 'Tagged bundle',
            'sku' => 'TAGGED-BUNDLE',
            'tags' => 'alpha'
        )));

        $bundle = Bundle::first();

        $this->postStore($this->bundleInput(array(
            'id' => $bundle->id,
            'name' => 'Tagged bundle updated',
            'sku' => 'TAGGED-BUNDLE',
            'tags' => '0'
        )));

        $this->assertRedirectedTo('/admin/bundles');
        $this->assertSame('Tagged bundle updated', Bundle::find($bundle->id)->name);
        $this->assertSame(array('alpha'), $this->tagNames(Bundle::find($bundle->id)));
    }

    /**
     * Test (Fail): a tags array fails the regex rule, saving nothing and keeping the old tags
     */
    public function testCharacterizationArrayTagsFailsValidation()
    {
        $this->postStore($this->bundleInput(array(
            'name' => 'Tagged bundle',
            'sku' => 'TAGGED-BUNDLE',
            'tags' => 'alpha'
        )));

        $bundle = Bundle::first();

        $this->assertValidationFailure(
            '/admin/bundles/edit/' . $bundle->id,
            $this->bundleInput(array(
                'id' => $bundle->id,
                'name' => 'Tagged bundle updated',
                'sku' => 'TAGGED-BUNDLE',
                'tags' => array()
            )),
            array('tags' => 'The tags field format is invalid.')
        );

        $this->assertSame('Tagged bundle', Bundle::find($bundle->id)->name);
        $this->assertSame(array('alpha'), $this->tagNames(Bundle::find($bundle->id)));
    }

    /**
     * Test (Pass): no tags input keeps the old tags
     */
    public function testCharacterizationNoTagsInputKeepsOldTags()
    {
        $this->postStore($this->bundleInput(array(
            'name' => 'Tagged bundle',
            'sku' => 'TAGGED-BUNDLE',
            'tags' => 'alpha'
        )));

        $bundle = Bundle::first();

        $this->postStore($this->bundleInput(array(
            'id' => $bundle->id,
            'name' => 'Tagged bundle updated',
            'sku' => 'TAGGED-BUNDLE'
        )));

        $this->assertRedirectedTo('/admin/bundles');
        $this->assertSame('Tagged bundle updated', Bundle::find($bundle->id)->name);
        $this->assertSame(array('alpha'), $this->tagNames(Bundle::find($bundle->id)));
    }

    /**
     * Test (Pass): getEdit passes bundle, product_id, pricelist_id, translated, tagString and imagine
     */
    public function testCharacterizationGetEditViewData()
    {
        $bundle = $this->createModel(Bundle::class, array(
            'name' => 'View bundle',
            'sku' => 'VIEW-BUNDLE-SKU',
            'short_description' => 'View short',
            'category_id' => 1
        ));
        $bundle->products()->attach($this->seedProduct->id);
        $pricelist = $this->createPricelistFixture('View');
        $bundle->pricelists()->attach($pricelist->id);

        $translation = new Translation;
        $translation->lang = 'cn';
        $translation->content = json_encode(array(
            'name' => 'View CN name',
            'short_description' => 'View CN short',
            'long_description' => 'View CN long'
        ));
        $bundle->translations()->save($translation);
        Tag::addTag($bundle, 'alpha');
        Tag::addTag($bundle, 'beta');

        $this->call('GET', '/admin/bundles/edit/' . $bundle->id);

        $this->assertResponseOk();
        $this->assertViewHas('bundle', function ($value) use ($bundle) {
            return $value instanceof Bundle && $value->id == $bundle->id;
        });
        $this->assertViewHas('product_id', function ($value) use ($bundle) {
            return isset($value[$bundle->products()->first()->id]);
        });
        $this->assertViewHas('pricelist_id', function ($value) use ($pricelist) {
            return isset($value[$pricelist->id]);
        });
        $this->assertViewHas('translated', function ($value) {
            return isset($value['cn']) && $value['cn']->name === 'View CN name';
        });
        $this->assertViewHas('tagString', 'alpha,beta');
        $this->assertViewHas('categories');
        $this->assertViewHas('products');
        $this->assertViewHas('membermodules', function ($value) use ($pricelist) {
            return isset($value[$pricelist->id]);
        });
        $this->assertViewHas('imagine');
    }

    /**
     * Test (Pass): getCreate passes categories, products and membermodules
     */
    public function testCharacterizationGetCreateViewData()
    {
        $this->createPricelistFixture('Create');

        $this->call('GET', '/admin/bundles/create');

        $this->assertResponseOk();
        $this->assertViewHas('categories');
        $this->assertViewHas('products', function ($value) {
            return $value->has($this->seedProduct->id);
        });
        $this->assertViewHas('membermodules');
    }

    /**
     * Test (Fail): getEdit for a nonexistent id uses the exact editError message
     */
    public function testCharacterizationGetEditMissing()
    {
        $this->call('GET', '/admin/bundles/edit/999');

        $this->assertRedirectedTo('/admin/bundles');
        $this->assertErrorMessage(
            'editError',
            'The bundle cannot be found because it does not exist or may have been deleted.'
        );
    }

    /**
     * Test (Pass): two files in images[] attach two Image rows
     */
    public function testCharacterizationImagesArrayAttachesTwoRows()
    {
        $this->postStore(
            $this->bundleInput(array('name' => 'Image bundle', 'sku' => 'IMAGE-BUNDLE-SKU')),
            array('images' => array(
                $this->uploadedImage('first.png'),
                $this->uploadedImage('second.png')
            ))
        );

        $this->assertRedirectedTo('/admin/bundles');
        $this->assertSame(1, Bundle::count());
        $this->assertSame(2, Image::count());

        $bundle = Bundle::first();
        $this->assertSame(2, $bundle->images()->count());
        $this->assertSame(Bundle::class, Image::first()->imageable_type);
        $this->assertSame($bundle->id, Image::first()->imageable_id);
    }

    /**
     * Test (Pass): the legacy single image field attaches one Image row
     */
    public function testCharacterizationLegacySingleImageAttachesOneRow()
    {
        $this->postStore(
            $this->bundleInput(array('name' => 'Legacy bundle', 'sku' => 'LEGACY-BUNDLE-SKU')),
            array('image' => $this->uploadedImage('legacy.png'))
        );

        $this->assertRedirectedTo('/admin/bundles');
        $this->assertSame(1, Bundle::count());
        $this->assertSame(1, Image::count());
        $this->assertSame(Bundle::class, Image::first()->imageable_type);
    }

    /**
     * Test (Fail): a validation failure creates no Image rows
     */
    public function testCharacterizationValidationFailureCreatesNoImages()
    {
        $this->assertValidationFailure(
            '/admin/bundles/create',
            $this->bundleInput(array('name' => '')),
            array('name' => 'The name field is required.'),
            array('images' => array($this->uploadedImage('not-saved.png')))
        );

        $this->assertSame(0, Bundle::count());
        $this->assertSame(0, Image::count());
    }

    /**
     * Test (Pass): images are removed through the images controller and redirect back
     */
    public function testCharacterizationImageDeleteThroughImagesController()
    {
        $bundle = $this->createModel(Bundle::class, array(
            'name' => 'Image delete bundle',
            'sku' => 'IMAGE-DELETE-BUNDLE',
            'short_description' => 'Short',
            'category_id' => 1
        ));
        $image = $this->attachImage($bundle);

        $this->call('POST', '/admin/images/delete/' . $image->id, array(), array(), array(), array(
            'HTTP_REFERER' => '/admin/bundles/edit/' . $bundle->id
        ));

        $this->assertRedirectedTo('/admin/bundles/edit/' . $bundle->id);
        $this->assertNull(Image::find($image->id));
    }

    /**
     * Test (Fail): deleting a missing image through the images controller gives deleteError
     */
    public function testCharacterizationImageDeleteMissingThroughImagesController()
    {
        $this->call('POST', '/admin/images/delete/999999', array(), array(), array(), array(
            'HTTP_REFERER' => '/admin/bundles'
        ));

        $this->assertRedirectedTo('/admin/bundles');
        $this->assertErrorMessage('deleteError', 'The image cannot be deleted at this time.');
        $this->assertSame(0, Image::count());
    }

    /**
     * Test (Fail): no product or pricelist selected gives the applyToError message and redirects to create
     */
    public function testCharacterizationApplyToErrorOnCreate()
    {
        $this->postStore($this->bundleInput(array(
            'name' => 'No items bundle',
            'sku' => 'NO-ITEMS-SKU',
            'product_id' => array(999),
            'pricelist_id' => array()
        )));

        $this->assertRedirectedTo('/admin/bundles/create');
        $this->assertErrorMessage(
            'applyToError',
            'You have not selected any Product or Membership/Module under Bundled Items.'
        );
        $this->assertSame(0, Bundle::count());
    }

    /**
     * Test (Fail): no product or pricelist selected on update gives applyToError and redirects to edit
     */
    public function testCharacterizationApplyToErrorOnUpdate()
    {
        $bundle = $this->createModel(Bundle::class, array(
            'name' => 'Original no items',
            'sku' => 'ORIGINAL-NO-ITEMS',
            'short_description' => 'Short',
            'category_id' => 1
        ));

        $this->postStore($this->bundleInput(array(
            'id' => $bundle->id,
            'name' => 'Changed no items',
            'sku' => 'ORIGINAL-NO-ITEMS',
            'product_id' => array(999),
            'pricelist_id' => array()
        )));

        $this->assertRedirectedTo('/admin/bundles/edit/' . $bundle->id);
        $this->assertErrorMessage(
            'applyToError',
            'You have not selected any Product or Membership/Module under Bundled Items.'
        );
        $this->assertSame('Original no items', Bundle::find($bundle->id)->name);
    }

    /**
     * Test (Pass): pricelist pivots are saved on create and replaced on update
     */
    public function testCharacterizationPricelistPivotsSavedAndReplaced()
    {
        $first = $this->createPricelistFixture('First');
        $second = $this->createPricelistFixture('Second');

        $this->postStore($this->bundleInput(array(
            'name' => 'Priced bundle',
            'sku' => 'PRICED-BUNDLE',
            'product_id' => array(),
            'pricelist_id' => array($first->id)
        )));

        $this->assertRedirectedTo('/admin/bundles');
        $bundle = Bundle::first();
        $this->assertSame(array($first->id), $this->pivotIds($bundle, 'pricelists'));

        $this->postStore($this->bundleInput(array(
            'id' => $bundle->id,
            'name' => 'Priced bundle',
            'sku' => 'PRICED-BUNDLE',
            'product_id' => array(),
            'pricelist_id' => array($second->id)
        )));

        $this->assertRedirectedTo('/admin/bundles');
        $this->assertSame(array($second->id), $this->pivotIds(Bundle::find($bundle->id), 'pricelists'));
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
