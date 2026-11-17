<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Category;
use Redooor\Redminportal\App\Models\Image;
use Redooor\Redminportal\App\Models\Page;
use Redooor\Redminportal\App\Models\Translation;

/**
 * Pins the behaviour before the controller refactor; do not edit to make a refactor pass.
 */
class PageCharacterizationTest extends BaseControllerTest
{
    use CharacterizationSupport;

    public function setUp(): void
    {
        parent::setUp();

        $this->page = '/admin/pages';
        $this->viewhas = array(
            'singular' => 'page',
            'plural' => 'models'
        );
        $this->input = array(
            'create' => array(
                'title' => 'This is the title',
                'slug' => 'this_is_a_slug',
                'content' => 'This is the body',
                'private' => false,
                'category_id' => 1,
                'cn_title' => 'This is cn title',
                'cn_slug' => 'This is cn slug',
                'cn_content' => 'This is cn content'
            ),
            'edit' => array(
                'id' => 1,
                'title' => 'This is the title',
                'slug' => 'this_is_a_slug',
                'content' => 'This is the body',
                'private' => false,
                'category_id' => 1,
                'cn_title' => 'This is cn title',
                'cn_slug' => 'This is cn slug',
                'cn_content' => 'This is cn content'
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
            'title' => 'Original title',
            'slug' => 'original slug here',
            'content' => 'Original body',
            'private' => '1',
            'category_id' => '1'
        ));

        $this->assertRedirectedTo('/admin/pages');
        $this->assertSame(1, Page::count());

        $page = Page::first();
        $this->assertSame('Original title', $page->title);
        $this->assertSame('original_slug_here', $page->slug);
        $this->assertSame('Original body', $page->content);
        $this->assertSame(1, (int) $page->private);
        $this->assertSame(1, (int) $page->category_id);

        $this->postStore(array(
            'id' => $page->id,
            'title' => 'Updated title',
            'slug' => 'updated slug here',
            'content' => 'Updated body',
            'private' => ''
        ));

        $this->assertRedirectedTo('/admin/pages');
        $this->assertSame(1, Page::count());

        $page = Page::first();
        $this->assertSame('Updated title', $page->title);
        $this->assertSame('updated_slug_here', $page->slug);
        $this->assertSame('Updated body', $page->content);
        $this->assertSame(0, (int) $page->private);
        $this->assertNull($page->category_id);
    }

    /**
     * Test (Fail): invalid input with no id redirects to create
     */
    public function testCharacterizationValidationRedirectWhenIdOmitted()
    {
        $this->assertValidationFailure(
            '/admin/pages/create',
            array('slug' => 'valid slug', 'content' => 'Body'),
            array('title' => 'The title field is required.')
        );
    }

    /**
     * Test (Fail): invalid input with a nonexistent id redirects to edit/5 before the not-found check
     */
    public function testCharacterizationValidationRedirectWhenIdIsFive()
    {
        $this->assertValidationFailure(
            '/admin/pages/edit/5',
            array('id' => '5', 'slug' => 'valid slug', 'content' => 'Body'),
            array('title' => 'The title field is required.')
        );
    }

    /**
     * Test (Fail): an empty id is converted to null by the HTTP middleware, so it redirects to create
     */
    public function testCharacterizationValidationRedirectWhenIdIsEmptyString()
    {
        $this->assertValidationFailure(
            '/admin/pages/create',
            array('id' => '', 'slug' => 'valid slug', 'content' => 'Body'),
            array('title' => 'The title field is required.')
        );
    }

    /**
     * Test (Fail): an id of zero is present and redirects to edit/0
     */
    public function testCharacterizationValidationRedirectWhenIdIsZero()
    {
        $this->assertValidationFailure(
            '/admin/pages/edit/0',
            array('id' => '0', 'slug' => 'valid slug', 'content' => 'Body'),
            array('title' => 'The title field is required.')
        );
    }

