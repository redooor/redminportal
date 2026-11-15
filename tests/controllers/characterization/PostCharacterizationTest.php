<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Category;
use Redooor\Redminportal\App\Models\Image;
use Redooor\Redminportal\App\Models\Post;
use Redooor\Redminportal\App\Models\Translation;

/**
 * Pins the behaviour before the controller refactor; do not edit to make a refactor pass.
 */
class PostCharacterizationTest extends BaseControllerTest
{
    use CharacterizationSupport;

    public function setUp(): void
    {
        parent::setUp();

        $this->page = '/admin/posts';
        $this->viewhas = array(
            'singular' => 'post',
            'plural' => 'models'
        );
        $this->input = array(
            'create' => array(
                'title' => 'This is the title',
                'slug' => 'this_is_a_slug',
                'content' => 'This is the body',
                'private' => false,
                'featured' => true,
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
                'featured' => true,
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
            'featured' => '1',
            'category_id' => '1'
        ));

        $this->assertRedirectedTo('/admin/posts');
        $this->assertSame(1, Post::count());

        $post = Post::first();
        $this->assertSame('Original title', $post->title);
        $this->assertSame('original_slug_here', $post->slug);
        $this->assertSame('Original body', $post->content);
        $this->assertSame(1, (int) $post->private);
        $this->assertSame(1, (int) $post->featured);
        $this->assertSame(1, (int) $post->category_id);

        $this->postStore(array(
            'id' => $post->id,
            'title' => 'Updated title',
            'slug' => 'updated slug here',
            'content' => 'Updated body',
            'private' => '',
            'featured' => ''
        ));

        $this->assertRedirectedTo('/admin/posts');
        $this->assertSame(1, Post::count());

        $post = Post::first();
        $this->assertSame('Updated title', $post->title);
        $this->assertSame('updated_slug_here', $post->slug);
        $this->assertSame('Updated body', $post->content);
        $this->assertSame(0, (int) $post->private);
        $this->assertSame(0, (int) $post->featured);
        $this->assertNull($post->category_id);
    }

    /**
     * Test (Fail): invalid input with no id redirects to create
     */
    public function testCharacterizationValidationRedirectWhenIdOmitted()
    {
        $this->assertValidationFailure(
            '/admin/posts/create',
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
            '/admin/posts/edit/5',
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
            '/admin/posts/create',
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
            '/admin/posts/edit/0',
            array('id' => '0', 'slug' => 'valid slug', 'content' => 'Body'),
            array('title' => 'The title field is required.')
        );
    }

