<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Category;
use Redooor\Redminportal\App\Models\Image;
use Redooor\Redminportal\App\Models\Module;
use Redooor\Redminportal\App\Models\Translation;

/**
 * Pins the behaviour before the controller refactor; do not edit to make a refactor pass.
 */
class CategoryCharacterizationTest extends BaseControllerTest
{
    use CharacterizationSupport;

    private $relativeFolder;

    public function setUp(): void
    {
        parent::setUp();

        $this->page = '/admin/categories';
        $this->viewhas = array(
            'singular' => 'category',
            'plural' => 'categories'
        );
        $this->input = array(
            'create' => array(
                'name' => 'This is a name',
                'short_description' => 'This is short description',
                'long_description' => 'This is long description',
                'active' => true,
                'order' => 1,
                'parent_id' => 1,
                'cn_name' => 'This is cn name',
                'cn_short_description' => 'This is cn short description',
                'cn_long_description' => 'This is cn long description'
            ),
            'edit' => array(
                'id' => 1,
                'name' => 'This is a name',
                'short_description' => 'This is short description',
                'long_description' => 'This is long description',
                'active' => true,
                'order' => 1,
                'parent_id' => 1,
                'cn_name' => 'This is cn name',
                'cn_short_description' => 'This is cn short description',
                'cn_long_description' => 'This is cn long description'
            )
        );

        $this->relativeFolder = null;

        $this->configureImageUploads();
    }