    /**
     * Test (Fail): invalid input with an existing id redirects to edit/<id>
     */
    public function testCharacterizationValidationRedirectWhenIdExists()
    {
        $page = $this->createModel(Page::class, array(
            'title' => 'Existing title',
            'slug' => 'existing_slug',
            'content' => 'Existing body'
        ));

        $this->assertValidationFailure(
            '/admin/pages/edit/' . $page->id,
            array('id' => $page->id, 'slug' => 'valid slug', 'content' => 'Body'),
            array('title' => 'The title field is required.')
        );
    }

    /**
     * Test (Fail): valid input with a nonexistent id gives the editError not-found message
     */
    public function testCharacterizationNonexistentIdWithValidFields()
    {
        $this->postStore(array(
            'id' => '5',
            'title' => 'Valid title',
            'slug' => 'valid slug',
            'content' => 'Valid body'
        ));

        $this->assertRedirectedTo('/admin/pages');
        $this->assertErrorMessage(
            'editError',
            'The page cannot be found because it does not exist or may have been deleted.'
        );
        $this->assertSame(0, Page::count());
    }

    /**
     * Test (Fail): title is required
     */
    public function testCharacterizationMissingTitle()
    {
        $this->assertValidationFailure(
            '/admin/pages/create',
            array('slug' => 'valid slug', 'content' => 'Body'),
            array('title' => 'The title field is required.')
        );
    }

    /**
     * Test (Fail): slug is required
     */
    public function testCharacterizationMissingSlug()
    {
        $this->assertValidationFailure(
            '/admin/pages/create',
            array('title' => 'Valid title', 'content' => 'Body'),
            array('slug' => 'The slug field is required.')
        );
    }

    /**
     * Test (Fail): content is required
     */
    public function testCharacterizationMissingContent()
    {
        $this->assertValidationFailure(
            '/admin/pages/create',
            array('title' => 'Valid title', 'slug' => 'valid slug'),
            array('content' => 'The content field is required.')
        );
    }

    /**
     * Test (Fail): title regex rejects characters outside the allow-list
     */
    public function testCharacterizationInvalidTitleFormat()
    {
        $this->assertValidationFailure(
            '/admin/pages/create',
            array('title' => 'Open&%*<', 'slug' => 'valid slug', 'content' => 'Body'),
            array('title' => 'The title field format is invalid.')
        );
    }

    /**
     * Test (Fail): slug regex rejects characters outside the allow-list
     */
    public function testCharacterizationInvalidSlugFormat()
    {
        $this->assertValidationFailure(
            '/admin/pages/create',
            array('title' => 'Valid title', 'slug' => 'Open&%*<', 'content' => 'Body'),
            array('slug' => 'The slug field format is invalid.')
        );
    }

    /**
     * Test (Fail): old input keeps scalar values but never an uploaded file
     */
    public function testCharacterizationOldInputWithFile()
    {
        $this->assertValidationFailure(
            '/admin/pages/create',
            array('slug' => 'valid slug', 'content' => 'Body'),
            array('title' => 'The title field is required.'),
            array('images' => array($this->uploadedImage('old-input.png')))
        );

        $this->assertOldInput(array('slug' => 'valid slug', 'content' => 'Body'));
        $this->assertSame(0, Image::count());
    }

    /**
     * Test (Fail): a JSON Accept header still redirects with session errors in the default bag
     */
    public function testCharacterizationJsonRequestStillRedirectsWithDefaultBag()
    {
        $this->postStoreAsJson(array(
            'slug' => 'valid slug',
            'content' => 'Body',
            '_error_bag' => 'custom'
        ));

        $this->assertResponseStatus(302);
        $this->assertRedirectedTo('/admin/pages/create');
        $this->assertSessionHasErrors('title');
        $this->assertErrorMessage('title', 'The title field is required.');
        $this->assertErrorBagIsDefaultOnly();
    }

