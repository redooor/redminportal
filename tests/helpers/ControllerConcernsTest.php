<?php namespace Redooor\Redminportal\Test;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Redooor\Redminportal\App\Http\Concerns\PreparesEditData;
use Redooor\Redminportal\App\Http\Concerns\RedirectsNotFound;
use Redooor\Redminportal\App\Http\Concerns\RemovesGalleryImages;
use Redooor\Redminportal\App\Http\Concerns\SavesTranslations;
use Redooor\Redminportal\App\Http\Concerns\SyncsTags;
use Redooor\Redminportal\App\Http\Concerns\ValidatesInput;
use Redooor\Redminportal\App\Http\Controllers\Controller;
use Redooor\Redminportal\App\Http\Validation\InputRules;
use Redooor\Redminportal\App\Models\Category;
use Redooor\Redminportal\App\Models\Image;
use Redooor\Redminportal\App\Models\Page;
use Redooor\Redminportal\App\Models\Tag;
use Redooor\Redminportal\App\Models\Translation;

class ControllerConcernsTest extends RedminTestCase
{
    /**
     * The first non-English language in the package translation config
     */
    private function nonEnglishLanguage()
    {
        foreach (Config::get('redminportal::translation') as $translation) {
            if ($translation['lang'] != 'en') {
                return $translation['lang'];
            }
        }

        $this->fail('The translation config has no non-English language.');
    }

    /**
     * Replace the request used by the Request facade and URL generator
     */
    private function bindInput(array $input)
    {
        $request = Request::create('/', 'POST', $input);

        \Illuminate\Support\Facades\Facade::clearResolvedInstance('request');
        $this->app->instance('request', $request);
        $this->app['url']->setRequest($request);

        return $request;
    }

    private function createPage()
    {
        $page = new Page;
        $page->title = 'Page title';
        $page->slug = 'page-title';
        $page->content = 'Page content';
        $page->save();

        return $page;
    }

    private function createCategory()
    {
        $category = new Category;
        $category->name = 'Category name';
        $category->short_description = 'Short description';
        $category->save();

        return $category;
    }

    private function decodedContent($model, $lang)
    {
        $translation = $model->translations()->where('lang', $lang)->first();

        $this->assertNotNull($translation);

        return json_decode($translation->content, true);
    }

    public function testSaveTranslationsFromInputWithPage()
    {
        $lang = $this->nonEnglishLanguage();
        $page = $this->createPage();

        $this->bindInput(array(
            $lang . '_title' => 'Translated title',
            $lang . '_slug' => 'a b c',
            $lang . '_content' => 'Translated content',
        ));

        $double = new ControllerConcernsTranslationDouble;
        $double->save($page, array('title', 'slug', 'content'), function ($field, $value) {
            return $field == 'slug' ? str_replace(' ', '_', $value) : $value;
        });

        $this->assertSame(1, Translation::count());
        $this->assertSame($lang, $page->translations()->first()->lang);
        $this->assertSame(array(
            'title' => 'Translated title',
            'slug' => 'a_b_c',
            'content' => 'Translated content',
        ), $this->decodedContent($page, $lang));
    }

    public function testSaveTranslationsFromInputSecondCallUpdatesSameRow()
    {
        $lang = $this->nonEnglishLanguage();
        $page = $this->createPage();

        $this->bindInput(array(
            $lang . '_title' => 'First title',
            $lang . '_slug' => 'first slug',
            $lang . '_content' => 'First content',
        ));

        $double = new ControllerConcernsTranslationDouble;
        $double->save($page, array('title', 'slug', 'content'), function ($field, $value) {
            return $field == 'slug' ? str_replace(' ', '_', $value) : $value;
        });

        $firstId = $page->translations()->first()->id;

        $page = Page::find($page->id);

        $this->bindInput(array(
            $lang . '_title' => 'Second title',
            $lang . '_slug' => 'second slug',
            $lang . '_content' => 'Second content',
        ));

        $double->save($page, array('title', 'slug', 'content'), function ($field, $value) {
            return $field == 'slug' ? str_replace(' ', '_', $value) : $value;
        });

        $this->assertSame(1, Translation::count());
        $this->assertSame($firstId, $page->translations()->first()->id);
        $this->assertSame(array(
            'title' => 'Second title',
            'slug' => 'second_slug',
            'content' => 'Second content',
        ), $this->decodedContent($page, $lang));
    }