    /**
     * Test (Fail): invalid input with an existing id redirects to edit/<id>
     */
    public function testCharacterizationValidationRedirectWhenIdExists()
    {
        $post = $this->createModel(Post::class, array(
            'title' => 'Existing title',
            'slug' => 'existing_slug',
            'content' => 'Existing body'
        ));

        $this->assertValidationFailure(
            '/admin/posts/edit/' . $post->id,
            array('id' => $post->id, 'slug' => 'valid slug', 'content' => 'Body'),
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

        $this->assertRedirectedTo('/admin/posts');
        $this->assertErrorMessage(
            'editError',
            'The post cannot be found because it does not exist or may have been deleted.'
        );
        $this->assertSame(0, Post::count());
    }

    /**
     * Test (Fail): title is required
     */
    public function testCharacterizationMissingTitle()
    {
        $this->assertValidationFailure(
            '/admin/posts/create',
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
            '/admin/posts/create',
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
            '/admin/posts/create',
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
            '/admin/posts/create',
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
            '/admin/posts/create',
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
            '/admin/posts/create',
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
        $this->assertRedirectedTo('/admin/posts/create');
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

        $this->assertRedirectedTo('/admin/posts');

        $this->assertTranslations(Post::first(), array(
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

        $post = Post::first();
        $translation = $post->translations()->first();
        $translation->content = json_encode(array(
            'title' => 'pre-existing',
            'slug' => 'pre-existing slug',
            'content' => 'pre-existing content'
        ));
        $translation->save();

        $this->postStore(array(
            'id' => $post->id,
            'title' => 'Translation owner',
            'slug' => 'translation slug',
            'content' => 'Body',
            'cn_title' => 'Updated CN title',
            'cn_slug' => 'Updated CN slug here',
            'cn_content' => 'Updated CN content'
        ));

        $this->assertRedirectedTo('/admin/posts');
        $this->assertSame(1, Post::count());

        $translations = $this->assertTranslations(Post::find($post->id), array(
            'cn' => array(
                'title' => 'Updated CN title',
                'slug' => 'Updated_CN_slug_here',
                'content' => 'Updated CN content'
            )
        ));

        $this->assertSame($translation->id, $translations->first()->id);
    }

    /**
     * Test (Pass): missing translated inputs store null title/content and an empty slug
     */
    public function testCharacterizationMissingTranslatedInputs()
    {
        $this->postStore(array(
            'title' => 'No translations',
            'slug' => 'no translations',
            'content' => 'Body'
        ));

        $this->assertRedirectedTo('/admin/posts');

        $this->assertTranslations(Post::first(), array(
            'cn' => array(
                'title' => null,
                'slug' => '',
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
            'title' => 'Tagged post',
            'slug' => 'tagged post',
            'content' => 'Body',
            'tags' => 'alpha,beta'
        ));

        $this->assertRedirectedTo('/admin/posts');

        $post = Post::first();
        $this->assertSame(array('alpha', 'beta'), $this->tagNames($post));
    }

    /**
     * Test (Pass): a second update with new tags replaces the old ones
     */
    public function testCharacterizationSecondUpdateWithNewTagsReplacesOldTags()
    {
        $this->postStore(array(
            'title' => 'Tagged post',
            'slug' => 'tagged post',
            'content' => 'Body',
            'tags' => 'alpha,beta'
        ));

        $post = Post::first();

        $this->postStore(array(
            'id' => $post->id,
            'title' => 'Tagged post updated',
            'slug' => 'tagged post',
            'content' => 'Body',
            'tags' => 'gamma'
        ));

        $this->assertRedirectedTo('/admin/posts');
        $this->assertSame(array('gamma'), $this->tagNames(Post::find($post->id)));
    }

    /**
     * Test (Pass): an empty tags string keeps the old tags
     */
    public function testCharacterizationEmptyTagsStringKeepsOldTags()
    {
        $this->postStore(array(
            'title' => 'Tagged post',
            'slug' => 'tagged post',
            'content' => 'Body',
            'tags' => 'alpha'
        ));

        $post = Post::first();

        $this->postStore(array(
            'id' => $post->id,
            'title' => 'Tagged post updated',
            'slug' => 'tagged post',
            'content' => 'Body',
            'tags' => ''
        ));

        $this->assertRedirectedTo('/admin/posts');
        $this->assertSame(array('alpha'), $this->tagNames(Post::find($post->id)));
    }

    /**
     * Test (Pass): a tags value of "0" is empty() so it keeps the old tags
     */
    public function testCharacterizationZeroTagsStringKeepsOldTags()
    {
        $this->postStore(array(
            'title' => 'Tagged post',
            'slug' => 'tagged post',
            'content' => 'Body',
            'tags' => 'alpha'
        ));

        $post = Post::first();

        $this->postStore(array(
            'id' => $post->id,
            'title' => 'Tagged post updated',
            'slug' => 'tagged post',
            'content' => 'Body',
            'tags' => '0'
        ));

        $this->assertRedirectedTo('/admin/posts');
        $this->assertSame(array('alpha'), $this->tagNames(Post::find($post->id)));
    }

    /**
     * Test (Pass): a tags array is empty() so it keeps the old tags
     */
    public function testCharacterizationArrayTagsKeepsOldTags()
    {
        $this->postStore(array(
            'title' => 'Tagged post',
            'slug' => 'tagged post',
            'content' => 'Body',
            'tags' => 'alpha'
        ));

        $post = Post::first();

        $this->postStore(array(
            'id' => $post->id,
            'title' => 'Tagged post updated',
            'slug' => 'tagged post',
            'content' => 'Body',
            'tags' => array()
        ));

        $this->assertRedirectedTo('/admin/posts');
        $this->assertSame(array('alpha'), $this->tagNames(Post::find($post->id)));
    }

    /**
     * Test (Pass): no tags input keeps the old tags
     */
    public function testCharacterizationNoTagsInputKeepsOldTags()
    {
        $this->postStore(array(
            'title' => 'Tagged post',
            'slug' => 'tagged post',
            'content' => 'Body',
            'tags' => 'alpha'
        ));

        $post = Post::first();

        $this->postStore(array(
            'id' => $post->id,
            'title' => 'Tagged post updated',
            'slug' => 'tagged post',
            'content' => 'Body'
        ));

        $this->assertRedirectedTo('/admin/posts');
        $this->assertSame(array('alpha'), $this->tagNames(Post::find($post->id)));
    }

    /**
     * Test (Pass): getEdit passes post, translated, imagine, categories and tagString view data
     */
    public function testCharacterizationGetEditViewData()
    {
        $post = $this->createModel(Post::class, array(
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
        $post->translations()->save($translation);
        \Redooor\Redminportal\App\Models\Tag::addTag($post, 'alpha');
        \Redooor\Redminportal\App\Models\Tag::addTag($post, 'beta');

        $this->call('GET', '/admin/posts/edit/' . $post->id);

        $this->assertResponseOk();
        $this->assertViewHas('post', function ($value) use ($post) {
            return $value instanceof Post && $value->id == $post->id;
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
        $this->call('GET', '/admin/posts/edit/999');

        $this->assertRedirectedTo('/admin/posts');
        $this->assertErrorMessage(
            'editError',
            'The post cannot be found because it does not exist or may have been deleted.'
        );
    }

    /**
     * Test (Fail): an image owned by another model cannot be removed through posts
     */
    public function testCharacterizationImgremoveWrongOwner()
    {
        $category = $this->createModel(Category::class, array(
            'name' => 'Post image owner ' . uniqid(),
            'short_description' => 'Short description'
        ));
        $image = $this->attachImage($category);

        $this->call('POST', '/admin/posts/imgremove/' . $image->id);

        $this->assertRedirectedTo('/admin/posts');
        $this->assertErrorMessage('deleteError', 'The image cannot be deleted at this time.');
        $this->assertNotNull(Image::find($image->id));
    }

    /**
     * Test (Fail): a nonexistent image id gives the deleteError message
     */
    public function testCharacterizationImgremoveMissing()
    {
        $this->call('POST', '/admin/posts/imgremove/999999');

        $this->assertRedirectedTo('/admin/posts');
        $this->assertErrorMessage('deleteError', 'The image cannot be deleted at this time.');
    }

    /**
     * Test (Fail): a non-image upload fails the mimes rule with the exact message
     */
    public function testCharacterizationInvalidImageMime()
    {
        $this->assertValidationFailure(
            '/admin/posts/create',
            array('title' => 'Bad image', 'slug' => 'bad image', 'content' => 'Body'),
            array('image' => 'The image field must be a file of type: jpg, jpeg, png, gif.'),
            array('image' => $this->invalidUploadedFile())
        );

        $this->assertSame(0, Post::count());
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

        $this->assertRedirectedTo('/admin/posts');
        $this->assertSame(1, Post::count());
        $this->assertSame(2, Image::count());

        $post = Post::first();
        $this->assertSame(2, $post->images()->count());
        $this->assertSame(
            array(1, 2),
            array_map('intval', Image::orderBy('id')->pluck('sort_order')->all())
        );
        $this->assertSame(Post::class, Image::first()->imageable_type);
        $this->assertSame($post->id, Image::first()->imageable_id);
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

        $this->assertRedirectedTo('/admin/posts');
        $this->assertSame(1, Post::count());
        $this->assertSame(1, Image::count());
        $this->assertSame(Post::class, Image::first()->imageable_type);
    }

    /**
     * Test (Fail): a validation failure creates no Image rows
     */
    public function testCharacterizationValidationFailureCreatesNoImages()
    {
        $this->assertValidationFailure(
            '/admin/posts/create',
            array('slug' => 'valid slug', 'content' => 'Body'),
            array('title' => 'The title field is required.'),
            array('images' => array($this->uploadedImage('not-saved.png')))
        );

        $this->assertSame(0, Post::count());
        $this->assertSame(0, Image::count());
    }
}