    /**
     * Test (Pass): create writes one cn translation row and replaces spaces in the translated slug
     */
    public function testCharacterizationTranslationCreated()
    {
        $this->postStore(array(
            'title' => 'Translated title',
            'slug' => 'translated slug',
            'content' => 'Translated body',
            'cn_title' => 'CN title',
            'cn_slug' => 'CN slug here',
            'cn_content' => 'CN content'
        ));

        $this->assertRedirectedTo('/admin/pages');

        $this->assertTranslations(Page::first(), array(
            'cn' => array(
                'title' => 'CN title',
                'slug' => 'CN_slug_here',
                'content' => 'CN content'
            )
        ));
    }

    /**
     * Test (Pass): update reuses the translation row, even when it already existed
     */
    public function testCharacterizationTranslationUpdatedInPlace()
    {
        $this->postStore(array(
            'title' => 'Translation owner',
            'slug' => 'translation slug',
            'content' => 'Body',
            'cn_title' => 'CN title',
            'cn_slug' => 'CN slug here',
            'cn_content' => 'CN content'
        ));

        $page = Page::first();
        $translation = $page->translations()->first();
        $translation->content = json_encode(array(
            'title' => 'pre-existing',
            'slug' => 'pre-existing slug',
            'content' => 'pre-existing content'
        ));
        $translation->save();

        $this->postStore(array(
            'id' => $page->id,
            'title' => 'Translation owner',
            'slug' => 'translation slug',
            'content' => 'Body',
            'cn_title' => 'Updated CN title',
            'cn_slug' => 'Updated CN slug here',
            'cn_content' => 'Updated CN content'
        ));

        $this->assertRedirectedTo('/admin/pages');
        $this->assertSame(1, Page::count());

        $translations = $this->assertTranslations(Page::find($page->id), array(
            'cn' => array(
                'title' => 'Updated CN title',
                'slug' => 'Updated_CN_slug_here',
                'content' => 'Updated CN content'
            )
        ));

        $this->assertSame($translation->id, $translations->first()->id);
    }

    /**
     * Test (Pass): missing translated inputs store null title/content and a null slug
     */
    public function testCharacterizationMissingTranslatedInputs()
    {
        $this->postStore(array(
            'title' => 'No translations',
            'slug' => 'no translations',
            'content' => 'Body'
        ));

        $this->assertRedirectedTo('/admin/pages');

        $this->assertTranslations(Page::first(), array(
            'cn' => array(
                'title' => null,
                'slug' => null,
                'content' => null
            )
        ));
    }

    /**
     * Test (Pass): a comma separated tags input attaches the new tags
     */
    public function testCharacterizationTagsAttached()
    {
        $this->postStore(array(
            'title' => 'Tagged page',
            'slug' => 'tagged page',
            'content' => 'Body',
            'tags' => 'alpha,beta'
        ));

        $this->assertRedirectedTo('/admin/pages');

        $page = Page::first();
        $this->assertSame(array('alpha', 'beta'), $this->tagNames($page));
    }

    /**
     * Test (Pass): a second update with new tags replaces the old ones
     */
    public function testCharacterizationSecondUpdateWithNewTagsReplacesOldTags()
    {
        $this->postStore(array(
            'title' => 'Tagged page',
            'slug' => 'tagged page',
            'content' => 'Body',
            'tags' => 'alpha,beta'
        ));

        $page = Page::first();

        $this->postStore(array(
            'id' => $page->id,
            'title' => 'Tagged page updated',
            'slug' => 'tagged page',
            'content' => 'Body',
            'tags' => 'gamma'
        ));

        $this->assertRedirectedTo('/admin/pages');
        $this->assertSame(array('gamma'), $this->tagNames(Page::find($page->id)));
    }