    public function tearDown(): void
    {
        $this->cleanupImageUploads();

        if ($this->relativeFolder) {
            $this->deleteFolder($this->relativeFolder);
            @rmdir(dirname($this->relativeFolder));
            @rmdir(dirname(dirname($this->relativeFolder)));
            @rmdir(dirname(dirname(dirname($this->relativeFolder))));
        }

        $this->relativeFolder = null;

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
            'order' => '3',
            'parent_id' => '1',
            'active' => '1'
        ));

        $this->assertRedirectedTo('/admin/categories');
        $this->assertSame(1, Category::count());

        $category = Category::first();
        $this->assertSame('Original name', $category->name);
        $this->assertSame('Original short', $category->short_description);
        $this->assertSame('Original long', $category->long_description);
        $this->assertSame(3, (int) $category->order);
        $this->assertSame(1, (int) $category->category_id);
        $this->assertSame(1, (int) $category->active);

        $this->postStore(array(
            'id' => $category->id,
            'name' => 'Updated name',
            'short_description' => 'Updated short',
            'long_description' => 'Updated long',
            'order' => '7',
            'parent_id' => '1',
            'active' => ''
        ));

        $this->assertRedirectedTo('/admin/categories');
        $this->assertSame(1, Category::count());

        $category = Category::first();
        $this->assertSame('Updated name', $category->name);
        $this->assertSame('Updated short', $category->short_description);
        $this->assertSame('Updated long', $category->long_description);
        $this->assertSame(7, (int) $category->order);
        $this->assertSame(0, (int) $category->active);
    }

    /**
     * Test (Fail): invalid input with no id redirects to create
     */
    public function testCharacterizationValidationRedirectWhenIdOmitted()
    {
        $this->assertValidationFailure(
            '/admin/categories/create',
            array('short_description' => 'Short', 'order' => 1),
            array('name' => 'The name field is required.')
        );
    }

    /**
     * Test (Fail): invalid input with a nonexistent id redirects to edit/5 before the not-found check
     */
    public function testCharacterizationValidationRedirectWhenIdIsFive()
    {
        $this->assertValidationFailure(
            '/admin/categories/edit/5',
            array('id' => '5', 'short_description' => 'Short', 'order' => 1),
            array('name' => 'The name field is required.')
        );
    }

    /**
     * Test (Fail): an empty id is converted to null by the HTTP middleware, so it redirects to create
     */
    public function testCharacterizationValidationRedirectWhenIdIsEmptyString()
    {
        $this->assertValidationFailure(
            '/admin/categories/create',
            array('id' => '', 'short_description' => 'Short', 'order' => 1),
            array('name' => 'The name field is required.')
        );
    }

    /**
     * Test (Fail): an id of zero is present and redirects to edit/0
     */
    public function testCharacterizationValidationRedirectWhenIdIsZero()
    {
        $this->assertValidationFailure(
            '/admin/categories/edit/0',
            array('id' => '0', 'short_description' => 'Short', 'order' => 1),
            array('name' => 'The name field is required.')
        );
    }

    /**
     * Test (Fail): invalid input with an existing id redirects to edit/<id>
     */
    public function testCharacterizationValidationRedirectWhenIdExists()
    {
        $category = $this->createModel(Category::class, array(
            'name' => 'Existing category',
            'short_description' => 'Existing short',
            'order' => 1
        ));

        $this->assertValidationFailure(
            '/admin/categories/edit/' . $category->id,
            array('id' => $category->id, 'short_description' => 'Short', 'order' => 1),
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
            'order' => 1
        ));

        $this->assertRedirectedTo('/admin/categories');
        $this->assertErrorMessage(
            'editError',
            'The category cannot be edited because it does not exist or may have been deleted.'
        );
        $this->assertSame(0, Category::count());
    }

    /**
     * Test (Fail): name is required
     */
    public function testCharacterizationMissingName()
    {
        $this->assertValidationFailure(
            '/admin/categories/create',
            array('short_description' => 'Short', 'order' => 1),
            array('name' => 'The name field is required.')
        );
    }

    /**
     * Test (Fail): short_description is required
     */
    public function testCharacterizationMissingShortDescription()
    {
        $this->assertValidationFailure(
            '/admin/categories/create',
            array('name' => 'Valid name', 'order' => 1),
            array('short_description' => 'The short description field is required.')
        );
    }

    /**
     * Test (Fail): order is required
     */
    public function testCharacterizationMissingOrder()
    {
        $this->assertValidationFailure(
            '/admin/categories/create',
            array('name' => 'Valid name', 'short_description' => 'Short'),
            array('order' => 'The order field is required.')
        );
    }

    /**
     * Test (Pass): min:0 compares the string length without a numeric rule, so -1 passes
     */
    public function testCharacterizationNegativeOrderPassesValidation()
    {
        $this->postStore(array(
            'name' => 'Negative order',
            'short_description' => 'Short',
            'order' => '-1'
        ));

        $this->assertRedirectedTo('/admin/categories');
        $this->assertSame(-1, (int) Category::first()->order);
    }

    /**
     * Test (Fail): old input keeps scalar values but never an uploaded file
     */
    public function testCharacterizationOldInputWithFile()
    {
        $this->assertValidationFailure(
            '/admin/categories/create',
            array('short_description' => 'Short', 'order' => '4'),
            array('name' => 'The name field is required.'),
            array('images' => array($this->uploadedImage('old-input.png')))
        );

        $this->assertOldInput(array('short_description' => 'Short', 'order' => '4'));
        $this->assertSame(0, Image::count());
    }

    /**
     * Test (Fail): a JSON Accept header still redirects with session errors in the default bag
     */
    public function testCharacterizationJsonRequestStillRedirectsWithDefaultBag()
    {
        $this->postStoreAsJson(array(
            'short_description' => 'Short',
            'order' => 1,
            '_error_bag' => 'custom'
        ));

        $this->assertResponseStatus(302);
        $this->assertRedirectedTo('/admin/categories/create');
        $this->assertSessionHasErrors('name');
        $this->assertErrorMessage('name', 'The name field is required.');
        $this->assertErrorBagIsDefaultOnly();
    }

    /**
     * Test (Pass): create writes one cn translation row with the exact decoded content
     */
    public function testCharacterizationTranslationCreated()
    {
        $this->postStore(array(
            'name' => 'Translated category',
            'short_description' => 'Translated short',
            'long_description' => 'Translated long',
            'order' => 1,
            'cn_name' => 'CN name',
            'cn_short_description' => 'CN short',
            'cn_long_description' => 'CN long'
        ));

        $this->assertRedirectedTo('/admin/categories');

        $this->assertTranslations(Category::first(), array(
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
            'short_description' => 'Translated short',
            'long_description' => 'Translated long',
            'order' => 1,
            'cn_name' => 'CN name',
            'cn_short_description' => 'CN short',
            'cn_long_description' => 'CN long'
        ));

        $category = Category::first();
        $translation = $category->translations()->first();
        $translation->content = json_encode(array(
            'name' => 'pre-existing',
            'short_description' => 'pre-existing short',
            'long_description' => 'pre-existing long'
        ));
        $translation->save();

        $this->postStore(array(
            'id' => $category->id,
            'name' => 'Translation owner',
            'short_description' => 'Translated short',
            'long_description' => 'Translated long',
            'order' => 1,
            'cn_name' => 'Updated CN name',
            'cn_short_description' => 'Updated CN short',
            'cn_long_description' => 'Updated CN long'
        ));

        $this->assertRedirectedTo('/admin/categories');
        $this->assertSame(1, Category::count());

        $translations = $this->assertTranslations(Category::find($category->id), array(
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
            'short_description' => 'Short',
            'order' => 1
        ));

        $this->assertRedirectedTo('/admin/categories');

        $this->assertTranslations(Category::first(), array(
            'cn' => array(
                'name' => null,
                'short_description' => null,
                'long_description' => null
            )
        ));
    }

    /**
     * Test (Fail): the same name under the same parent is rejected after validation
     */
    public function testCharacterizationSameNameSameParentRejected()
    {
        $this->createModel(Category::class, array(
            'name' => 'Sibling name',
            'short_description' => 'Existing short',
            'order' => 1
        ));

        $this->postStore(array(
            'name' => 'Sibling name',
            'short_description' => 'New short',
            'order' => 1
        ));

        $this->assertRedirectedTo('/admin/categories/create');
        $this->assertErrorMessage(
            'nameError',
            "The category cannot be added because there's an existing category with the same name."
        );
        $this->assertSame(1, Category::count());
        $this->assertOldInput(array('name' => 'Sibling name'));
    }

    /**
     * Test (Pass): the same name under another parent is allowed
     */
    public function testCharacterizationSameNameDifferentParentAllowed()
    {
        $this->createModel(Category::class, array(
            'name' => 'Shared name',
            'short_description' => 'Existing short',
            'order' => 1
        ));

        $this->postStore(array(
            'name' => 'Shared name',
            'short_description' => 'New short',
            'order' => 1,
            'parent_id' => '1'
        ));

        $this->assertRedirectedTo('/admin/categories');
        $this->assertSame(2, Category::count());
        $this->assertSame(1, (int) Category::where('name', 'Shared name')->orderByDesc('id')->first()->category_id);
    }

    /**
     * Test (Pass): parent id omitted, empty or zero is stored as null; a valid parent id is kept
     */
    public function testCharacterizationParentNormalisation()
    {
        $parent = $this->createModel(Category::class, array(
            'name' => 'Root parent',
            'short_description' => 'Short',
            'order' => 1
        ));

        foreach (array(
            array('name' => 'Omitted parent', 'parent_id' => null),
            array('name' => 'Empty parent', 'parent_id' => ''),
            array('name' => 'Zero parent', 'parent_id' => '0'),
        ) as $item) {
            $input = array(
                'name' => $item['name'],
                'short_description' => 'Short',
                'order' => 1
            );

            if ($item['parent_id'] !== null) {
                $input['parent_id'] = $item['parent_id'];
            }

            $this->postStore($input);
            $this->assertRedirectedTo('/admin/categories');
            $this->assertNull(Category::where('name', $item['name'])->first()->category_id);
        }

        $this->postStore(array(
            'name' => 'Valid parent',
            'short_description' => 'Short',
            'order' => 1,
            'parent_id' => (string) $parent->id
        ));

        $this->assertRedirectedTo('/admin/categories');
        $this->assertSame(
            $parent->id,
            (int) Category::where('name', 'Valid parent')->first()->category_id
        );
    }

    /**
     * Test (Pass): getEdit passes category, translated, imagine and categories view data
     */
    public function testCharacterizationGetEditViewData()
    {
        $category = $this->createModel(Category::class, array(
            'name' => 'View category',
            'short_description' => 'View short',
            'order' => 1
        ));
        $translation = new Translation;
        $translation->lang = 'cn';
        $translation->content = json_encode(array(
            'name' => 'View CN name',
            'short_description' => 'View CN short',
            'long_description' => 'View CN long'
        ));
        $category->translations()->save($translation);

        $this->call('GET', '/admin/categories/edit/' . $category->id);

        $this->assertResponseOk();
        $this->assertViewHas('category', function ($value) use ($category) {
            return $value instanceof Category && $value->id == $category->id;
        });
        $this->assertViewHas('translated', function ($value) {
            return isset($value['cn']) && $value['cn']->name === 'View CN name';
        });
        $this->assertViewHas('imagine');
        $this->assertViewHas('categories');
    }

    /**
     * Test (Fail): getEdit for a nonexistent id uses the lowercase not-found message
     */
    public function testCharacterizationGetEditMissing()
    {
        $this->call('GET', '/admin/categories/edit/999');

        $this->assertRedirectedTo('/admin/categories');
        $this->assertErrorMessage(
            'editError',
            'The category cannot be edited because it does not exist or may have been deleted.'
        );
    }

    /**
     * Test (Fail): an image owned by another model cannot be removed through categories
     */
    public function testCharacterizationImgremoveWrongOwner()
    {
        $module = $this->createModel(Module::class, array(
            'name' => 'Category image owner ' . uniqid(),
            'sku' => 'CATIMG' . uniqid(),
            'short_description' => 'Short description',
            'category_id' => 1
        ));
        $image = $this->attachImage($module);

        $this->call('POST', '/admin/categories/imgremove/' . $image->id);

        $this->assertRedirectedTo('/admin/categories');
        $this->assertErrorMessage('deleteError', 'The image cannot be deleted at this time.');
        $this->assertNotNull(Image::find($image->id));
    }

    /**
     * Test (Fail): a nonexistent image id gives the deleteError message
     */
    public function testCharacterizationImgremoveMissing()
    {
        $this->call('POST', '/admin/categories/imgremove/999999');

        $this->assertRedirectedTo('/admin/categories');
        $this->assertErrorMessage('deleteError', 'The image cannot be deleted at this time.');
    }

    /**
     * Test (Fail): a non-image upload fails the mimes rule with the exact message
     */
    public function testCharacterizationInvalidImageMime()
    {
        $this->assertValidationFailure(
            '/admin/categories/create',
            array('name' => 'Bad image', 'short_description' => 'Short', 'order' => 1),
            array('image' => 'The image field must be a file of type: jpg, jpeg, png, gif.'),
            array('image' => $this->invalidUploadedFile())
        );

        $this->assertSame(0, Category::count());
        $this->assertSame(0, Image::count());
    }

    /**
     * Test (Pass): two files in images[] attach two Image rows
     */
    public function testCharacterizationImagesArrayAttachesTwoRows()
    {
        $this->postStore(
            array('name' => 'Image category', 'short_description' => 'Short', 'order' => 1),
            array('images' => array(
                $this->uploadedImage('first.png'),
                $this->uploadedImage('second.png')
            ))
        );

        $this->assertRedirectedTo('/admin/categories');
        $this->assertSame(1, Category::count());
        $this->assertSame(2, Image::count());

        $category = Category::first();
        $this->assertSame(2, $category->images()->count());
        $this->assertSame(
            array(1, 2),
            array_map('intval', Image::orderBy('id')->pluck('sort_order')->all())
        );
        $this->assertSame(Category::class, Image::first()->imageable_type);
        $this->assertSame($category->id, Image::first()->imageable_id);
    }

    /**
     * Test (Pass): the legacy single image field attaches one Image row
     */
    public function testCharacterizationLegacySingleImageAttachesOneRow()
    {
        $this->postStore(
            array('name' => 'Legacy image', 'short_description' => 'Short', 'order' => 1),
            array('image' => $this->uploadedImage('legacy.png'))
        );

        $this->assertRedirectedTo('/admin/categories');
        $this->assertSame(1, Category::count());
        $this->assertSame(1, Image::count());
        $this->assertSame(Category::class, Image::first()->imageable_type);
    }

    /**
     * Test (Fail): a validation failure creates no Image rows
     */
    public function testCharacterizationValidationFailureCreatesNoImages()
    {
        $this->assertValidationFailure(
            '/admin/categories/create',
            array('short_description' => 'Short', 'order' => 1),
            array('name' => 'The name field is required.'),
            array('images' => array($this->uploadedImage('not-saved.png')))
        );

        $this->assertSame(0, Category::count());
        $this->assertSame(0, Image::count());
    }

    /**
     * Test (Fail): a category with children cannot be deleted and its errors name the category
     */
    public function testCharacterizationGetDeleteWithChildren()
    {
        $parent = $this->createModel(Category::class, array(
            'name' => 'Parent category',
            'short_description' => 'Short',
            'order' => 1
        ));
        $this->createModel(Category::class, array(
            'name' => 'Child category',
            'short_description' => 'Short',
            'order' => 1,
            'category_id' => $parent->id
        ));
        $image = $this->attachImage($parent);

        $this->call('POST', '/admin/categories/delete/' . $parent->id, array(), array(), array(), array(
            'HTTP_REFERER' => '/admin/categories'
        ));

        $this->assertRedirectedTo('/admin/categories');
        $this->assertErrorMessage(
            'deleteError',
            "The category 'Parent category' cannot be deleted because it has 1 children categories."
        );
        $this->assertSame(2, Category::count());
        $this->assertNotNull(Category::find($parent->id));
        $this->assertNotNull(Image::find($image->id));
    }

    /**
     * Test (Pass): a childless category is deleted with its Image rows and its configured asset folder
     */
    public function testCharacterizationGetDeleteChildless()
    {
        $category = $this->createModel(Category::class, array(
            'name' => 'Deletable category',
            'short_description' => 'Short',
            'order' => 1
        ));
        $image = $this->attachImage($category, 'categories/' . $category->id . '/dummy.png');

        $configuredFolder = $this->characterizationUploadPath . 'categories/' . $category->id;
        mkdir($configuredFolder, 0777, true);
        file_put_contents($configuredFolder . '/dummy.png', 'configured');

        $this->relativeFolder = getcwd() . '/assets/img/categories/' . $category->id;
        mkdir($this->relativeFolder, 0777, true);
        file_put_contents($this->relativeFolder . '/relative.png', 'relative');

        $this->call('POST', '/admin/categories/delete/' . $category->id, array(), array(), array(), array(
            'HTTP_REFERER' => '/admin/categories'
        ));

        $this->assertRedirectedTo('/admin/categories');
        $this->assertSame(0, Category::count());
        $this->assertSame(0, Image::count());
        $this->assertFileDoesNotExist($configuredFolder . '/dummy.png');
        $this->assertDirectoryDoesNotExist($configuredFolder);
        $this->assertFileExists($this->relativeFolder . '/relative.png');
    }

    /**
     * Test (Pass): getDetail passes category and imagine view data
     */
    public function testCharacterizationGetDetailViewData()
    {
        $category = $this->createModel(Category::class, array(
            'name' => 'Detail category',
            'short_description' => 'Detail short',
            'order' => 1
        ));

        $this->call('GET', '/admin/categories/detail/' . $category->id);

        $this->assertResponseOk();
        $this->assertViewHas('category', function ($value) use ($category) {
            return $value instanceof Category && $value->id == $category->id;
        });
        $this->assertViewHas('imagine');
    }

    /**
     * Test (Fail): getDetail for a missing id renders the 404 view with a 404 response
     */
    public function testCharacterizationGetDetailMissing()
    {
        $this->call('GET', '/admin/categories/detail/999');

        $this->assertResponseStatus(404);
        $this->response->assertViewIs('redminportal::pages.404');
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
