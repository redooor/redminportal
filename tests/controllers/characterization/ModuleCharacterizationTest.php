<?php namespace Redooor\Redminportal\Test;

use Illuminate\Support\Facades\DB;
use Redooor\Redminportal\App\Models\Category;
use Redooor\Redminportal\App\Models\Image;
use Redooor\Redminportal\App\Models\Media;
use Redooor\Redminportal\App\Models\Membership;
use Redooor\Redminportal\App\Models\Module;
use Redooor\Redminportal\App\Models\ModuleMediaMembership;
use Redooor\Redminportal\App\Models\Pricelist;
use Redooor\Redminportal\App\Models\Tag;
use Redooor\Redminportal\App\Models\Translation;

/**
 * Pins the behaviour before the controller refactor; do not edit to make a refactor pass.
 */
class ModuleCharacterizationTest extends BaseControllerTest
{
    use CharacterizationSupport;

    public function setUp(): void
    {
        parent::setUp();

        $this->page = '/admin/modules';
        $this->viewhas = array(
            'singular' => 'module',
            'plural' => 'models'
        );
        $this->input = array(
            'create' => array(
                'name' => 'This is title',
                'short_description' => 'This is body',
                'cn_name' => 'CN name',
                'cn_short_description' => 'CN short body',
                'category_id' => 1,
                'sku' => 'UNIQUESKU001',
                'cn_long_description' => 'CN long body'
            ),
            'edit' => array(
                'id' => 1,
                'name' => 'This is title',
                'short_description' => 'This is body',
                'cn_name' => 'CN name',
                'cn_short_description' => 'CN short body',
                'category_id' => 1,
                'sku' => 'UNIQUESKU001',
                'cn_long_description' => 'CN long body'
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
        $this->postStore(array(
            'name' => 'Original module',
            'sku' => 'ORIGINAL-SKU',
            'short_description' => 'Original short',
            'long_description' => 'Original long',
            'category_id' => '1',
            'featured' => '1',
            'active' => '1'
        ));

        $this->assertRedirectedTo('/admin/modules');
        $this->assertSame(1, Module::count());

        $module = Module::first();
        $this->assertSame('Original module', $module->name);
        $this->assertSame('ORIGINAL-SKU', $module->sku);
        $this->assertSame('Original short', $module->short_description);
        $this->assertSame('Original long', $module->long_description);
        $this->assertSame(1, (int) $module->category_id);
        $this->assertSame(1, (int) $module->featured);
        $this->assertSame(1, (int) $module->active);

        $this->postStore(array(
            'id' => $module->id,
            'name' => 'Updated module',
            'sku' => 'UPDATED-SKU',
            'short_description' => 'Updated short',
            'long_description' => 'Updated long',
            'category_id' => '1',
            'featured' => '',
            'active' => ''
        ));

        $this->assertRedirectedTo('/admin/modules');
        $this->assertSame(1, Module::count());

        $module = Module::first();
        $this->assertSame('Updated module', $module->name);
        $this->assertSame('UPDATED-SKU', $module->sku);
        $this->assertSame('Updated short', $module->short_description);
        $this->assertSame('Updated long', $module->long_description);
        $this->assertSame(0, (int) $module->featured);
        $this->assertSame(0, (int) $module->active);
    }

    /**
     * Test (Fail): invalid input with no id redirects to create
     */
    public function testCharacterizationValidationRedirectWhenIdOmitted()
    {
        $this->assertValidationFailure(
            '/admin/modules/create',
            array('short_description' => 'Short', 'sku' => 'VALID-SKU', 'category_id' => 1),
            array('name' => 'The name field is required.')
        );
    }

    /**
     * Test (Fail): invalid input with a nonexistent id redirects to edit/5 before the not-found check
     */
    public function testCharacterizationValidationRedirectWhenIdIsFive()
    {
        $this->assertValidationFailure(
            '/admin/modules/edit/5',
            array('id' => '5', 'short_description' => 'Short', 'sku' => 'VALID-SKU', 'category_id' => 1),
            array('name' => 'The name field is required.')
        );
    }

    /**
     * Test (Fail): an empty id is converted to null by the HTTP middleware, so it redirects to create
     */
    public function testCharacterizationValidationRedirectWhenIdIsEmptyString()
    {
        $this->assertValidationFailure(
            '/admin/modules/create',
            array('id' => '', 'short_description' => 'Short', 'sku' => 'VALID-SKU', 'category_id' => 1),
            array('name' => 'The name field is required.')
        );
    }

    /**
     * Test (Fail): an id of zero is present and redirects to edit/0
     */
    public function testCharacterizationValidationRedirectWhenIdIsZero()
    {
        $this->assertValidationFailure(
            '/admin/modules/edit/0',
            array('id' => '0', 'short_description' => 'Short', 'sku' => 'VALID-SKU', 'category_id' => 1),
            array('name' => 'The name field is required.')
        );
    }

    /**
     * Test (Fail): invalid input with an existing id redirects to edit/<id>
     */
    public function testCharacterizationValidationRedirectWhenIdExists()
    {
        $module = $this->createModel(Module::class, array(
            'name' => 'Existing module',
            'sku' => 'EXISTING-SKU',
            'short_description' => 'Existing short',
            'category_id' => 1
        ));

        $this->assertValidationFailure(
            '/admin/modules/edit/' . $module->id,
            array('id' => $module->id, 'short_description' => 'Short', 'sku' => 'VALID-SKU', 'category_id' => 1),
            array('name' => 'The name field is required.')
        );
    }

    /**
     * Test (Fail): valid input with a nonexistent id gives the editError not-found message
     */
    public function testCharacterizationNonexistentIdWithValidFields()
    {
        $this->postStore(array(
            'id' => '5',
            'name' => 'Valid module',
            'sku' => 'VALID-SKU',
            'short_description' => 'Valid short',
            'category_id' => 1
        ));

        $this->assertRedirectedTo('/admin/modules');
        $this->assertErrorMessage(
            'editError',
            'The module cannot be found because it does not exist or may have been deleted.'
        );
        $this->assertSame(0, Module::count());
    }

    /**
     * Test (Fail): name is required
     */
    public function testCharacterizationMissingName()
    {
        $this->assertValidationFailure(
            '/admin/modules/create',
            array('short_description' => 'Short', 'sku' => 'VALID-SKU', 'category_id' => 1),
            array('name' => 'The name field is required.')
        );
    }

    /**
     * Test (Fail): short_description is required
     */
    public function testCharacterizationMissingShortDescription()
    {
        $this->assertValidationFailure(
            '/admin/modules/create',
            array('name' => 'Valid module', 'sku' => 'VALID-SKU', 'category_id' => 1),
            array('short_description' => 'The short description field is required.')
        );
    }

    /**
     * Test (Fail): sku is required
     */
    public function testCharacterizationMissingSku()
    {
        $this->assertValidationFailure(
            '/admin/modules/create',
            array('name' => 'Valid module', 'short_description' => 'Short', 'category_id' => 1),
            array('sku' => 'The sku field is required.')
        );
    }

    /**
     * Test (Fail): category_id is required
     */
    public function testCharacterizationMissingCategoryId()
    {
        $this->assertValidationFailure(
            '/admin/modules/create',
            array('name' => 'Valid module', 'short_description' => 'Short', 'sku' => 'VALID-SKU'),
            array('category_id' => 'The category id field is required.')
        );
    }

    /**
     * Test (Fail): category_id min:1 uses the custom "The category field is required." message
     */
    public function testCharacterizationCategoryIdZero()
    {
        $this->assertValidationFailure(
            '/admin/modules/create',
            array('name' => 'Valid module', 'short_description' => 'Short', 'sku' => 'VALID-SKU', 'category_id' => '0'),
            array('category_id' => 'The category field is required.')
        );
    }

    /**
     * Test (Fail): category_id must be numeric
     */
    public function testCharacterizationCategoryIdNonNumeric()
    {
        $this->assertValidationFailure(
            '/admin/modules/create',
            array('name' => 'Valid module', 'short_description' => 'Short', 'sku' => 'VALID-SKU', 'category_id' => 'abc'),
            array('category_id' => 'The category id field must be a number.')
        );
    }

    /**
     * Test (Fail): sku has an alpha_dash rule
     */
    public function testCharacterizationInvalidSkuFormat()
    {
        $this->assertValidationFailure(
            '/admin/modules/create',
            array('name' => 'Valid module', 'short_description' => 'Short', 'sku' => 'bad sku!', 'category_id' => 1),
            array('sku' => 'The sku field must only contain letters, numbers, dashes, and underscores.')
        );
    }

    /**
     * Test (Fail): tags has a regex rule
     */
    public function testCharacterizationInvalidTagsFormat()
    {
        $this->assertValidationFailure(
            '/admin/modules/create',
            array('name' => 'Valid module', 'short_description' => 'Short', 'sku' => 'VALID-SKU', 'category_id' => 1, 'tags' => 'a!b'),
            array('tags' => 'The tags field format is invalid.')
        );
    }

    /**
     * Test (Fail): old input keeps scalar values but never an uploaded file
     */
    public function testCharacterizationOldInputWithFile()
    {
        $this->assertValidationFailure(
            '/admin/modules/create',
            array('short_description' => 'Short', 'sku' => 'VALID-SKU', 'category_id' => '2'),
            array('name' => 'The name field is required.'),
            array('images' => array($this->uploadedImage('old-input.png')))
        );

        $this->assertOldInput(array('short_description' => 'Short', 'sku' => 'VALID-SKU', 'category_id' => '2'));
        $this->assertSame(0, Image::count());
    }

    /**
     * Test (Fail): a JSON Accept header still redirects with session errors in the default bag
     */
    public function testCharacterizationJsonRequestStillRedirectsWithDefaultBag()
    {
        $this->postStoreAsJson(array(
            'short_description' => 'Short',
            'sku' => 'VALID-SKU',
            'category_id' => 1,
            '_error_bag' => 'custom'
        ));

        $this->assertResponseStatus(302);
        $this->assertRedirectedTo('/admin/modules/create');
        $this->assertSessionHasErrors('name');
        $this->assertErrorMessage('name', 'The name field is required.');
        $this->assertErrorBagIsDefaultOnly();
    }

    /**
     * Test (Pass): an unchanged name on update passes unique and another row's name fails
     */
    public function testCharacterizationUniqueNameOnUpdate()
    {
        $first = $this->createModel(Module::class, array(
            'name' => 'First module',
            'sku' => 'FIRST-SKU',
            'short_description' => 'First short',
            'category_id' => 1
        ));
        $this->createModel(Module::class, array(
            'name' => 'Second module',
            'sku' => 'SECOND-SKU',
            'short_description' => 'Second short',
            'category_id' => 1
        ));

        $this->postStore(array(
            'id' => $first->id,
            'name' => 'First module',
            'sku' => 'FIRST-SKU',
            'short_description' => 'Changed short',
            'category_id' => 1
        ));

        $this->assertRedirectedTo('/admin/modules');
        $this->assertSame('Changed short', Module::find($first->id)->short_description);

        $this->postStore(array(
            'id' => $first->id,
            'name' => 'Second module',
            'sku' => 'FIRST-SKU',
            'short_description' => 'Changed again',
            'category_id' => 1
        ));

        $this->assertRedirectedTo('/admin/modules/edit/' . $first->id);
        $this->assertErrorMessage('name', 'The name has already been taken.');
        $this->assertSame('Changed short', Module::find($first->id)->short_description);
    }

    /**
     * Test (Pass): an unchanged sku on update passes unique and another row's sku fails
     */
    public function testCharacterizationUniqueSkuOnUpdate()
    {
        $first = $this->createModel(Module::class, array(
            'name' => 'First module',
            'sku' => 'FIRST-SKU',
            'short_description' => 'First short',
            'category_id' => 1
        ));
        $this->createModel(Module::class, array(
            'name' => 'Second module',
            'sku' => 'SECOND-SKU',
            'short_description' => 'Second short',
            'category_id' => 1
        ));

        $this->postStore(array(
            'id' => $first->id,
            'name' => 'First module',
            'sku' => 'FIRST-SKU',
            'short_description' => 'Changed short',
            'category_id' => 1
        ));

        $this->assertRedirectedTo('/admin/modules');
        $this->assertSame('Changed short', Module::find($first->id)->short_description);

        $this->postStore(array(
            'id' => $first->id,
            'name' => 'First module',
            'sku' => 'SECOND-SKU',
            'short_description' => 'Changed again',
            'category_id' => 1
        ));

        $this->assertRedirectedTo('/admin/modules/edit/' . $first->id);
        $this->assertErrorMessage('sku', 'The sku has already been taken.');
        $this->assertSame('Changed short', Module::find($first->id)->short_description);
    }

    /**
     * Test (Pass): create writes one cn translation row with the exact decoded content
     */
    public function testCharacterizationTranslationCreated()
    {
        $this->postStore(array(
            'name' => 'Translated module',
            'sku' => 'TRANSLATED-SKU',
            'short_description' => 'Translated short',
            'long_description' => 'Translated long',
            'category_id' => 1,
            'cn_name' => 'CN name',
            'cn_short_description' => 'CN short',
            'cn_long_description' => 'CN long'
        ));

        $this->assertRedirectedTo('/admin/modules');

        $this->assertTranslations(Module::first(), array(
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
        $this->postStore(array(
            'name' => 'Translation owner',
            'sku' => 'TRANSLATION-SKU',
            'short_description' => 'Translated short',
            'long_description' => 'Translated long',
            'category_id' => 1,
            'cn_name' => 'CN name',
            'cn_short_description' => 'CN short',
            'cn_long_description' => 'CN long'
        ));

        $module = Module::first();
        $translation = $module->translations()->first();
        $translation->content = json_encode(array(
            'name' => 'pre-existing',
            'short_description' => 'pre-existing short',
            'long_description' => 'pre-existing long'
        ));
        $translation->save();

        $this->postStore(array(
            'id' => $module->id,
            'name' => 'Translation owner',
            'sku' => 'TRANSLATION-SKU',
            'short_description' => 'Translated short',
            'long_description' => 'Translated long',
            'category_id' => 1,
            'cn_name' => 'Updated CN name',
            'cn_short_description' => 'Updated CN short',
            'cn_long_description' => 'Updated CN long'
        ));

        $this->assertRedirectedTo('/admin/modules');
        $this->assertSame(1, Module::count());

        $translations = $this->assertTranslations(Module::find($module->id), array(
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
        $this->postStore(array(
            'name' => 'No translations',
            'sku' => 'NO-TRANSLATIONS',
            'short_description' => 'Short',
            'category_id' => 1
        ));

        $this->assertRedirectedTo('/admin/modules');

        $this->assertTranslations(Module::first(), array(
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
        $this->postStore(array(
            'name' => 'Tagged module',
            'sku' => 'TAGGED-SKU',
            'short_description' => 'Short',
            'category_id' => 1,
            'tags' => 'alpha,beta'
        ));

        $this->assertRedirectedTo('/admin/modules');

        $this->assertSame(array('alpha', 'beta'), $this->tagNames(Module::first()));
    }

    /**
     * Test (Pass): a second update with new tags replaces the old ones
     */
    public function testCharacterizationSecondUpdateWithNewTagsReplacesOldTags()
    {
        $this->postStore(array(
            'name' => 'Tagged module',
            'sku' => 'TAGGED-SKU',
            'short_description' => 'Short',
            'category_id' => 1,
            'tags' => 'alpha,beta'
        ));

        $module = Module::first();

        $this->postStore(array(
            'id' => $module->id,
            'name' => 'Tagged module',
            'sku' => 'TAGGED-SKU',
            'short_description' => 'Short',
            'category_id' => 1,
            'tags' => 'gamma'
        ));

        $this->assertRedirectedTo('/admin/modules');
        $this->assertSame(array('gamma'), $this->tagNames(Module::find($module->id)));
    }

    /**
     * Test (Pass): an empty tags string clears the old tags and still saves the module
     */
    public function testCharacterizationEmptyTagsStringClearsOldTags()
    {
        $this->postStore(array(
            'name' => 'Tagged module',
            'sku' => 'TAGGED-SKU',
            'short_description' => 'Short',
            'category_id' => 1,
            'tags' => 'alpha'
        ));

        $module = Module::first();

        $this->postStore(array(
            'id' => $module->id,
            'name' => 'Tagged module updated',
            'sku' => 'TAGGED-SKU',
            'short_description' => 'Short',
            'category_id' => 1,
            'tags' => ''
        ));

        $this->assertRedirectedTo('/admin/modules');
        $this->assertSame('Tagged module updated', Module::find($module->id)->name);
        $this->assertSame(array(), $this->tagNames(Module::find($module->id)));
    }

    /**
     * Test (Pass): a tags value of "0" is empty() so it clears the old tags
     */
    public function testCharacterizationZeroTagsStringClearsOldTags()
    {
        $this->postStore(array(
            'name' => 'Tagged module',
            'sku' => 'TAGGED-SKU',
            'short_description' => 'Short',
            'category_id' => 1,
            'tags' => 'alpha'
        ));

        $module = Module::first();

        $this->postStore(array(
            'id' => $module->id,
            'name' => 'Tagged module updated',
            'sku' => 'TAGGED-SKU',
            'short_description' => 'Short',
            'category_id' => 1,
            'tags' => '0'
        ));

        $this->assertRedirectedTo('/admin/modules');
        $this->assertSame('Tagged module updated', Module::find($module->id)->name);
        $this->assertSame(array(), $this->tagNames(Module::find($module->id)));
    }

    /**
     * Test (Fail): a tags array fails the regex rule, saves nothing and keeps the old tags
     */
    public function testCharacterizationArrayTagsFailsValidation()
    {
        $this->postStore(array(
            'name' => 'Tagged module',
            'sku' => 'TAGGED-SKU',
            'short_description' => 'Short',
            'category_id' => 1,
            'tags' => 'alpha'
        ));

        $module = Module::first();

        $this->assertValidationFailure(
            '/admin/modules/edit/' . $module->id,
            array(
                'id' => $module->id,
                'name' => 'Tagged module updated',
                'sku' => 'TAGGED-SKU',
                'short_description' => 'Short',
                'category_id' => 1,
                'tags' => array()
            ),
            array('tags' => 'The tags field format is invalid.')
        );

        $this->assertSame('Tagged module', Module::find($module->id)->name);
        $this->assertSame(array('alpha'), $this->tagNames(Module::find($module->id)));
    }

    /**
     * Test (Pass): no tags input keeps the old tags
     */
    public function testCharacterizationNoTagsInputKeepsOldTags()
    {
        $this->postStore(array(
            'name' => 'Tagged module',
            'sku' => 'TAGGED-SKU',
            'short_description' => 'Short',
            'category_id' => 1,
            'tags' => 'alpha'
        ));

        $module = Module::first();

        $this->postStore(array(
            'id' => $module->id,
            'name' => 'Tagged module updated',
            'sku' => 'TAGGED-SKU',
            'short_description' => 'Short',
            'category_id' => 1
        ));

        $this->assertRedirectedTo('/admin/modules');
        $this->assertSame('Tagged module updated', Module::find($module->id)->name);
        $this->assertSame(array('alpha'), $this->tagNames(Module::find($module->id)));
    }

    /**
     * Test (Pass): getEdit passes module, translated, categories, tagString, pricelists and imagine
     */
    public function testCharacterizationGetEditViewData()
    {
        $membership = new Membership;
        $membership->name = 'Gold';
        $membership->rank = 1;
        $membership->save();

        $module = $this->createModel(Module::class, array(
            'name' => 'View module',
            'sku' => 'VIEW-SKU',
            'short_description' => 'View short',
            'category_id' => 1
        ));
        $pricelist = new Pricelist;
        $pricelist->module_id = $module->id;
        $pricelist->membership_id = $membership->id;
        $pricelist->price = 12.34;
        $pricelist->active = true;
        $pricelist->save();

        $translation = new Translation;
        $translation->lang = 'cn';
        $translation->content = json_encode(array(
            'name' => 'View CN name',
            'short_description' => 'View CN short',
            'long_description' => 'View CN long'
        ));
        $module->translations()->save($translation);
        Tag::addTag($module, 'alpha');
        Tag::addTag($module, 'beta');

        $this->call('GET', '/admin/modules/edit/' . $module->id);

        $this->assertResponseOk();
        $this->assertViewHas('module', function ($value) use ($module) {
            return $value instanceof Module && $value->id == $module->id;
        });
        $this->assertViewHas('translated', function ($value) {
            return isset($value['cn']) && $value['cn']->name === 'View CN name';
        });
        $this->assertViewHas('categories');
        $this->assertViewHas('tagString', 'alpha,beta');
        $this->assertViewHas('pricelists', function ($value) use ($membership) {
            return isset($value[0])
                && $value[0]['id'] == $membership->id
                && $value[0]['name'] === 'Gold'
                && (float) $value[0]['price'] === 12.34
                && (bool) $value[0]['active'] === true;
        });
        $this->assertViewHas('imagine');
    }

    /**
     * Test (Fail): getEdit for a nonexistent id uses the exact not-found message
     */
    public function testCharacterizationGetEditMissing()
    {
        $this->call('GET', '/admin/modules/edit/999');

        $this->assertRedirectedTo('/admin/modules');
        $this->assertErrorMessage(
            'editError',
            'The module cannot be found because it does not exist or may have been deleted.'
        );
    }

    /**
     * Test (Fail): an image owned by another model cannot be removed through modules
     */
    public function testCharacterizationImgremoveWrongOwner()
    {
        $category = $this->createModel(Category::class, array(
            'name' => 'Module image owner ' . uniqid(),
            'short_description' => 'Short description',
            'order' => 1
        ));
        $image = $this->attachImage($category);

        $this->call('POST', '/admin/modules/imgremove/' . $image->id);

        $this->assertRedirectedTo('/admin/modules');
        $this->assertErrorMessage('deleteError', 'The image cannot be deleted at this time.');
        $this->assertNotNull(Image::find($image->id));
    }

    /**
     * Test (Fail): a nonexistent image id gives the deleteError message
     */
    public function testCharacterizationImgremoveMissing()
    {
        $this->call('POST', '/admin/modules/imgremove/999999');

        $this->assertRedirectedTo('/admin/modules');
        $this->assertErrorMessage('deleteError', 'The image cannot be deleted at this time.');
    }

    /**
     * Test (Pass): two files in images[] attach two Image rows
     */
    public function testCharacterizationImagesArrayAttachesTwoRows()
    {
        $this->postStore(
            array('name' => 'Image module', 'sku' => 'IMAGE-SKU', 'short_description' => 'Short', 'category_id' => 1),
            array('images' => array(
                $this->uploadedImage('first.png'),
                $this->uploadedImage('second.png')
            ))
        );

        $this->assertRedirectedTo('/admin/modules');
        $this->assertSame(1, Module::count());
        $this->assertSame(2, Image::count());

        $module = Module::first();
        $this->assertSame(2, $module->images()->count());
        $this->assertSame(
            array(1, 2),
            array_map('intval', Image::orderBy('id')->pluck('sort_order')->all())
        );
        $this->assertSame(Module::class, Image::first()->imageable_type);
        $this->assertSame($module->id, Image::first()->imageable_id);
    }

    /**
     * Test (Pass): the legacy single image field attaches one Image row
     */
    public function testCharacterizationLegacySingleImageAttachesOneRow()
    {
        $this->postStore(
            array('name' => 'Legacy module', 'sku' => 'LEGACY-SKU', 'short_description' => 'Short', 'category_id' => 1),
            array('image' => $this->uploadedImage('legacy.png'))
        );

        $this->assertRedirectedTo('/admin/modules');
        $this->assertSame(1, Module::count());
        $this->assertSame(1, Image::count());
        $this->assertSame(Module::class, Image::first()->imageable_type);
    }

    /**
     * Test (Fail): a validation failure creates no Image rows
     */
    public function testCharacterizationValidationFailureCreatesNoImages()
    {
        $this->assertValidationFailure(
            '/admin/modules/create',
            array('short_description' => 'Short', 'sku' => 'VALID-SKU', 'category_id' => 1),
            array('name' => 'The name field is required.'),
            array('images' => array($this->uploadedImage('not-saved.png')))
        );

        $this->assertSame(0, Module::count());
        $this->assertSame(0, Image::count());
    }

    /**
     * Test (Pass): membership prices are saved on create and updated in place
     */
    public function testCharacterizationMembershipPricingSavedAndUpdated()
    {
        $gold = new Membership;
        $gold->name = 'Gold';
        $gold->rank = 1;
        $gold->save();

        $silver = new Membership;
        $silver->name = 'Silver';
        $silver->rank = 2;
        $silver->save();

        $this->postStore(array(
            'name' => 'Priced module',
            'sku' => 'PRICED-SKU',
            'short_description' => 'Short',
            'category_id' => 1,
            'price_' . $gold->id => '99.99',
            'price_active_' . $gold->id => '1',
            'price_' . $silver->id => '49.50',
            'price_active_' . $silver->id => ''
        ));

        $this->assertRedirectedTo('/admin/modules');
        $module = Module::first();
        $this->assertSame(2, Pricelist::where('module_id', $module->id)->count());

        $goldPrice = Pricelist::where('module_id', $module->id)->where('membership_id', $gold->id)->first();
        $silverPrice = Pricelist::where('module_id', $module->id)->where('membership_id', $silver->id)->first();
        $this->assertSame(99.99, (float) $goldPrice->price);
        $this->assertSame(1, (int) $goldPrice->active);
        $this->assertSame(49.50, (float) $silverPrice->price);
        $this->assertSame(0, (int) $silverPrice->active);

        $this->postStore(array(
            'id' => $module->id,
            'name' => 'Priced module',
            'sku' => 'PRICED-SKU',
            'short_description' => 'Short',
            'category_id' => 1,
            'price_' . $gold->id => '88.88',
            'price_active_' . $gold->id => '',
            'price_' . $silver->id => '39.40',
            'price_active_' . $silver->id => '1'
        ));

        $this->assertRedirectedTo('/admin/modules');
        $this->assertSame(2, Pricelist::where('module_id', $module->id)->count());
        $goldPrice = Pricelist::where('module_id', $module->id)->where('membership_id', $gold->id)->first();
        $silverPrice = Pricelist::where('module_id', $module->id)->where('membership_id', $silver->id)->first();
        $this->assertSame(88.88, (float) $goldPrice->price);
        $this->assertSame(0, (int) $goldPrice->active);
        $this->assertSame(39.40, (float) $silverPrice->price);
        $this->assertSame(1, (int) $silverPrice->active);
    }

    /**
     * Test (Pass): an empty price already referenced by order_pricelist cannot be removed
     */
    public function testCharacterizationPriceInUseCannotBeRemoved()
    {
        $membership = new Membership;
        $membership->name = 'Gold';
        $membership->rank = 1;
        $membership->save();

        $this->postStore(array(
            'name' => 'Used price module',
            'sku' => 'USED-PRICE-SKU',
            'short_description' => 'Short',
            'category_id' => 1,
            'price_' . $membership->id => '99.99',
            'price_active_' . $membership->id => '1'
        ));

        $module = Module::first();
        $pricelist = Pricelist::where('module_id', $module->id)
            ->where('membership_id', $membership->id)
            ->first();

        $orderId = DB::table('orders')->insertGetId(array('user_id' => 1));
        DB::table('order_pricelist')->insert(array(
            'order_id' => $orderId,
            'pricelist_id' => $pricelist->id
        ));

        $this->postStore(array(
            'id' => $module->id,
            'name' => 'Used price module',
            'sku' => 'USED-PRICE-SKU',
            'short_description' => 'Short',
            'category_id' => 1
        ));

        $this->assertRedirectedTo('/admin/modules');
        $pricelist = Pricelist::find($pricelist->id);
        $this->assertNotNull($pricelist);
        $this->assertSame(99.99, (float) $pricelist->price);
    }

    /**
     * Test (Pass): update replaces the attached media-membership rows instead of appending
     */
    public function testCharacterizationUpdateReplacesMediaMemberships()
    {
        $membership = new Membership;
        $membership->name = 'Gold';
        $membership->rank = 1;
        $membership->save();

        $first = $this->createModel(Media::class, array(
            'name' => 'First media',
            'path' => 'broken.pdf',
            'sku' => 'FIRST-MEDIA',
            'short_description' => 'First short',
            'category_id' => 1
        ));
        $second = $this->createModel(Media::class, array(
            'name' => 'Second media',
            'path' => 'broken.pdf',
            'sku' => 'SECOND-MEDIA',
            'short_description' => 'Second short',
            'category_id' => 1
        ));

        $this->postStore(array(
            'name' => 'Media module',
            'sku' => 'MEDIA-MODULE-SKU',
            'short_description' => 'Short',
            'category_id' => 1,
            'price_' . $membership->id => '10.00',
            'media_checkbox' => array($first->id . '_' . $membership->id)
        ));

        $module = Module::first();
        $this->assertSame(1, ModuleMediaMembership::where('module_id', $module->id)->count());
        $this->assertSame($first->id, (int) ModuleMediaMembership::where('module_id', $module->id)->first()->media_id);

        $this->postStore(array(
            'id' => $module->id,
            'name' => 'Media module',
            'sku' => 'MEDIA-MODULE-SKU',
            'short_description' => 'Short',
            'category_id' => 1,
            'price_' . $membership->id => '10.00',
            'media_checkbox' => array($second->id . '_' . $membership->id)
        ));

        $this->assertRedirectedTo('/admin/modules');
        $this->assertSame(1, ModuleMediaMembership::where('module_id', $module->id)->count());
        $this->assertSame($second->id, (int) ModuleMediaMembership::where('module_id', $module->id)->first()->media_id);
    }

    /**
     * Test (Pass): getMedias passes the medias of the category and memberships
     */
    public function testCharacterizationGetMediasViewData()
    {
        $category = $this->createModel(Category::class, array(
            'name' => 'Media category',
            'short_description' => 'Short',
            'order' => 1
        ));
        $media = $this->createModel(Media::class, array(
            'name' => 'Category media',
            'path' => 'broken.pdf',
            'sku' => 'CATEGORY-MEDIA',
            'short_description' => 'Short',
            'category_id' => $category->id
        ));

        $this->call('GET', '/admin/modules/medias/' . $category->id);

        $this->assertResponseOk();
        $this->assertViewHas('medias', function ($value) use ($media) {
            return $value->count() === 1 && (int) $value->first()->id === (int) $media->id;
        });
        $this->assertViewHas('memberships');
    }

    /**
     * Test (Pass): getEditmedias also passes the module's media-membership map
     */
    public function testCharacterizationGetEditmediasViewData()
    {
        $category = $this->createModel(Category::class, array(
            'name' => 'Media category',
            'short_description' => 'Short',
            'order' => 1
        ));
        $module = $this->createModel(Module::class, array(
            'name' => 'Edit medias module',
            'sku' => 'EDIT-MEDIAS-SKU',
            'short_description' => 'Short',
            'category_id' => $category->id
        ));
        $media = $this->createModel(Media::class, array(
            'name' => 'Category media',
            'path' => 'broken.pdf',
            'sku' => 'CATEGORY-MEDIA',
            'short_description' => 'Short',
            'category_id' => $category->id
        ));
        $membership = new Membership;
        $membership->name = 'Gold';
        $membership->rank = 1;
        $membership->save();
        $mmm = new ModuleMediaMembership;
        $mmm->module_id = $module->id;
        $mmm->media_id = $media->id;
        $mmm->membership_id = $membership->id;
        $mmm->save();

        $this->call('GET', '/admin/modules/editmedias/' . $category->id . '/' . $module->id);

        $this->assertResponseOk();
        $this->assertViewHas('medias', function ($value) {
            return $value->count() === 1;
        });
        $this->assertViewHas('memberships');
        $this->assertViewHas('modMediaMembership', function ($value) use ($media, $membership) {
            return isset($value[$media->id][$membership->id]) && $value[$media->id][$membership->id] === true;
        });
    }

    /**
     * Test (Pass): getDelete removes the module and its pricelist and media-membership rows
     */
    public function testCharacterizationGetDelete()
    {
        $module = $this->createModel(Module::class, array(
            'name' => 'Deletable module',
            'sku' => 'DELETABLE-SKU',
            'short_description' => 'Short',
            'category_id' => 1
        ));
        $membership = new Membership;
        $membership->name = 'Gold';
        $membership->rank = 1;
        $membership->save();
        $pricelist = new Pricelist;
        $pricelist->module_id = $module->id;
        $pricelist->membership_id = $membership->id;
        $pricelist->price = 10.00;
        $pricelist->save();
        $media = $this->createModel(Media::class, array(
            'name' => 'Delete media',
            'path' => 'broken.pdf',
            'sku' => 'DELETE-MEDIA',
            'short_description' => 'Short',
            'category_id' => 1
        ));
        $mmm = new ModuleMediaMembership;
        $mmm->module_id = $module->id;
        $mmm->media_id = $media->id;
        $mmm->membership_id = $membership->id;
        $mmm->save();
        Tag::addTag($module, 'alpha');

        $this->call('POST', '/admin/modules/delete/' . $module->id, array(), array(), array(), array(
            'HTTP_REFERER' => '/admin/modules'
        ));

        $this->assertRedirectedTo('/admin/modules');
        $this->assertSame(0, Module::count());
        $this->assertSame(0, Pricelist::count());
        $this->assertSame(0, ModuleMediaMembership::count());
    }

    /**
     * Test (Fail): getDelete for a missing module uses the deleteError message
     */
    public function testCharacterizationGetDeleteMissing()
    {
        $this->call('POST', '/admin/modules/delete/999', array(), array(), array(), array(
            'HTTP_REFERER' => '/admin/modules'
        ));

        $this->assertRedirectedTo('/admin/modules');
        $this->assertErrorMessage('deleteError', 'We are having problem deleting this entry. Please try again.');
        $this->assertSame(0, Module::count());
    }
}
