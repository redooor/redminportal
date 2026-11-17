<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Category;
use Redooor\Redminportal\App\Models\Image;
use Redooor\Redminportal\App\Models\Media;
use Redooor\Redminportal\App\Models\Tag;
use Redooor\Redminportal\App\Models\Translation;

/**
 * Pins the behaviour before the controller refactor; do not edit to make a refactor pass.
 */
class MediaCharacterizationTest extends BaseControllerTest
{
    use CharacterizationSupport;

    private $mediaFolders = array();

    public function setUp(): void
    {
        parent::setUp();

        $this->page = '/admin/medias';
        $this->viewhas = array(
            'singular' => 'media',
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

        $this->mediaFolders = array();

        $this->configureImageUploads();
    }

    public function tearDown(): void
    {
        $this->cleanupImageUploads();

        foreach ($this->mediaFolders as $folder) {
            $this->deleteFolder($folder);
            @rmdir(dirname($folder));
            @rmdir(dirname(dirname($folder)));
            @rmdir(dirname(dirname(dirname($folder))));
        }

        $this->mediaFolders = array();

        parent::tearDown();
    }

    /**
     * Test (Pass): create then update keeps one row and saves the main fields
     */
    public function testCharacterizationCreateThenUpdate()
    {
        $this->postStore(array(
            'name' => 'Original media',
            'sku' => 'ORIGINAL-SKU',
            'short_description' => 'Original short',
            'long_description' => 'Original long',
            'category_id' => '1',
            'featured' => '1',
            'active' => '1'
        ));

        $this->assertRedirectedTo('/admin/medias');
        $this->assertSame(1, Media::count());

        $media = Media::first();
        $this->assertSame('Original media', $media->name);
        $this->assertSame('ORIGINAL-SKU', $media->sku);
        $this->assertSame('Original short', $media->short_description);
        $this->assertSame('Original long', $media->long_description);
        $this->assertSame(1, (int) $media->category_id);
        $this->assertSame(1, (int) $media->featured);
        $this->assertSame(1, (int) $media->active);
        $this->assertSame('broken.pdf', $media->path);

        $this->postStore(array(
            'id' => $media->id,
            'name' => 'Updated media',
            'sku' => 'UPDATED-SKU',
            'short_description' => 'Updated short',
            'long_description' => 'Updated long',
            'category_id' => '1',
            'featured' => '',
            'active' => ''
        ));

        $this->assertRedirectedTo('/admin/medias');
        $this->assertSame(1, Media::count());

        $media = Media::first();
        $this->assertSame('Updated media', $media->name);
        $this->assertSame('UPDATED-SKU', $media->sku);
        $this->assertSame('Updated short', $media->short_description);
        $this->assertSame('Updated long', $media->long_description);
        $this->assertSame(0, (int) $media->featured);
        $this->assertSame(0, (int) $media->active);
    }

    /**
     * Test (Fail): invalid input with no id redirects to create
     */
    public function testCharacterizationValidationRedirectWhenIdOmitted()
    {
        $this->assertValidationFailure(
            '/admin/medias/create',
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
            '/admin/medias/edit/5',
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
            '/admin/medias/create',
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
            '/admin/medias/edit/0',
            array('id' => '0', 'short_description' => 'Short', 'sku' => 'VALID-SKU', 'category_id' => 1),
            array('name' => 'The name field is required.')
        );
    }

    /**
     * Test (Fail): invalid input with an existing id redirects to edit/<id>
     */
    public function testCharacterizationValidationRedirectWhenIdExists()
    {
        $media = $this->createModel(Media::class, array(
            'name' => 'Existing media',
            'path' => 'broken.pdf',
            'sku' => 'EXISTING-SKU',
            'short_description' => 'Existing short',
            'category_id' => 1
        ));

        $this->assertValidationFailure(
            '/admin/medias/edit/' . $media->id,
            array('id' => $media->id, 'short_description' => 'Short', 'sku' => 'VALID-SKU', 'category_id' => 1),
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
            'name' => 'Valid media',
            'sku' => 'VALID-SKU',
            'short_description' => 'Valid short',
            'category_id' => 1
        ));

        $this->assertRedirectedTo('/admin/medias');
        $this->assertErrorMessage(
            'editError',
            'The media cannot be found because it does not exist or may have been deleted.'
        );
        $this->assertSame(0, Media::count());
    }

