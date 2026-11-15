<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Announcement;
use Redooor\Redminportal\App\Models\Category;
use Redooor\Redminportal\App\Models\Image;

/**
 * Pins the behaviour before the controller refactor; do not edit to make a refactor pass.
 */
class AnnouncementCharacterizationTest extends BaseControllerTest
{
    use CharacterizationSupport;

    public function setUp(): void
    {
        parent::setUp();

        $this->page = '/admin/announcements';
        $this->viewhas = array(
            'singular' => 'announcement',
            'plural' => 'models'
        );
        $this->input = array(
            'create' => array(
                'title' => 'This is title',
                'content' => 'This is body',
                'private' => false
            ),
            'edit' => array(
                'id' => 1,
                'title' => 'This is title',
                'content' => 'This is body',
                'private' => false
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
            'content' => 'Original body',
            'private' => '1'
        ));

        $this->assertRedirectedTo('/admin/announcements');
        $this->assertSame(1, Announcement::count());

        $announcement = Announcement::first();
        $this->assertSame('Original title', $announcement->title);
        $this->assertSame('Original body', $announcement->content);
        $this->assertSame(1, (int) $announcement->private);

        $this->postStore(array(
            'id' => $announcement->id,
            'title' => 'Updated title',
            'content' => 'Updated body',
            'private' => ''
        ));

        $this->assertRedirectedTo('/admin/announcements');
        $this->assertSame(1, Announcement::count());

        $announcement = Announcement::first();
        $this->assertSame('Updated title', $announcement->title);
        $this->assertSame('Updated body', $announcement->content);
        $this->assertSame(0, (int) $announcement->private);
    }

    /**
     * Test (Fail): invalid input with no id redirects to create
     */
    public function testCharacterizationValidationRedirectWhenIdOmitted()
    {
        $this->assertValidationFailure(
            '/admin/announcements/create',
            array('content' => 'Body'),
            array('title' => 'The title field is required.')
        );
    }

    /**
     * Test (Fail): invalid input with a nonexistent id redirects to edit/5 before the not-found check
     */
    public function testCharacterizationValidationRedirectWhenIdIsFive()
    {
        $this->assertValidationFailure(
            '/admin/announcements/edit/5',
            array('id' => '5', 'content' => 'Body'),
            array('title' => 'The title field is required.')
        );
    }

    /**
     * Test (Fail): an empty id is converted to null by the HTTP middleware, so it redirects to create
     */
    public function testCharacterizationValidationRedirectWhenIdIsEmptyString()
    {
        $this->assertValidationFailure(
            '/admin/announcements/create',
            array('id' => '', 'content' => 'Body'),
            array('title' => 'The title field is required.')
        );
    }

    /**
     * Test (Fail): an id of zero is present and redirects to edit/0
     */
    public function testCharacterizationValidationRedirectWhenIdIsZero()
    {
        $this->assertValidationFailure(
            '/admin/announcements/edit/0',
            array('id' => '0', 'content' => 'Body'),
            array('title' => 'The title field is required.')
        );
    }

    /**
     * Test (Fail): invalid input with an existing id redirects to edit/<id>
     */
    public function testCharacterizationValidationRedirectWhenIdExists()
    {
        $announcement = $this->createModel(Announcement::class, array(
            'title' => 'Existing title',
            'content' => 'Existing body'
        ));

        $this->assertValidationFailure(
            '/admin/announcements/edit/' . $announcement->id,
            array('id' => $announcement->id, 'content' => 'Body'),
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
            'content' => 'Valid body'
        ));