    public function testSaveTranslationsFromInputMissingInputsBecomeNullAndEmptySlug()
    {
        $lang = $this->nonEnglishLanguage();
        $page = $this->createPage();

        $this->bindInput(array());

        $double = new ControllerConcernsTranslationDouble;
        $double->save($page, array('title', 'slug', 'content'), function ($field, $value) {
            return $field == 'slug' ? str_replace(' ', '_', $value) : $value;
        });

        $this->assertSame(array(
            'title' => null,
            'slug' => '',
            'content' => null,
        ), $this->decodedContent($page, $lang));
    }

    public function testSaveTranslationsFromInputWithoutTransformLeavesValuesUntouched()
    {
        $lang = $this->nonEnglishLanguage();
        $page = $this->createPage();

        $this->bindInput(array(
            $lang . '_title' => 'Translated title',
            $lang . '_slug' => 'a b c',
            $lang . '_content' => 'Translated content',
        ));

        $double = new ControllerConcernsTranslationDouble;
        $double->save($page, array('title', 'slug', 'content'));

        $this->assertSame(array(
            'title' => 'Translated title',
            'slug' => 'a b c',
            'content' => 'Translated content',
        ), $this->decodedContent($page, $lang));
    }

    public function testSaveTranslationsFromInputSkipsEnglish()
    {
        $lang = $this->nonEnglishLanguage();
        $page = $this->createPage();

        $this->bindInput(array(
            'en_title' => 'English title',
            'en_slug' => 'english slug',
            'en_content' => 'English content',
            $lang . '_title' => 'Translated title',
            $lang . '_slug' => 'translated slug',
            $lang . '_content' => 'Translated content',
        ));

        $double = new ControllerConcernsTranslationDouble;
        $double->save($page, array('title', 'slug', 'content'), function ($field, $value) {
            return $field == 'slug' ? str_replace(' ', '_', $value) : $value;
        });

        $this->assertSame(0, $page->translations()->where('lang', 'en')->count());
        $this->assertSame($lang, $page->translations()->first()->lang);
        $this->assertSame('translated_slug', $this->decodedContent($page, $lang)['slug']);
    }

    public function testSaveTranslationsFromInputWithCategory()
    {
        $lang = $this->nonEnglishLanguage();
        $category = $this->createCategory();

        $this->bindInput(array(
            $lang . '_name' => 'Translated name',
            $lang . '_short_description' => 'Translated short description',
            $lang . '_long_description' => 'Translated long description',
        ));

        $double = new ControllerConcernsTranslationDouble;
        $double->save($category, array('name', 'short_description', 'long_description'));

        $this->assertSame(1, Translation::count());
        $this->assertSame(array(
            'name' => 'Translated name',
            'short_description' => 'Translated short description',
            'long_description' => 'Translated long description',
        ), $this->decodedContent($category, $lang));
    }

    public function testTranslatedContentForKeepsLanguagesAndDecodedObjects()
    {
        $lang = $this->nonEnglishLanguage();
        $page = $this->createPage();

        $translation = new Translation;
        $translation->lang = $lang;
        $translation->content = json_encode(array(
            'title' => 'Translated title',
            'slug' => 'translated_slug',
            'content' => 'Translated content',
        ));
        $page->translations()->save($translation);

        $double = new ControllerConcernsEditDataDouble;
        $translated = $double->translatedContent($page);

        $this->assertSame(array($lang), array_keys($translated));
        $this->assertSame('Translated title', $translated[$lang]->title);
        $this->assertSame('translated_slug', $translated[$lang]->slug);
        $this->assertSame('Translated content', $translated[$lang]->content);
    }

    public function testTagStringForUsesModelTagOrder()
    {
        $page = $this->createPage();

        Tag::addTag($page, 'Second');
        Tag::addTag($page, 'First');

        $expected = implode(',', $page->tags()->get()->pluck('name')->all());

        $double = new ControllerConcernsEditDataDouble;

        $this->assertSame($expected, $double->tagString($page));
        $this->assertSame('second,first', $double->tagString($page));
    }

