<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Category;
use Redooor\Redminportal\App\Models\Image;
use Redooor\Redminportal\App\Models\Promotion;
use Redooor\Redminportal\App\Models\Translation;

/**
 * Pins the behaviour before the controller refactor; do not edit to make a refactor pass.
 */
class PromotionCharacterizationTest extends BaseControllerTest
{
    use CharacterizationSupport;

    public function setUp(): void
    {
        parent::setUp();

        $this->page = '/admin/promotions';
        $this->viewhas = array(
            'singular' => 'promotion',
            'plural' => 'models'
        );
        $this->input = array(
            'create' => array(
                'name' => 'This is title',
                'short_description' => 'This is body',
                'long_description' => 'This is long body',
                'start_date' => '29/02/2016',
                'end_date' => '01/03/2016',
                'active' => true,
                'cn_name' => 'This is cn name',
                'cn_short_description' => 'This is cn short description',
                'cn_long_description' => 'This is cn long description'
            ),
            'edit' => array(
                'id' => 1,
                'name' => 'This is title',
                'short_description' => 'This is body',
                'long_description' => 'This is long body',
                'start_date' => '29/02/2016',
                'end_date' => '01/03/2016',
                'active' => true,
                'cn_name' => 'This is cn name',
                'cn_short_description' => 'This is cn short description',
                'cn_long_description' => 'This is cn long description'
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
            'name' => 'Original name',
            'short_description' => 'Original short',
            'long_description' => 'Original long',
            'start_date' => '29/02/2016',
            'end_date' => '01/03/2016',
            'active' => '1'
        ));

        $this->assertRedirectedTo('/admin/promotions');
        $this->assertSame(1, Promotion::count());

        $promotion = Promotion::first();
        $this->assertSame('Original name', $promotion->name);
        $this->assertSame('Original short', $promotion->short_description);
        $this->assertSame('Original long', $promotion->long_description);
        $this->assertStringStartsWith('2016-02-29', $promotion->start_date);
        $this->assertStringStartsWith('2016-03-01', $promotion->end_date);
        $this->assertSame(1, (int) $promotion->active);

        $this->postStore(array(
            'id' => $promotion->id,
            'name' => 'Updated name',
            'short_description' => 'Updated short',
            'long_description' => 'Updated long',
            'start_date' => '30/03/2016',
            'end_date' => '31/03/2016',
            'active' => ''
        ));

        $this->assertRedirectedTo('/admin/promotions');
        $this->assertSame(1, Promotion::count());

        $promotion = Promotion::first();
        $this->assertSame('Updated name', $promotion->name);
        $this->assertSame('Updated short', $promotion->short_description);
        $this->assertSame('Updated long', $promotion->long_description);
        $this->assertStringStartsWith('2016-03-30', $promotion->start_date);
        $this->assertStringStartsWith('2016-03-31', $promotion->end_date);
        $this->assertSame(0, (int) $promotion->active);
    }

    /**
     * Test (Fail): invalid input with no id redirects to create
     */
    public function testCharacterizationValidationRedirectWhenIdOmitted()
    {
        $this->assertValidationFailure(
            '/admin/promotions/create',
            array('short_description' => 'Short'),
            array('name' => 'The name field is required.')
        );
    }

    /**
     * Test (Fail): invalid input with a nonexistent id redirects to edit/5 before the not-found check
     */
    public function testCharacterizationValidationRedirectWhenIdIsFive()
    {
        $this->assertValidationFailure(
            '/admin/promotions/edit/5',
            array('id' => '5', 'short_description' => 'Short'),
            array('name' => 'The name field is required.')
        );
    }

    /**
     * Test (Fail): an empty id is converted to null by the HTTP middleware, so it redirects to create
     */
    public function testCharacterizationValidationRedirectWhenIdIsEmptyString()
    {
        $this->assertValidationFailure(
            '/admin/promotions/create',
            array('id' => '', 'short_description' => 'Short'),
            array('name' => 'The name field is required.')
        );
    }

    /**
     * Test (Fail): an id of zero is present and redirects to edit/0
     */
    public function testCharacterizationValidationRedirectWhenIdIsZero()
    {
        $this->assertValidationFailure(
            '/admin/promotions/edit/0',
            array('id' => '0', 'short_description' => 'Short'),
            array('name' => 'The name field is required.')
        );
    }