    /**
     * Test (Fail): name is required
     */
    public function testCharacterizationMissingName()
    {
        $this->assertValidationFailure(
            '/admin/medias/create',
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
            '/admin/medias/create',
            array('name' => 'Valid media', 'sku' => 'VALID-SKU', 'category_id' => 1),
            array('short_description' => 'The short description field is required.')
        );
    }

    /**
     * Test (Fail): sku is required
     */
    public function testCharacterizationMissingSku()
    {
        $this->assertValidationFailure(
            '/admin/medias/create',
            array('name' => 'Valid media', 'short_description' => 'Short', 'category_id' => 1),
            array('sku' => 'The sku field is required.')
        );
    }

    /**
     * Test (Fail): category_id is required
     */
    public function testCharacterizationMissingCategoryId()
    {
        $this->assertValidationFailure(
            '/admin/medias/create',
            array('name' => 'Valid media', 'short_description' => 'Short', 'sku' => 'VALID-SKU'),
            array('category_id' => 'The category id field is required.')
        );
    }

    /**
     * Test (Fail): category_id min:1 uses the custom "The category field is required." message
     */
    public function testCharacterizationCategoryIdZero()
    {
        $this->assertValidationFailure(
            '/admin/medias/create',
            array('name' => 'Valid media', 'short_description' => 'Short', 'sku' => 'VALID-SKU', 'category_id' => '0'),
            array('category_id' => 'The category field is required.')
        );
    }

    /**
     * Test (Fail): category_id must be numeric
     */
    public function testCharacterizationCategoryIdNonNumeric()
    {
        $this->assertValidationFailure(
            '/admin/medias/create',
            array('name' => 'Valid media', 'short_description' => 'Short', 'sku' => 'VALID-SKU', 'category_id' => 'abc'),
            array('category_id' => 'The category id field must be a number.')
        );
    }

    /**
     * Test (Fail): sku has an alpha_dash rule
     */
    public function testCharacterizationInvalidSkuFormat()
    {
        $this->assertValidationFailure(
            '/admin/medias/create',
            array('name' => 'Valid media', 'short_description' => 'Short', 'sku' => 'bad sku!', 'category_id' => 1),
            array('sku' => 'The sku field must only contain letters, numbers, dashes, and underscores.')
        );
    }

    /**
     * Test (Fail): tags has a regex rule
     */
    public function testCharacterizationInvalidTagsFormat()
    {
        $this->assertValidationFailure(
            '/admin/medias/create',
            array('name' => 'Valid media', 'short_description' => 'Short', 'sku' => 'VALID-SKU', 'category_id' => 1, 'tags' => 'a!b'),
            array('tags' => 'The tags field format is invalid.')
        );
    }