    public function testTagStringForReturnsEmptyStringWhenNoTags()
    {
        $page = $this->createPage();

        $double = new ControllerConcernsEditDataDouble;

        $this->assertSame('', $double->tagString($page));
    }

    public function testSyncTagsFromInputAttachesThenReplacesTags()
    {
        $page = $this->createPage();
        $double = new ControllerConcernsTagsDouble;

        $double->sync($page, 'a,b');

        $this->assertSame(array('a', 'b'), $page->tags()->get()->pluck('name')->all());

        $double->sync($page, 'c');

        $this->assertSame(array('c'), $page->tags()->get()->pluck('name')->all());
        $this->assertSame(3, Tag::count());
    }

    public function testSyncTagsFromInputIgnoresEmptyValues()
    {
        $page = $this->createPage();
        $double = new ControllerConcernsTagsDouble;

        $double->sync($page, 'c');

        foreach (array('', '0', array(), null) as $tags) {
            $double->sync($page, $tags);

            $this->assertSame(array('c'), $page->tags()->get()->pluck('name')->all());
            $this->assertSame(1, Tag::count());
        }
    }

    public function testSyncTagsFromInputReattachesExistingTagWithoutDuplicateRow()
    {
        $page = $this->createPage();
        $double = new ControllerConcernsTagsDouble;

        $double->sync($page, 'Shared');

        $this->assertSame(1, Tag::count());
        $this->assertSame(array('shared'), $page->tags()->get()->pluck('name')->all());

        $double->sync($page, 'shared');

        $this->assertSame(1, Tag::count());
        $this->assertSame(array('shared'), $page->tags()->get()->pluck('name')->all());
    }

    public function testSyncTagsFromInputMixedCaseAndWhitespaceFollowsAddTag()
    {
        $page = $this->createPage();
        $double = new ControllerConcernsTagsDouble;

        $double->sync($page, ' Mixed Case ,  Trimmed ');

        $this->assertSame(array('mixed case', 'trimmed'), $page->tags()->get()->pluck('name')->all());
        $this->assertSame(2, Tag::count());
    }

    private function errorBagFrom($response, $bag = 'default')
    {
        $errors = $response->getSession()->get('errors');

        $this->assertInstanceOf(\Illuminate\Support\ViewErrorBag::class, $errors);

        return $errors->getBag($bag);
    }