    /**
     * Test (Fail): invalid input with an existing id redirects to edit/<id>
     */
    public function testCharacterizationValidationRedirectWhenIdExists()
    {
        $promotion = $this->createModel(Promotion::class, array(
            'name' => 'Existing name',
            'short_description' => 'Existing short',
            'start_date' => '2016-02-29 00:00:00',
            'end_date' => '2016-03-01 00:00:00'
        ));

        $this->assertValidationFailure(
            '/admin/promotions/edit/' . $promotion->id,
            array('id' => $promotion->id, 'short_description' => 'Short'),
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
            'name' => 'Valid name',
            'short_description' => 'Valid short',
            'long_description' => 'Valid long',
            'start_date' => '29/02/2016',
            'end_date' => '01/03/2016'
        ));

        $this->assertRedirectedTo('/admin/promotions');
        $this->assertErrorMessage(
            'editError',
            'The promotion cannot be found because it does not exist or may have been deleted.'
        );
        $this->assertSame(0, Promotion::count());
    }

    /**
     * Test (Fail): name is required
     */
    public function testCharacterizationMissingName()
    {
        $this->assertValidationFailure(
            '/admin/promotions/create',
            array(
                'short_description' => 'Short',
                'long_description' => 'Long',
                'start_date' => '29/02/2016',
                'end_date' => '01/03/2016'
            ),
            array('name' => 'The name field is required.')
        );
    }

    /**
     * Test (Fail): short_description is required
     */
    public function testCharacterizationMissingShortDescription()
    {
        $this->assertValidationFailure(
            '/admin/promotions/create',
            array(
                'name' => 'Name',
                'long_description' => 'Long',
                'start_date' => '29/02/2016',
                'end_date' => '01/03/2016'
            ),
            array('short_description' => 'The short description field is required.')
        );
    }

    /**
     * Test (Fail): long_description is required
     */
    public function testCharacterizationMissingLongDescription()
    {
        $this->assertValidationFailure(
            '/admin/promotions/create',
            array(
                'name' => 'Name',
                'short_description' => 'Short',
                'start_date' => '29/02/2016',
                'end_date' => '01/03/2016'
            ),
            array('long_description' => 'The long description field is required.')
        );
    }

    /**
     * Test (Fail): start_date is required
     */
    public function testCharacterizationMissingStartDate()
    {
        $this->assertValidationFailure(
            '/admin/promotions/create',
            array(
                'name' => 'Name',
                'short_description' => 'Short',
                'long_description' => 'Long',
                'end_date' => '01/03/2016'
            ),
            array('start_date' => 'The start date field is required.')
        );
    }

    /**
     * Test (Fail): end_date is required
     */
    public function testCharacterizationMissingEndDate()
    {
        $this->assertValidationFailure(
            '/admin/promotions/create',
            array(
                'name' => 'Name',
                'short_description' => 'Short',
                'long_description' => 'Long',
                'start_date' => '29/02/2016'
            ),
            array('end_date' => 'The end date field is required.')
        );
    }

    /**
     * Test (Fail): an unparseable start date is rejected by the date_format rule
     */
    public function testCharacterizationInvalidDateIsRejected()
    {
        $this->assertValidationFailure(
            '/admin/promotions/create',
            array(
                'name' => 'Bad date',
                'short_description' => 'Short',
                'long_description' => 'Long',
                'start_date' => 'not-a-date',
                'end_date' => '01/03/2016'
            ),
            array('start_date' => 'The start date field must match the format d/m/Y.')
        );

        $this->assertSame(0, Promotion::count());
    }

    /**
     * Test (Fail): an end before start is rejected by the after_or_equal rule
     */
    public function testCharacterizationDateOrderingIsValidated()
    {
        $this->assertValidationFailure(
            '/admin/promotions/create',
            array(
                'name' => 'Backwards dates',
                'short_description' => 'Short',
                'long_description' => 'Long',
                'start_date' => '01/03/2016',
                'end_date' => '29/02/2016'
            ),
            array('end_date' => 'The end date field must be a date after or equal to start date.')
        );

        $this->assertSame(0, Promotion::count());
    }

    /**
     * Test (Fail): old input keeps scalar values but never an uploaded file
     */
    public function testCharacterizationOldInputWithFile()
    {
        $this->assertValidationFailure(
            '/admin/promotions/create',
            array(
                'short_description' => 'Short',
                'long_description' => 'Long',
                'start_date' => '29/02/2016',
                'end_date' => '01/03/2016'
            ),
            array('name' => 'The name field is required.'),
            array('images' => array($this->uploadedImage('old-input.png')))
        );

        $this->assertOldInput(array(
            'short_description' => 'Short',
            'long_description' => 'Long',
            'start_date' => '29/02/2016',
            'end_date' => '01/03/2016'
        ));
        $this->assertSame(0, Image::count());
    }

    /**
     * Test (Fail): a JSON Accept header still redirects with session errors in the default bag
     */
    public function testCharacterizationJsonRequestStillRedirectsWithDefaultBag()
    {
        $this->postStoreAsJson(array(
            'short_description' => 'Short',
            'long_description' => 'Long',
            'start_date' => '29/02/2016',
            'end_date' => '01/03/2016',
            '_error_bag' => 'custom'
        ));

        $this->assertResponseStatus(302);
        $this->assertRedirectedTo('/admin/promotions/create');
        $this->assertSessionHasErrors('name');
        $this->assertErrorMessage('name', 'The name field is required.');
        $this->assertErrorBagIsDefaultOnly();
    }

    /**
     * Test (Pass): create writes one cn translation row with exact decoded content
     */
    public function testCharacterizationTranslationCreated()
    {
        $this->postStore(array(
            'name' => 'Translated name',
            'short_description' => 'Translated short',
            'long_description' => 'Translated long',
            'start_date' => '29/02/2016',
            'end_date' => '01/03/2016',
            'cn_name' => 'CN name',
            'cn_short_description' => 'CN short',
            'cn_long_description' => 'CN long'
        ));

        $this->assertRedirectedTo('/admin/promotions');

        $this->assertTranslations(Promotion::first(), array(
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
            'short_description' => 'Short',
            'long_description' => 'Long',
            'start_date' => '29/02/2016',
            'end_date' => '01/03/2016',
            'cn_name' => 'CN name',
            'cn_short_description' => 'CN short',
            'cn_long_description' => 'CN long'
        ));

        $promotion = Promotion::first();
        $translation = $promotion->translations()->first();
        $translation->content = json_encode(array(
            'name' => 'pre-existing',
            'short_description' => 'pre-existing short',
            'long_description' => 'pre-existing long'
        ));
        $translation->save();

        $this->postStore(array(
            'id' => $promotion->id,
            'name' => 'Translation owner',
            'short_description' => 'Short',
            'long_description' => 'Long',
            'start_date' => '29/02/2016',
            'end_date' => '01/03/2016',
            'cn_name' => 'Updated CN name',
            'cn_short_description' => 'Updated CN short',
            'cn_long_description' => 'Updated CN long'
        ));

        $this->assertRedirectedTo('/admin/promotions');
        $this->assertSame(1, Promotion::count());

        $translations = $this->assertTranslations(Promotion::find($promotion->id), array(
            'cn' => array(
                'name' => 'Updated CN name',
                'short_description' => 'Updated CN short',
                'long_description' => 'Updated CN long'
            )
        ));

        $this->assertSame($translation->id, $translations->first()->id);
    }

    /**
     * Test (Pass): missing translated inputs are stored as nulls
     */
    public function testCharacterizationMissingTranslatedInputsStoreNull()
    {
        $this->postStore(array(
            'name' => 'No translations',
            'short_description' => 'Short',
            'long_description' => 'Long',
            'start_date' => '29/02/2016',
            'end_date' => '01/03/2016'
        ));

        $this->assertRedirectedTo('/admin/promotions');

        $this->assertTranslations(Promotion::first(), array(
            'cn' => array(
                'name' => null,
                'short_description' => null,
                'long_description' => null
            )
        ));
    }

    /**
     * Test (Pass): getEdit passes promotion, translated, start/end DateTime and imagine view data
     */
    public function testCharacterizationGetEditViewData()
    {
        $promotion = $this->createModel(Promotion::class, array(
            'name' => 'View name',
            'short_description' => 'View short',
            'long_description' => 'View long',
            'start_date' => '2016-02-29 00:00:00',
            'end_date' => '2016-03-01 00:00:00'
        ));
        $translation = new Translation;
        $translation->lang = 'cn';
        $translation->content = json_encode(array(
            'name' => 'View CN name',
            'short_description' => 'View CN short',
            'long_description' => 'View CN long'
        ));
        $promotion->translations()->save($translation);

        $this->call('GET', '/admin/promotions/edit/' . $promotion->id);

        $this->assertResponseOk();
        $this->assertViewHas('promotion', function ($value) use ($promotion) {
            return $value instanceof Promotion && $value->id == $promotion->id;
        });
        $this->assertViewHas('translated', function ($value) {
            return isset($value['cn']) && $value['cn']->name === 'View CN name';
        });
        $this->assertViewHas('start_date', function ($value) {
            return $value instanceof \DateTime;
        });
        $this->assertViewHas('end_date', function ($value) {
            return $value instanceof \DateTime;
        });
        $this->assertViewHas('imagine');
    }

    /**
     * Test (Fail): getEdit for a nonexistent id uses the lowercase not-found message
     */
    public function testCharacterizationGetEditMissing()
    {
        $this->call('GET', '/admin/promotions/edit/999');

        $this->assertRedirectedTo('/admin/promotions');
        $this->assertErrorMessage(
            'editError',
            'The promotion cannot be found because it does not exist or may have been deleted.'
        );
    }

    /**
     * Test (Fail): an image owned by another model cannot be removed through promotions
     */
    public function testCharacterizationImgremoveWrongOwner()
    {
        $category = $this->createModel(Category::class, array(
            'name' => 'Promotion image owner ' . uniqid(),
            'short_description' => 'Short description'
        ));
        $image = $this->attachImage($category);

        $this->call('POST', '/admin/promotions/imgremove/' . $image->id);

        $this->assertRedirectedTo('/admin/promotions');
        $this->assertErrorMessage('deleteError', 'The image cannot be deleted at this time.');
        $this->assertNotNull(Image::find($image->id));
    }

    /**
     * Test (Fail): a nonexistent image id gives the deleteError message
     */
    public function testCharacterizationImgremoveMissing()
    {
        $this->call('POST', '/admin/promotions/imgremove/999999');

        $this->assertRedirectedTo('/admin/promotions');
        $this->assertErrorMessage('deleteError', 'The image cannot be deleted at this time.');
    }

    /**
     * Test (Fail): a non-image upload fails the mimes rule with the exact message
     */
    public function testCharacterizationInvalidImageMime()
    {
        $this->assertValidationFailure(
            '/admin/promotions/create',
            array(
                'name' => 'Bad image',
                'short_description' => 'Short',
                'long_description' => 'Long',
                'start_date' => '29/02/2016',
                'end_date' => '01/03/2016'
            ),
            array('image' => 'The image field must be a file of type: jpg, jpeg, png, gif.'),
            array('image' => $this->invalidUploadedFile())
        );

        $this->assertSame(0, Promotion::count());
        $this->assertSame(0, Image::count());
    }

    /**
     * Test (Pass): two files in images[] attach two Image rows
     */
    public function testCharacterizationImagesArrayAttachesTwoRows()
    {
        $this->postStore(
            array(
                'name' => 'Image name',
                'short_description' => 'Short',
                'long_description' => 'Long',
                'start_date' => '29/02/2016',
                'end_date' => '01/03/2016'
            ),
            array('images' => array(
                $this->uploadedImage('first.png'),
                $this->uploadedImage('second.png')
            ))
        );

        $this->assertRedirectedTo('/admin/promotions');
        $this->assertSame(1, Promotion::count());
        $this->assertSame(2, Image::count());

        $promotion = Promotion::first();
        $this->assertSame(2, $promotion->images()->count());
        $this->assertSame(
            array(1, 2),
            array_map('intval', Image::orderBy('id')->pluck('sort_order')->all())
        );
        $this->assertSame(Promotion::class, Image::first()->imageable_type);
        $this->assertSame($promotion->id, Image::first()->imageable_id);
    }

    /**
     * Test (Pass): the legacy single image field attaches one Image row
     */
    public function testCharacterizationLegacySingleImageAttachesOneRow()
    {
        $this->postStore(
            array(
                'name' => 'Legacy image',
                'short_description' => 'Short',
                'long_description' => 'Long',
                'start_date' => '29/02/2016',
                'end_date' => '01/03/2016'
            ),
            array('image' => $this->uploadedImage('legacy.png'))
        );

        $this->assertRedirectedTo('/admin/promotions');
        $this->assertSame(1, Promotion::count());
        $this->assertSame(1, Image::count());
        $this->assertSame(Promotion::class, Image::first()->imageable_type);
    }

    /**
     * Test (Fail): a validation failure creates no Image rows
     */
    public function testCharacterizationValidationFailureCreatesNoImages()
    {
        $this->assertValidationFailure(
            '/admin/promotions/create',
            array(
                'short_description' => 'Short',
                'long_description' => 'Long',
                'start_date' => '29/02/2016',
                'end_date' => '01/03/2016'
            ),
            array('name' => 'The name field is required.'),
            array('images' => array($this->uploadedImage('not-saved.png')))
        );

        $this->assertSame(0, Promotion::count());
        $this->assertSame(0, Image::count());
    }
}