    /**
     * Test (Fail): old input keeps scalar values but never an uploaded file
     */
    public function testCharacterizationOldInputWithFile()
    {
        $this->assertValidationFailure(
            '/admin/medias/create',
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
        $this->assertRedirectedTo('/admin/medias/create');
        $this->assertSessionHasErrors('name');
        $this->assertErrorMessage('name', 'The name field is required.');
        $this->assertErrorBagIsDefaultOnly();
    }

    /**
     * Test (Pass): an unchanged name on update passes unique and another row's name fails
     */
    public function testCharacterizationUniqueNameOnUpdate()
    {
        $first = $this->createModel(Media::class, array(
            'name' => 'First media',
            'path' => 'broken.pdf',
            'sku' => 'FIRST-SKU',
            'short_description' => 'First short',
            'category_id' => 1
        ));
        $this->createModel(Media::class, array(
            'name' => 'Second media',
            'path' => 'broken.pdf',
            'sku' => 'SECOND-SKU',
            'short_description' => 'Second short',
            'category_id' => 1
        ));

        $this->postStore(array(
            'id' => $first->id,
            'name' => 'First media',
            'sku' => 'FIRST-SKU',
            'short_description' => 'Changed short',
            'category_id' => 1
        ));

        $this->assertRedirectedTo('/admin/medias');
        $this->assertSame('Changed short', Media::find($first->id)->short_description);

        $this->postStore(array(
            'id' => $first->id,
            'name' => 'Second media',
            'sku' => 'FIRST-SKU',
            'short_description' => 'Changed again',
            'category_id' => 1
        ));

        $this->assertRedirectedTo('/admin/medias/edit/' . $first->id);
        $this->assertErrorMessage('name', 'The name has already been taken.');
        $this->assertSame('Changed short', Media::find($first->id)->short_description);
    }

    /**
     * Test (Pass): an unchanged sku on update passes unique and another row's sku fails
     */
    public function testCharacterizationUniqueSkuOnUpdate()
    {
        $first = $this->createModel(Media::class, array(
            'name' => 'First media',
            'path' => 'broken.pdf',
            'sku' => 'FIRST-SKU',
            'short_description' => 'First short',
            'category_id' => 1
        ));
        $this->createModel(Media::class, array(
            'name' => 'Second media',
            'path' => 'broken.pdf',
            'sku' => 'SECOND-SKU',
            'short_description' => 'Second short',
            'category_id' => 1
        ));

        $this->postStore(array(
            'id' => $first->id,
            'name' => 'First media',
            'sku' => 'FIRST-SKU',
            'short_description' => 'Changed short',
            'category_id' => 1
        ));

        $this->assertRedirectedTo('/admin/medias');
        $this->assertSame('Changed short', Media::find($first->id)->short_description);

        $this->postStore(array(
            'id' => $first->id,
            'name' => 'First media',
            'sku' => 'SECOND-SKU',
            'short_description' => 'Changed again',
            'category_id' => 1
        ));

        $this->assertRedirectedTo('/admin/medias/edit/' . $first->id);
        $this->assertErrorMessage('sku', 'The sku has already been taken.');
        $this->assertSame('Changed short', Media::find($first->id)->short_description);
    }

    /**
     * Test (Pass): create writes one cn translation row with the exact decoded content
     */
    public function testCharacterizationTranslationCreated()
    {
        $this->postStore(array(
            'name' => 'Translated media',
            'sku' => 'TRANSLATED-SKU',
            'short_description' => 'Translated short',
            'long_description' => 'Translated long',
            'category_id' => 1,
            'cn_name' => 'CN name',
            'cn_short_description' => 'CN short',
            'cn_long_description' => 'CN long'
        ));

        $this->assertRedirectedTo('/admin/medias');

        $this->assertTranslations(Media::first(), array(
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

        $media = Media::first();
        $translation = $media->translations()->first();
        $translation->content = json_encode(array(
            'name' => 'pre-existing',
            'short_description' => 'pre-existing short',
            'long_description' => 'pre-existing long'
        ));
        $translation->save();

        $this->postStore(array(
            'id' => $media->id,
            'name' => 'Translation owner',
            'sku' => 'TRANSLATION-SKU',
            'short_description' => 'Translated short',
            'long_description' => 'Translated long',
            'category_id' => 1,
            'cn_name' => 'Updated CN name',
            'cn_short_description' => 'Updated CN short',
            'cn_long_description' => 'Updated CN long'
        ));

        $this->assertRedirectedTo('/admin/medias');
        $this->assertSame(1, Media::count());

        $translations = $this->assertTranslations(Media::find($media->id), array(
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

        $this->assertRedirectedTo('/admin/medias');

        $this->assertTranslations(Media::first(), array(
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
            'name' => 'Tagged media',
            'sku' => 'TAGGED-SKU',
            'short_description' => 'Short',
            'category_id' => 1,
            'tags' => 'alpha,beta'
        ));

        $this->assertRedirectedTo('/admin/medias');

        $this->assertSame(array('alpha', 'beta'), $this->tagNames(Media::first()));
    }

    /**
     * Test (Pass): a second update with new tags replaces the old ones
     */
    public function testCharacterizationSecondUpdateWithNewTagsReplacesOldTags()
    {
        $this->postStore(array(
            'name' => 'Tagged media',
            'sku' => 'TAGGED-SKU',
            'short_description' => 'Short',
            'category_id' => 1,
            'tags' => 'alpha,beta'
        ));

        $media = Media::first();

        $this->postStore(array(
            'id' => $media->id,
            'name' => 'Tagged media',
            'sku' => 'TAGGED-SKU',
            'short_description' => 'Short',
            'category_id' => 1,
            'tags' => 'gamma'
        ));

        $this->assertRedirectedTo('/admin/medias');
        $this->assertSame(array('gamma'), $this->tagNames(Media::find($media->id)));
    }

    /**
     * Test (Pass): an empty tags string clears the old tags and still saves the media
     */
    public function testCharacterizationEmptyTagsStringClearsOldTags()
    {
        $this->postStore(array(
            'name' => 'Tagged media',
            'sku' => 'TAGGED-SKU',
            'short_description' => 'Short',
            'category_id' => 1,
            'tags' => 'alpha'
        ));

        $media = Media::first();

        $this->postStore(array(
            'id' => $media->id,
            'name' => 'Tagged media updated',
            'sku' => 'TAGGED-SKU',
            'short_description' => 'Short',
            'category_id' => 1,
            'tags' => ''
        ));

        $this->assertRedirectedTo('/admin/medias');
        $this->assertSame('Tagged media updated', Media::find($media->id)->name);
        $this->assertSame(array(), $this->tagNames(Media::find($media->id)));
    }

    /**
     * Test (Pass): a tags value of "0" is empty() so it clears the old tags
     */
    public function testCharacterizationZeroTagsStringClearsOldTags()
    {
        $this->postStore(array(
            'name' => 'Tagged media',
            'sku' => 'TAGGED-SKU',
            'short_description' => 'Short',
            'category_id' => 1,
            'tags' => 'alpha'
        ));

        $media = Media::first();

        $this->postStore(array(
            'id' => $media->id,
            'name' => 'Tagged media updated',
            'sku' => 'TAGGED-SKU',
            'short_description' => 'Short',
            'category_id' => 1,
            'tags' => '0'
        ));

        $this->assertRedirectedTo('/admin/medias');
        $this->assertSame('Tagged media updated', Media::find($media->id)->name);
        $this->assertSame(array(), $this->tagNames(Media::find($media->id)));
    }

    /**
     * Test (Fail): a tags array fails the regex rule, saves nothing and keeps the old tags
     */
    public function testCharacterizationArrayTagsFailsValidation()
    {
        $this->postStore(array(
            'name' => 'Tagged media',
            'sku' => 'TAGGED-SKU',
            'short_description' => 'Short',
            'category_id' => 1,
            'tags' => 'alpha'
        ));

        $media = Media::first();

        $this->assertValidationFailure(
            '/admin/medias/edit/' . $media->id,
            array(
                'id' => $media->id,
                'name' => 'Tagged media updated',
                'sku' => 'TAGGED-SKU',
                'short_description' => 'Short',
                'category_id' => 1,
                'tags' => array()
            ),
            array('tags' => 'The tags field format is invalid.')
        );

        $this->assertSame('Tagged media', Media::find($media->id)->name);
        $this->assertSame(array('alpha'), $this->tagNames(Media::find($media->id)));
    }

    /**
     * Test (Pass): no tags input keeps the old tags
     */
    public function testCharacterizationNoTagsInputKeepsOldTags()
    {
        $this->postStore(array(
            'name' => 'Tagged media',
            'sku' => 'TAGGED-SKU',
            'short_description' => 'Short',
            'category_id' => 1,
            'tags' => 'alpha'
        ));

        $media = Media::first();

        $this->postStore(array(
            'id' => $media->id,
            'name' => 'Tagged media updated',
            'sku' => 'TAGGED-SKU',
            'short_description' => 'Short',
            'category_id' => 1
        ));

        $this->assertRedirectedTo('/admin/medias');
        $this->assertSame('Tagged media updated', Media::find($media->id)->name);
        $this->assertSame(array('alpha'), $this->tagNames(Media::find($media->id)));
    }

    /**
     * Test (Pass): getEdit passes media, translated, categories, tagString and imagine
     */
    public function testCharacterizationGetEditViewData()
    {
        $media = $this->createModel(Media::class, array(
            'name' => 'View media',
            'path' => 'broken.pdf',
            'sku' => 'VIEW-SKU',
            'short_description' => 'View short',
            'category_id' => 1
        ));
        $translation = new Translation;
        $translation->lang = 'cn';
        $translation->content = json_encode(array(
            'name' => 'View CN name',
            'short_description' => 'View CN short',
            'long_description' => 'View CN long'
        ));
        $media->translations()->save($translation);
        Tag::addTag($media, 'alpha');
        Tag::addTag($media, 'beta');

        $this->call('GET', '/admin/medias/edit/' . $media->id);

        $this->assertResponseOk();
        $this->assertViewHas('media', function ($value) use ($media) {
            return $value instanceof Media && $value->id == $media->id;
        });
        $this->assertViewHas('translated', function ($value) {
            return isset($value['cn']) && $value['cn']->name === 'View CN name';
        });
        $this->assertViewHas('categories');
        $this->assertViewHas('tagString', 'alpha,beta');
        $this->assertViewHas('imagine');
    }

    /**
     * Test (Fail): getEdit for a nonexistent id uses the exact not-found message
     */
    public function testCharacterizationGetEditMissing()
    {
        $this->call('GET', '/admin/medias/edit/999');

        $this->assertRedirectedTo('/admin/medias');
        $this->assertErrorMessage(
            'editError',
            'The media cannot be found because it does not exist or may have been deleted.'
        );
    }

    /**
     * Test (Fail): an image owned by another model cannot be removed through medias
     */
    public function testCharacterizationImgremoveWrongOwner()
    {
        $category = $this->createModel(Category::class, array(
            'name' => 'Media image owner ' . uniqid(),
            'short_description' => 'Short description',
            'order' => 1
        ));
        $image = $this->attachImage($category);

        $this->call('POST', '/admin/medias/imgremove/' . $image->id);

        $this->assertRedirectedTo('/admin/medias');
        $this->assertErrorMessage('deleteError', 'The image cannot be deleted at this time.');
        $this->assertNotNull(Image::find($image->id));
    }

    /**
     * Test (Fail): a nonexistent image id gives the deleteError message
     */
    public function testCharacterizationImgremoveMissing()
    {
        $this->call('POST', '/admin/medias/imgremove/999999');

        $this->assertRedirectedTo('/admin/medias');
        $this->assertErrorMessage('deleteError', 'The image cannot be deleted at this time.');
    }

    /**
     * Test (Pass): two files in images[] attach two Image rows
     */
    public function testCharacterizationImagesArrayAttachesTwoRows()
    {
        $this->postStore(
            array('name' => 'Image media', 'sku' => 'IMAGE-SKU', 'short_description' => 'Short', 'category_id' => 1),
            array('images' => array(
                $this->uploadedImage('first.png'),
                $this->uploadedImage('second.png')
            ))
        );

        $this->assertRedirectedTo('/admin/medias');
        $this->assertSame(1, Media::count());
        $this->assertSame(2, Image::count());

        $media = Media::first();
        $this->assertSame(2, $media->images()->count());
        $this->assertSame(
            array(1, 2),
            array_map('intval', Image::orderBy('id')->pluck('sort_order')->all())
        );
        $this->assertSame(Media::class, Image::first()->imageable_type);
        $this->assertSame($media->id, Image::first()->imageable_id);
    }

    /**
     * Test (Pass): the legacy single image field attaches one Image row
     */
    public function testCharacterizationLegacySingleImageAttachesOneRow()
    {
        $this->postStore(
            array('name' => 'Legacy media', 'sku' => 'LEGACY-SKU', 'short_description' => 'Short', 'category_id' => 1),
            array('image' => $this->uploadedImage('legacy.png'))
        );

        $this->assertRedirectedTo('/admin/medias');
        $this->assertSame(1, Media::count());
        $this->assertSame(1, Image::count());
        $this->assertSame(Media::class, Image::first()->imageable_type);
    }

    /**
     * Test (Fail): a validation failure creates no Image rows
     */
    public function testCharacterizationValidationFailureCreatesNoImages()
    {
        $this->assertValidationFailure(
            '/admin/medias/create',
            array('short_description' => 'Short', 'sku' => 'VALID-SKU', 'category_id' => 1),
            array('name' => 'The name field is required.'),
            array('images' => array($this->uploadedImage('not-saved.png')))
        );

        $this->assertSame(0, Media::count());
        $this->assertSame(0, Image::count());
    }

    /**
     * Test (Pass): changing category_id copies the media folder and deletes the old one
     */
    public function testCharacterizationCategoryChangeMovesMediaFolder()
    {
        $oldCategory = $this->createModel(Category::class, array(
            'name' => 'Old media category',
            'short_description' => 'Short',
            'order' => 1
        ));
        $newCategory = $this->createModel(Category::class, array(
            'name' => 'New media category',
            'short_description' => 'Short',
            'order' => 1
        ));
        $media = $this->createModel(Media::class, array(
            'name' => 'Moving media',
            'path' => 'track.mp3',
            'sku' => 'MOVING-SKU',
            'short_description' => 'Short',
            'category_id' => $oldCategory->id
        ));

        $oldFolder = $this->createStoredMedia($oldCategory->id, $media->id, 'track.mp3', 'audio-bytes');
        $newFolder = public_path() . '/assets/medias/' . $newCategory->id . '/' . $media->id;
        $this->mediaFolders[] = $newFolder;

        $this->postStore(array(
            'id' => $media->id,
            'name' => 'Moving media',
            'sku' => 'MOVING-SKU',
            'short_description' => 'Short',
            'category_id' => $newCategory->id
        ));

        $this->assertRedirectedTo('/admin/medias');
        $this->assertSame($newCategory->id, (int) Media::find($media->id)->category_id);
        $this->assertDirectoryDoesNotExist($oldFolder);
        $this->assertFileExists($newFolder . '/track.mp3');
        $this->assertSame('audio-bytes', file_get_contents($newFolder . '/track.mp3'));
    }

    /**
     * Test (Pass): getDelete removes the media row, its Image rows and its media folder
     */
    public function testCharacterizationGetDeleteRemovesFilesAndRows()
    {
        $category = $this->createModel(Category::class, array(
            'name' => 'Delete media category',
            'short_description' => 'Short',
            'order' => 1
        ));
        $media = $this->createModel(Media::class, array(
            'name' => 'Deletable media',
            'path' => 'delete.mp3',
            'sku' => 'DELETABLE-SKU',
            'short_description' => 'Short',
            'category_id' => $category->id
        ));
        $folder = $this->createStoredMedia($category->id, $media->id, 'delete.mp3', 'audio');
        $image = $this->attachImage($media);
        Tag::addTag($media, 'alpha');

        $this->call('POST', '/admin/medias/delete/' . $media->id, array(), array(), array(), array(
            'HTTP_REFERER' => '/admin/medias'
        ));

        $this->assertRedirectedTo('/admin/medias');
        $this->assertSame(0, Media::count());
        $this->assertSame(0, Image::count());
        $this->assertNull(Image::find($image->id));
        $this->assertDirectoryDoesNotExist($folder);
    }

    /**
     * Test (Fail): getDelete for a missing media uses the deleteError message
     */
    public function testCharacterizationGetDeleteMissing()
    {
        $this->call('POST', '/admin/medias/delete/999', array(), array(), array(), array(
            'HTTP_REFERER' => '/admin/medias'
        ));

        $this->assertRedirectedTo('/admin/medias');
        $this->assertErrorMessage('deleteError', 'We are having problem deleting this entry. Please try again.');
        $this->assertSame(0, Media::count());
    }

    /**
     * Test (Pass): getDuration reads an existing audio file and stores its playtime
     */
    public function testCharacterizationGetDuration()
    {
        $media = $this->createModel(Media::class, array(
            'name' => 'Duration media',
            'path' => 'duration.mp3',
            'sku' => 'DURATION-SKU',
            'short_description' => 'Short',
            'category_id' => 1,
            'mimetype' => 'audio/mpeg'
        ));
        $folder = $this->createStoredMedia(1, $media->id, 'duration.mp3', '');
        copy(__DIR__ . '/../../dummy/foo113audio.mp3', $folder . '/duration.mp3');

        $this->call('POST', '/admin/medias/duration/' . $media->id);

        $this->assertResponseOk();
        $status = json_decode($this->response->getContent(), true);
        $this->assertSame('success', $status['status']);
        $this->assertSame('0:07', $status['data']);

        $options = json_decode(Media::find($media->id)->options, true);
        $this->assertSame('0:07', $options['duration']);
    }

    /**
     * Test (Pass): getUploadform passes the model to the upload view
     */
    public function testCharacterizationGetUploadformViewData()
    {
        $media = $this->createModel(Media::class, array(
            'name' => 'Uploadform media',
            'path' => 'broken.pdf',
            'sku' => 'UPLOADFORM-SKU',
            'short_description' => 'Short',
            'category_id' => 1
        ));

        $this->call('GET', '/admin/medias/uploadform/' . $media->id);

        $this->assertResponseOk();
        $this->assertViewHas('model', function ($value) use ($media) {
            return $value instanceof Media && $value->id == $media->id;
        });
    }

    /**
     * Test (Fail): getUploadform for a missing media redirects to the index
     */
    public function testCharacterizationGetUploadformMissing()
    {
        $this->call('GET', '/admin/medias/uploadform/999');

        $this->assertRedirectedTo('/admin/medias');
    }

    /**
     * Test (Helper): create a media folder with one file and register it for cleanup
     */
    private function createStoredMedia($categoryId, $mediaId, $fileName, $contents)
    {
        $folder = public_path() . '/assets/medias/' . $categoryId . '/' . $mediaId;

        if (! is_dir($folder)) {
            mkdir($folder, 0777, true);
        }

        file_put_contents($folder . '/' . $fileName, $contents);
        $this->mediaFolders[] = $folder;

        return $folder;
    }

    /**
     * Test (Helper): recursively delete a folder created by this test
     */
    private function deleteFolder($folder)
    {
        if (! is_dir($folder)) {
            return;
        }

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($folder, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($items as $item) {
            if ($item->isDir()) {
                @rmdir($item->getPathname());
            } else {
                @unlink($item->getPathname());
            }
        }

        @rmdir($folder);
    }
}