    public function testRedirectNotFoundWithMessageUsesDefaultEditErrorKey()
    {
        $double = new ControllerConcernsRedirectDouble;

        $response = $double->redirectNotFound('/admin/pages/edit/1', 'The page cannot be found.');

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);
        $this->assertSame(302, $response->getStatusCode());
        $this->assertStringEndsWith('/admin/pages/edit/1', $response->getTargetUrl());
        $this->assertSame('The page cannot be found.', $this->errorBagFrom($response)->first('editError'));
    }

    public function testRedirectNotFoundWithMessageUsesGivenErrorKey()
    {
        $double = new ControllerConcernsRedirectDouble;

        $response = $double->redirectNotFound('/admin/categories', 'Category error', 'customKey');

        $this->assertStringEndsWith('/admin/categories', $response->getTargetUrl());
        $this->assertSame('Category error', $this->errorBagFrom($response)->first('customKey'));
        $this->assertSame(array(), $this->errorBagFrom($response)->get('editError'));
    }

    public function testValidateInputOrRedirectReturnsNullOnValidInput()
    {
        $this->bindInput(array('title' => 'Valid title'));

        $double = new ControllerConcernsValidationDouble;

        $this->assertNull($double->validate(
            array('title' => 'required'),
            array(),
            'admin/pages/create'
        ));
    }

    public function testValidateInputOrRedirectReturnsRedirectWithErrorsAndOldInput()
    {
        $input = array('title' => '', 'slug' => 'a b c');
        $this->bindInput($input);

        $double = new ControllerConcernsValidationDouble;

        $response = $double->validate(
            array(
                'title' => 'required',
                'slug' => 'required',
            ),
            array(),
            'admin/pages/create'
        );

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);
        $this->assertStringEndsWith('/admin/pages/create', $response->getTargetUrl());

        $errors = $this->errorBagFrom($response);

        $this->assertSame('The title field is required.', $errors->first('title'));
        $this->assertSame(array(), $errors->get('slug'));
        $this->assertSame($input, $response->getSession()->getOldInput());
    }

    public function testRemovesGalleryImagesRemovesOwnedImageAndRedirectsToOwner()
    {
        $page = $this->createPage();

        $image = new Image;
        $image->path = 'missing-controller-concerns-image.jpg';
        $page->images()->save($image);

        $double = new ControllerConcernsGalleryDouble;
        $response = $double->getImgremove($image->id);

        $this->assertNull(Image::find($image->id));
        $this->assertSame(302, $response->getStatusCode());
        $this->assertStringEndsWith('/admin/pages/edit/' . $page->id, $response->getTargetUrl());
    }

    public function testRemovesGalleryImagesRejectsImageOwnedByAnotherType()
    {
        $category = $this->createCategory();

        $image = new Image;
        $image->path = 'missing-controller-concerns-image.jpg';
        $category->images()->save($image);

        $double = new ControllerConcernsGalleryDouble;
        $response = $double->getImgremove($image->id);

        $this->assertNotNull(Image::find($image->id));
        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame(url('/admin/pages'), $response->getTargetUrl());
        $this->assertSame(
            'The image cannot be deleted at this time.',
            $this->errorBagFrom($response)->first('deleteError')
        );
    }

    public function testRemovesGalleryImagesRejectsMissingImage()
    {
        $double = new ControllerConcernsGalleryDouble;
        $response = $double->getImgremove(999999);

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame(url('/admin/pages'), $response->getTargetUrl());
        $this->assertSame(
            'The image cannot be deleted at this time.',
            $this->errorBagFrom($response)->first('deleteError')
        );
    }

    public function testInputRulesHasEmptyMessagesAndRulesReceivesId()
    {
        $rules = new ControllerConcernsRulesDouble;

        $this->assertSame(array(), $rules->messages());
        $this->assertSame(array('id' => null), $rules->rules());
        $this->assertSame(array('id' => 7), $rules->rules(7));
    }
}

class ControllerConcernsTranslationDouble
{
    use SavesTranslations;

    public function save($model, array $fields, ?callable $transform = null): void
    {
        $this->saveTranslationsFromInput($model, $fields, $transform);
    }
}

class ControllerConcernsEditDataDouble
{
    use PreparesEditData;

    public function translatedContent($model): array
    {
        return $this->translatedContentFor($model);
    }

    public function tagString($model): string
    {
        return $this->tagStringFor($model);
    }
}

class ControllerConcernsTagsDouble
{
    use SyncsTags;

    public function sync($model, $tags): void
    {
        $this->syncTagsFromInput($model, $tags);
    }
}

class ControllerConcernsRedirectDouble
{
    use RedirectsNotFound;

    public function redirectNotFound(string $path, string $message, string $errorKey = 'editError'): \Illuminate\Http\RedirectResponse
    {
        return $this->redirectNotFoundWithMessage($path, $message, $errorKey);
    }
}

class ControllerConcernsValidationDouble
{
    use ValidatesInput;

    public function validate(array $rules, array $messages, string $failureUrl): ?\Illuminate\Http\RedirectResponse
    {
        return $this->validateInputOrRedirect($rules, $messages, $failureUrl);
    }
}

class ControllerConcernsGalleryDouble extends Controller
{
    use RemovesGalleryImages;

    public function getImgremove($sid)
    {
        return $this->removeOwnedGalleryImage($sid, Page::class, 'admin/pages');
    }

    protected function galleryModelClass(): string
    {
        return Page::class;
    }

    protected function galleryPath(): string
    {
        return 'admin/pages';
    }
}

class ControllerConcernsRulesDouble extends InputRules
{
    public function rules($id = null): array
    {
        return array('id' => $id);
    }
}