    /**
     * Test (Pass): an empty tags string clears the old tags
     */
    public function testCharacterizationEmptyTagsStringClearsOldTags()
    {
        $this->postStore(array(
            'title' => 'Tagged page',
            'slug' => 'tagged page',
            'content' => 'Body',
            'tags' => 'alpha'
        ));

        $page = Page::first();

        $this->postStore(array(
            'id' => $page->id,
            'title' => 'Tagged page updated',
            'slug' => 'tagged page',
            'content' => 'Body',
            'tags' => ''
        ));

        $this->assertRedirectedTo('/admin/pages');
        $this->assertSame(array(), $this->tagNames(Page::find($page->id)));
    }

    /**
     * Test (Pass): a tags value of "0" is empty() so it clears the old tags
     */
    public function testCharacterizationZeroTagsStringClearsOldTags()
    {
        $this->postStore(array(
            'title' => 'Tagged page',
            'slug' => 'tagged page',
            'content' => 'Body',
            'tags' => 'alpha'
        ));

        $page = Page::first();

        $this->postStore(array(
            'id' => $page->id,
            'title' => 'Tagged page updated',
            'slug' => 'tagged page',
            'content' => 'Body',
            'tags' => '0'
        ));

        $this->assertRedirectedTo('/admin/pages');
        $this->assertSame(array(), $this->tagNames(Page::find($page->id)));
    }

    /**
     * Test (Fail): an array tags input fails validation and keeps the old tags
     */
    public function testCharacterizationArrayTagsFailsValidation()
    {
        $this->postStore(array(
            'title' => 'Tagged page',
            'slug' => 'tagged page',
            'content' => 'Body',
            'tags' => 'alpha'
        ));

        $page = Page::first();

        $this->assertValidationFailure(
            '/admin/pages/edit/' . $page->id,
            array(
                'id' => $page->id,
                'title' => 'Tagged page updated',
                'slug' => 'tagged page',
                'content' => 'Body',
                'tags' => array()
            ),
            array('tags' => 'The tags field must be a string.')
        );

        $this->assertSame('Tagged page', Page::find($page->id)->title);
        $this->assertSame(array('alpha'), $this->tagNames(Page::find($page->id)));
    }

    /**
     * Test (Pass): no tags input keeps the old tags
     */
    public function testCharacterizationNoTagsInputKeepsOldTags()
    {
        $this->postStore(array(
            'title' => 'Tagged page',
            'slug' => 'tagged page',
            'content' => 'Body',
            'tags' => 'alpha'
        ));

        $page = Page::first();

        $this->postStore(array(
            'id' => $page->id,
            'title' => 'Tagged page updated',
            'slug' => 'tagged page',
            'content' => 'Body'
        ));

        $this->assertRedirectedTo('/admin/pages');
        $this->assertSame(array('alpha'), $this->tagNames(Page::find($page->id)));
    }

    /**
     * Test (Pass): getEdit passes page, translated, imagine, categories and tagString view data
     */
    public function testCharacterizationGetEditViewData()
    {
        $page = $this->createModel(Page::class, array(
            'title' => 'View title',
            'slug' => 'view_slug',
            'content' => 'View body'
        ));
        $translation = new Translation;
        $translation->lang = 'cn';
        $translation->content = json_encode(array(
            'title' => 'View CN title',
            'slug' => 'View_CN_slug',
            'content' => 'View CN content'
        ));
        $page->translations()->save($translation);
        \Redooor\Redminportal\App\Models\Tag::addTag($page, 'alpha');
        \Redooor\Redminportal\App\Models\Tag::addTag($page, 'beta');

        $this->call('GET', '/admin/pages/edit/' . $page->id);

        $this->assertResponseOk();
        $this->assertViewHas('page', function ($value) use ($page) {
            return $value instanceof Page && $value->id == $page->id;
        });
        $this->assertViewHas('translated', function ($value) {
            return isset($value['cn']) && $value['cn']->title === 'View CN title';
        });
        $this->assertViewHas('imagine');
        $this->assertViewHas('categories');
        $this->assertViewHas('tagString', 'alpha,beta');
    }