        $this->assertRedirectedTo('/admin/announcements');
        $this->assertErrorMessage(
            'editError',
            'The Announcement cannot be found because it does not exist or may have been deleted.'
        );
        $this->assertSame(0, Announcement::count());
    }

    /**
     * Test (Fail): title is required with the exact model validate() message
     */
    public function testCharacterizationMissingTitle()
    {
        $this->assertValidationFailure(
            '/admin/announcements/create',
            array('content' => 'Body'),
            array('title' => 'The title field is required.')
        );
    }

    /**
     * Test (Fail): content is required with the exact model validate() message
     */
    public function testCharacterizationMissingContent()
    {
        $this->assertValidationFailure(
            '/admin/announcements/create',
            array('title' => 'Valid title'),
            array('content' => 'The content field is required.')
        );
    }

    /**
     * Test (Fail): title regex rejects characters outside the allow-list
     */
    public function testCharacterizationInvalidTitleFormat()
    {
        $this->assertValidationFailure(
            '/admin/announcements/create',
            array('title' => 'Open&%*<', 'content' => 'Body'),
            array('title' => 'The title field format is invalid.')
        );
    }

    /**
     * Test (Fail): old input keeps scalar values but never an uploaded file
     */
    public function testCharacterizationOldInputWithFile()
    {
        $this->assertValidationFailure(
            '/admin/announcements/create',
            array('content' => 'Body'),
            array('title' => 'The title field is required.'),
            array('images' => array($this->uploadedImage('old-input.png')))
        );

        $this->assertOldInput(array('content' => 'Body'));
        $this->assertSame(0, Image::count());
    }

    /**
     * Test (Fail): a JSON Accept header still redirects with session errors in the default bag
     */
    public function testCharacterizationJsonRequestStillRedirectsWithDefaultBag()
    {
        $this->postStoreAsJson(array('content' => 'Body', '_error_bag' => 'custom'));

        $this->assertResponseStatus(302);
        $this->assertRedirectedTo('/admin/announcements/create');
        $this->assertSessionHasErrors('title');
        $this->assertErrorMessage('title', 'The title field is required.');
        $this->assertErrorBagIsDefaultOnly();
    }

    /**
     * Test (Pass): getEdit passes the announcement and imagine view data
     */
    public function testCharacterizationGetEditViewData()
    {
        $announcement = $this->createModel(Announcement::class, array(
            'title' => 'View title',
            'content' => 'View body'
        ));

        $this->call('GET', '/admin/announcements/edit/' . $announcement->id);

        $this->assertResponseOk();
        $this->assertViewHas('announcement', function ($value) use ($announcement) {
            return $value instanceof Announcement && $value->id == $announcement->id;
        });
        $this->assertViewHas('imagine');
        $this->response->assertViewMissing('translated');
    }

    /**
     * Test (Fail): getEdit for a nonexistent id uses the lowercase not-found message
     */
    public function testCharacterizationGetEditMissing()
    {
        $this->call('GET', '/admin/announcements/edit/999');

        $this->assertRedirectedTo('/admin/announcements');
        $this->assertErrorMessage(
            'editError',
            'The announcement cannot be found because it does not exist or may have been deleted.'
        );
    }

    /**
     * Test (Fail): an image owned by another model cannot be removed through announcements
     */
    public function testCharacterizationImgremoveWrongOwner()
    {
        $category = $this->createModel(Category::class, array(
            'name' => 'Announcement image owner ' . uniqid(),
            'short_description' => 'Short description'
        ));
        $image = $this->attachImage($category);

        $this->call('POST', '/admin/announcements/imgremove/' . $image->id);

        $this->assertRedirectedTo('/admin/announcements');
        $this->assertErrorMessage('deleteError', 'The image cannot be deleted at this time.');
        $this->assertNotNull(Image::find($image->id));
    }

    /**
     * Test (Fail): a nonexistent image id gives the deleteError message
     */
    public function testCharacterizationImgremoveMissing()
    {
        $this->call('POST', '/admin/announcements/imgremove/999999');

        $this->assertRedirectedTo('/admin/announcements');
        $this->assertErrorMessage('deleteError', 'The image cannot be deleted at this time.');
    }

    /**
     * Test (Fail): a non-image upload fails the mimes rule with the exact message
     */
    public function testCharacterizationInvalidImageMime()
    {
        $this->assertValidationFailure(
            '/admin/announcements/create',
            array('title' => 'Bad image', 'content' => 'Body'),
            array('image' => 'The image field must be a file of type: jpg, jpeg, png, gif.'),
            array('image' => $this->invalidUploadedFile())
        );

        $this->assertSame(0, Announcement::count());
        $this->assertSame(0, Image::count());
    }

    /**
     * Test (Pass): two files in images[] attach two Image rows
     */
    public function testCharacterizationImagesArrayAttachesTwoRows()
    {
        $this->postStore(
            array('title' => 'Image title', 'content' => 'Image body'),
            array('images' => array(
                $this->uploadedImage('first.png'),
                $this->uploadedImage('second.png')
            ))
        );

        $this->assertRedirectedTo('/admin/announcements');
        $this->assertSame(1, Announcement::count());
        $this->assertSame(2, Image::count());

        $announcement = Announcement::first();
        $this->assertSame(2, $announcement->images()->count());
        $this->assertSame(
            array(1, 2),
            array_map('intval', Image::orderBy('id')->pluck('sort_order')->all())
        );
        $this->assertSame(
            array(1, 0),
            array_map('intval', Image::orderBy('id')->pluck('is_primary')->all())
        );
        $this->assertSame(Announcement::class, Image::first()->imageable_type);
        $this->assertSame($announcement->id, Image::first()->imageable_id);
    }

    /**
     * Test (Pass): the legacy single image field attaches one Image row
     */
    public function testCharacterizationLegacySingleImageAttachesOneRow()
    {
        $this->postStore(
            array('title' => 'Legacy image title', 'content' => 'Image body'),
            array('image' => $this->uploadedImage('legacy.png'))
        );

        $this->assertRedirectedTo('/admin/announcements');
        $this->assertSame(1, Announcement::count());
        $this->assertSame(1, Image::count());
        $this->assertSame(Announcement::class, Image::first()->imageable_type);
    }

    /**
     * Test (Fail): a validation failure creates no Image rows
     */
    public function testCharacterizationValidationFailureCreatesNoImages()
    {
        $this->assertValidationFailure(
            '/admin/announcements/create',
            array('content' => 'Body'),
            array('title' => 'The title field is required.'),
            array('images' => array($this->uploadedImage('not-saved.png')))
        );

        $this->assertSame(0, Announcement::count());
        $this->assertSame(0, Image::count());
    }
}
