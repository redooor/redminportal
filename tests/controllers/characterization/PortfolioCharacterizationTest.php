<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Category;
use Redooor\Redminportal\App\Models\Image;
use Redooor\Redminportal\App\Models\Portfolio;
use Redooor\Redminportal\App\Models\Translation;

/**
 * Pins the behaviour before the controller refactor; do not edit to make a refactor pass.
 */
class PortfolioCharacterizationTest extends BaseControllerTest
{
    use CharacterizationSupport;

    public function setUp(): void
    {
        parent::setUp();

        $this->page = '/admin/portfolios';
        $this->viewhas = array(
            'singular' => 'portfolio',
            'plural' => 'models'
        );
        $this->input = array(
            'create' => array(
                'name' => 'This is title',
                'short_description' => 'This is body',
                'long_description' => 'This is long body',
                'category_id' => 1,
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
                'category_id' => 1,
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
            'category_id' => '1',
            'active' => '1'
        ));

        $this->assertRedirectedTo('/admin/portfolios');
        $this->assertSame(1, Portfolio::count());

        $portfolio = Portfolio::first();
        $this->assertSame('Original name', $portfolio->name);
        $this->assertSame('Original short', $portfolio->short_description);
        $this->assertSame('Original long', $portfolio->long_description);
        $this->assertSame(1, (int) $portfolio->category_id);
        $this->assertSame(1, (int) $portfolio->active);

        $this->postStore(array(
            'id' => $portfolio->id,
            'name' => 'Updated name',
            'short_description' => 'Updated short',
            'long_description' => 'Updated long',
            'category_id' => '1',
            'active' => ''
        ));

        $this->assertRedirectedTo('/admin/portfolios');
        $this->assertSame(1, Portfolio::count());

        $portfolio = Portfolio::first();
        $this->assertSame('Updated name', $portfolio->name);
        $this->assertSame('Updated short', $portfolio->short_description);
        $this->assertSame('Updated long', $portfolio->long_description);
        $this->assertSame(0, (int) $portfolio->active);
    }

    /**
     * Test (Fail): invalid input with no id redirects to create
     */
    public function testCharacterizationValidationRedirectWhenIdOmitted()
    {
        $this->assertValidationFailure(
            '/admin/portfolios/create',
            array('short_description' => 'Short', 'category_id' => 1),
            array('name' => 'The name field is required.')
        );
    }

    /**
     * Test (Fail): invalid input with a nonexistent id redirects to edit/5 before the not-found check
     */
    public function testCharacterizationValidationRedirectWhenIdIsFive()
    {
        $this->assertValidationFailure(
            '/admin/portfolios/edit/5',
            array('id' => '5', 'short_description' => 'Short', 'category_id' => 1),
            array('name' => 'The name field is required.')
        );
    }

    /**
     * Test (Fail): an empty id is converted to null by the HTTP middleware, so it redirects to create
     */
    public function testCharacterizationValidationRedirectWhenIdIsEmptyString()
    {
        $this->assertValidationFailure(
            '/admin/portfolios/create',
            array('id' => '', 'short_description' => 'Short', 'category_id' => 1),
            array('name' => 'The name field is required.')
        );
    }

    /**
     * Test (Fail): an id of zero is present and redirects to edit/0
     */
    public function testCharacterizationValidationRedirectWhenIdIsZero()
    {
        $this->assertValidationFailure(
            '/admin/portfolios/edit/0',
            array('id' => '0', 'short_description' => 'Short', 'category_id' => 1),
            array('name' => 'The name field is required.')
        );
    }

    /**
     * Test (Fail): invalid input with an existing id redirects to edit/<id>
     */
    public function testCharacterizationValidationRedirectWhenIdExists()
    {
        $portfolio = $this->createModel(Portfolio::class, array(
            'name' => 'Existing name',
            'short_description' => 'Existing short',
            'category_id' => 1
        ));

        $this->assertValidationFailure(
            '/admin/portfolios/edit/' . $portfolio->id,
            array('id' => $portfolio->id, 'short_description' => 'Short', 'category_id' => 1),
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
            'category_id' => 1
        ));