    /**
     * Test (Fail): getEdit for a nonexistent id uses the lowercase not-found message
     */
    public function testCharacterizationGetEditMissing()
    {
        $this->call('GET', '/admin/pages/edit/999');

        $this->assertRedirectedTo('/admin/pages');
        $this->assertErrorMessage(
            'editError',
            'The page cannot be found because it does not exist or may have been deleted.'
        );
    }

    /**
     * Test (Fail): an image owned by another model cannot be removed through pages
     */
    public function testCharacterizationImgremoveWrongOwner()
    {
        $category = $this->createModel(Category::class, array(
            'name' => 'Page image owner ' . uniqid(),
            'short_description' => 'Short description'
        ));
        $image = $this->attachImage($category);

        $this->call('POST', '/admin/pages/imgremove/' . $image->id);

        $this->assertRedirectedTo('/admin/pages');
        $this->assertErrorMessage('deleteError', 'The image cannot be deleted at this time.');
        $this->assertNotNull(Image::find($image->id));
    }

    /**
     * Test (Fail): a nonexistent image id gives the deleteError message
     */
    public function testCharacterizationImgremoveMissing()
    {
        $this->call('POST', '/admin/pages/imgremove/999999');

        $this->assertRedirectedTo('/admin/pages');
        $this->assertErrorMessage('deleteError', 'The image cannot be deleted at this time.');
    }

    /**
     * Test (Fail): a non-image upload fails the mimes rule with the exact message
     */
    public function testCharacterizationInvalidImageMime()
    {
        $this->assertValidationFailure(
            '/admin/pages/create',
            array('title' => 'Bad image', 'slug' => 'bad image', 'content' => 'Body'),
            array('image' => 'The image field must be a file of type: jpg, jpeg, png, gif.'),
            array('image' => $this->invalidUploadedFile())
        );

        $this->assertSame(0, Page::count());
        $this->assertSame(0, Image::count());
    }

    /**
     * Test (Pass): two files in images[] attach two Image rows
     */
    public function testCharacterizationImagesArrayAttachesTwoRows()
    {
        $this->postStore(
            array('title' => 'Image title', 'slug' => 'image slug', 'content' => 'Body'),
            array('images' => array(
                $this->uploadedImage('first.png'),
                $this->uploadedImage('second.png')
            ))
        );

        $this->assertRedirectedTo('/admin/pages');
        $this->assertSame(1, Page::count());
        $this->assertSame(2, Image::count());

        $page = Page::first();
        $this->assertSame(2, $page->images()->count());
        $this->assertSame(
            array(1, 2),
            array_map('intval', Image::orderBy('id')->pluck('sort_order')->all())
        );
        $this->assertSame(Page::class, Image::first()->imageable_type);
        $this->assertSame($page->id, Image::first()->imageable_id);
    }

    /**
     * Test (Pass): the legacy single image field attaches one Image row
     */
    public function testCharacterizationLegacySingleImageAttachesOneRow()
    {
        $this->postStore(
            array('title' => 'Legacy image', 'slug' => 'legacy image', 'content' => 'Body'),
            array('image' => $this->uploadedImage('legacy.png'))
        );

        $this->assertRedirectedTo('/admin/pages');
        $this->assertSame(1, Page::count());
        $this->assertSame(1, Image::count());
        $this->assertSame(Page::class, Image::first()->imageable_type);
    }

    /**
     * Test (Fail): a validation failure creates no Image rows
     */
    public function testCharacterizationValidationFailureCreatesNoImages()
    {
        $this->assertValidationFailure(
            '/admin/pages/create',
            array('slug' => 'valid slug', 'content' => 'Body'),
            array('title' => 'The title field is required.'),
            array('images' => array($this->uploadedImage('not-saved.png')))
        );

        $this->assertSame(0, Page::count());
        $this->assertSame(0, Image::count());
    }
}