        $this->assertRedirectedTo('/admin/portfolios');
        $this->assertErrorMessage(
            'editError',
            'The portfolio cannot be found because it does not exist or may have been deleted.'
        );
        $this->assertSame(0, Portfolio::count());
    }

    /**
     * Test (Fail): name is required
     */
    public function testCharacterizationMissingName()
    {
        $this->assertValidationFailure(
            '/admin/portfolios/create',
            array('short_description' => 'Short', 'category_id' => 1),
            array('name' => 'The name field is required.')
        );
    }

    /**
     * Test (Fail): short_description is required
     */
    public function testCharacterizationMissingShortDescription()
    {
        $this->assertValidationFailure(
            '/admin/portfolios/create',
            array('name' => 'Name', 'category_id' => 1),
            array('short_description' => 'The short description field is required.')
        );
    }

    /**
     * Test (Fail): category_id is required
     */
    public function testCharacterizationMissingCategoryId()
    {
        $this->assertValidationFailure(
            '/admin/portfolios/create',
            array('name' => 'Name', 'short_description' => 'Short'),
            array('category_id' => 'The category id field is required.')
        );
    }

    /**
     * Test (Fail): category_id zero uses the custom minimum message
     */
    public function testCharacterizationCategoryIdZero()
    {
        $this->assertValidationFailure(
            '/admin/portfolios/create',
            array('name' => 'Name', 'short_description' => 'Short', 'category_id' => '0'),
            array('category_id' => 'The category field is required.')
        );
    }

    /**
     * Test (Fail): a non-numeric category_id fails the numeric rule
     */
    public function testCharacterizationCategoryIdNonNumeric()
    {
        $this->assertValidationFailure(
            '/admin/portfolios/create',
            array('name' => 'Name', 'short_description' => 'Short', 'category_id' => 'abc'),
            array('category_id' => 'The category id field must be a number.')
        );
    }

    /**
     * Test (Fail): old input keeps scalar values but never an uploaded file
     */
    public function testCharacterizationOldInputWithFile()
    {
        $this->assertValidationFailure(
            '/admin/portfolios/create',
            array('short_description' => 'Short', 'category_id' => 1),
            array('name' => 'The name field is required.'),
            array('images' => array($this->uploadedImage('old-input.png')))
        );

        $this->assertOldInput(array('short_description' => 'Short', 'category_id' => 1));
        $this->assertSame(0, Image::count());
    }

    /**
     * Test (Fail): a JSON Accept header still redirects with session errors in the default bag
     */
    public function testCharacterizationJsonRequestStillRedirectsWithDefaultBag()
    {
        $this->postStoreAsJson(array(
            'short_description' => 'Short',
            'category_id' => 1,
            '_error_bag' => 'custom'
        ));

        $this->assertResponseStatus(302);
        $this->assertRedirectedTo('/admin/portfolios/create');
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
            'category_id' => 1,
            'cn_name' => 'CN name',
            'cn_short_description' => 'CN short',
            'cn_long_description' => 'CN long'
        ));

        $this->assertRedirectedTo('/admin/portfolios');

        $this->assertTranslations(Portfolio::first(), array(
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
            'category_id' => 1,
            'cn_name' => 'CN name',
            'cn_short_description' => 'CN short',
            'cn_long_description' => 'CN long'
        ));

        $portfolio = Portfolio::first();
        $translation = $portfolio->translations()->first();
        $translation->content = json_encode(array(
            'name' => 'pre-existing',
            'short_description' => 'pre-existing short',
            'long_description' => 'pre-existing long'
        ));
        $translation->save();

        $this->postStore(array(
            'id' => $portfolio->id,
            'name' => 'Translation owner',
            'short_description' => 'Short',
            'category_id' => 1,
            'cn_name' => 'Updated CN name',
            'cn_short_description' => 'Updated CN short',
            'cn_long_description' => 'Updated CN long'
        ));

        $this->assertRedirectedTo('/admin/portfolios');
        $this->assertSame(1, Portfolio::count());

        $translations = $this->assertTranslations(Portfolio::find($portfolio->id), array(
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
            'category_id' => 1
        ));

        $this->assertRedirectedTo('/admin/portfolios');

        $this->assertTranslations(Portfolio::first(), array(
            'cn' => array(
                'name' => null,
                'short_description' => null,
                'long_description' => null
            )
        ));
    }

    /**
     * Test (Pass): getEdit passes portfolio, translated, imagine and categories view data
     */
    public function testCharacterizationGetEditViewData()
    {
        $portfolio = $this->createModel(Portfolio::class, array(
            'name' => 'View name',
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
        $portfolio->translations()->save($translation);

        $this->call('GET', '/admin/portfolios/edit/' . $portfolio->id);

        $this->assertResponseOk();
        $this->assertViewHas('portfolio', function ($value) use ($portfolio) {
            return $value instanceof Portfolio && $value->id == $portfolio->id;
        });
        $this->assertViewHas('translated', function ($value) {
            return isset($value['cn'])
                && $value['cn']->name === 'View CN name'
                && $value['cn']->short_description === 'View CN short'
                && $value['cn']->long_description === 'View CN long';
        });
        $this->assertViewHas('imagine');
        $this->assertViewHas('categories');
    }

    /**
     * Test (Fail): getEdit for a nonexistent id uses the lowercase not-found message
     */
    public function testCharacterizationGetEditMissing()
    {
        $this->call('GET', '/admin/portfolios/edit/999');

        $this->assertRedirectedTo('/admin/portfolios');
        $this->assertErrorMessage(
            'editError',
            'The portfolio cannot be found because it does not exist or may have been deleted.'
        );
    }

    /**
     * Test (Fail): an image owned by another model cannot be removed through portfolios
     */
    public function testCharacterizationImgremoveWrongOwner()
    {
        $category = $this->createModel(Category::class, array(
            'name' => 'Portfolio image owner ' . uniqid(),
            'short_description' => 'Short description'
        ));
        $image = $this->attachImage($category);

        $this->call('POST', '/admin/portfolios/imgremove/' . $image->id);

        $this->assertRedirectedTo('/admin/portfolios');
        $this->assertErrorMessage('deleteError', 'The image cannot be deleted at this time.');
        $this->assertNotNull(Image::find($image->id));
    }

    /**
     * Test (Fail): a nonexistent image id gives the deleteError message
     */
    public function testCharacterizationImgremoveMissing()
    {
        $this->call('POST', '/admin/portfolios/imgremove/999999');

        $this->assertRedirectedTo('/admin/portfolios');
        $this->assertErrorMessage('deleteError', 'The image cannot be deleted at this time.');
    }

    /**
     * Test (Fail): a non-image upload fails the mimes rule with the exact message
     */
    public function testCharacterizationInvalidImageMime()
    {
        $this->assertValidationFailure(
            '/admin/portfolios/create',
            array('name' => 'Bad image', 'short_description' => 'Short', 'category_id' => 1),
            array('image' => 'The image field must be a file of type: jpg, jpeg, png, gif.'),
            array('image' => $this->invalidUploadedFile())
        );

        $this->assertSame(0, Portfolio::count());
        $this->assertSame(0, Image::count());
    }

    /**
     * Test (Pass): two files in images[] attach two Image rows
     */
    public function testCharacterizationImagesArrayAttachesTwoRows()
    {
        $this->postStore(
            array('name' => 'Image name', 'short_description' => 'Short', 'category_id' => 1),
            array('images' => array(
                $this->uploadedImage('first.png'),
                $this->uploadedImage('second.png')
            ))
        );

        $this->assertRedirectedTo('/admin/portfolios');
        $this->assertSame(1, Portfolio::count());
        $this->assertSame(2, Image::count());

        $portfolio = Portfolio::first();
        $this->assertSame(2, $portfolio->images()->count());
        $this->assertSame(
            array(1, 2),
            array_map('intval', Image::orderBy('id')->pluck('sort_order')->all())
        );
        $this->assertSame(Portfolio::class, Image::first()->imageable_type);
        $this->assertSame($portfolio->id, Image::first()->imageable_id);
    }

    /**
     * Test (Pass): the legacy single image field attaches one Image row
     */
    public function testCharacterizationLegacySingleImageAttachesOneRow()
    {
        $this->postStore(
            array('name' => 'Legacy image', 'short_description' => 'Short', 'category_id' => 1),
            array('image' => $this->uploadedImage('legacy.png'))
        );

        $this->assertRedirectedTo('/admin/portfolios');
        $this->assertSame(1, Portfolio::count());
        $this->assertSame(1, Image::count());
        $this->assertSame(Portfolio::class, Image::first()->imageable_type);
    }

    /**
     * Test (Fail): a validation failure creates no Image rows
     */
    public function testCharacterizationValidationFailureCreatesNoImages()
    {
        $this->assertValidationFailure(
            '/admin/portfolios/create',
            array('short_description' => 'Short', 'category_id' => 1),
            array('name' => 'The name field is required.'),
            array('images' => array($this->uploadedImage('not-saved.png')))
        );

        $this->assertSame(0, Portfolio::count());
        $this->assertSame(0, Image::count());
    }
}
