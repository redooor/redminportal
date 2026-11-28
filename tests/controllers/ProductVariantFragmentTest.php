<?php namespace Redooor\Redminportal\Test;

use Database\Seeders\RedminSeeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Redooor\Redminportal\App\Models\Category;
use Redooor\Redminportal\App\Models\Group;
use Redooor\Redminportal\App\Models\Product;
use Redooor\Redminportal\App\Models\Tag;
use Redooor\Redminportal\App\Models\Translation;
use Redooor\Redminportal\App\Models\User;

/**
 * Covers the product variant pages rendered as modal fragments.
 */
class ProductVariantFragmentTest extends RedminBrowserTestCase
{
    use CharacterizationSupport;

    private $category;
    private $parent;
    private $variant;
    private $variantImage;
    private $parentImage;

    public function setUp(): void
    {
        parent::setUp();

        $this->seed(RedminSeeder::class);
        $this->loginRedminUser(1);

        $this->category = $this->createModel(Category::class, array(
            'name' => 'Variant fragment category',
            'short_description' => 'Category for variant fragments',
            'active' => true
        ));

        $this->parent = $this->createModel(Product::class, array(
            'name' => 'Parent product',
            'sku' => 'PARENT-FRAGMENT-001',
            'short_description' => 'Parent short',
            'category_id' => $this->category->id,
            'active' => true
        ));

        $this->variant = $this->createModel(Product::class, array(
            'name' => 'Variant product',
            'sku' => 'VARIANT-FRAGMENT-001',
            'short_description' => 'Variant short',
            'long_description' => 'Variant long',
            'price' => 12.50,
            'category_id' => $this->category->id,
            'weight' => 5.25,
            'weight_unit' => 'kg',
            'length' => 4.5,
            'width' => 3.5,
            'height' => 2.5,
            'volume_unit' => 'm',
            'featured' => true,
            'active' => true
        ));

        $this->parent->variants()->attach($this->variant->id);
        $this->variantImage = $this->attachImage($this->variant, 'variant-only.jpg');
        $this->parentImage = $this->attachImage($this->parent, 'parent-only.jpg');

        $this->configureImageUploads();
    }

    public function tearDown(): void
    {
        $this->cleanupImageUploads();

        parent::tearDown();
    }

    public function testCreateVariantFragmentAndPage()
    {
        $url = '/admin/products/create-variant/' . $this->parent->id;

        $this->assertFragment($this->ajaxGet($url));
        $this->assertStringContainsString('variant-sku', $this->ajaxGet($url)->getContent());

        $page = $this->call('GET', $url);
        $page->assertOk();
        $page->assertHeaderMissing('X-Redmin-Fragment');
        $this->assertStringContainsString('no-store', $page->headers->get('Cache-Control'));
        $this->assertStringContainsString('<html', $page->getContent());
        $this->assertStringContainsString('redmin-fileinput.min.js', $page->getContent());
        $this->assertStringContainsString('jodit.min.js', $page->getContent());
        $this->assertStringContainsString('bootstrap-tagsinput.min.js', $page->getContent());
    }

    public function testEditVariantFragmentAndPage()
    {
        $url = '/admin/products/edit-variant/' . $this->parent->id . '/' . $this->variant->id;

        $this->assertFragment($this->ajaxGet($url));
        $this->assertStringContainsString('variant-sku', $this->ajaxGet($url)->getContent());

        $page = $this->call('GET', $url);
        $page->assertOk();
        $page->assertHeaderMissing('X-Redmin-Fragment');
        $this->assertStringContainsString('no-store', $page->headers->get('Cache-Control'));
        $this->assertStringContainsString('<html', $page->getContent());
        $this->assertStringContainsString('redmin-fileinput.min.js', $page->getContent());
        $this->assertStringContainsString('jodit.min.js', $page->getContent());
        $this->assertStringContainsString('bootstrap-tagsinput.min.js', $page->getContent());
    }

    public function testViewVariantFragmentAndPage()
    {
        $url = '/admin/products/view-variant/' . $this->variant->id;

        $this->assertFragment($this->ajaxGet($url));
        $this->assertStringContainsString('data-fragment-title', $this->ajaxGet($url)->getContent());

        $page = $this->call('GET', $url);
        $page->assertOk();
        $page->assertHeaderMissing('X-Redmin-Fragment');
        $this->assertStringContainsString('no-store', $page->headers->get('Cache-Control'));
        $this->assertStringContainsString('<html', $page->getContent());
    }

    public function testNotFoundRedirectsAreUnchanged()
    {
        $urls = array(
            '/admin/products/create-variant/999999',
            '/admin/products/edit-variant/999999/999999',
            '/admin/products/view-variant/999999',
        );

        foreach ($urls as $url) {
            foreach (array(true, false) as $ajax) {
                $response = $ajax ? $this->ajaxGet($url) : $this->call('GET', $url);

                $response->assertStatus(302);
                $response->assertRedirect('/admin/products');
                $response->assertHeaderMissing('X-Redmin-Fragment');
            }
        }
    }

    public function testGuestAjaxGetsNoVariantData()
    {
        Auth::guard('redminguard')->logout();
        Auth::forgetGuards();

        $urls = array(
            '/admin/products/create-variant/' . $this->parent->id,
            '/admin/products/edit-variant/' . $this->parent->id . '/' . $this->variant->id,
            '/admin/products/view-variant/' . $this->variant->id,
        );

        foreach ($urls as $url) {
            $response = $this->ajaxGet($url);

            $response->assertOk();
            $response->assertHeaderMissing('X-Redmin-Fragment');
            $response->assertDontSee($this->variant->name, false);
            $response->assertDontSee('variant-sku', false);
        }

        $page = $this->call('GET', '/admin/products/view-variant/' . $this->variant->id);
        $page->assertStatus(302);
        $page->assertRedirect('login');
        $page->assertHeaderMissing('X-Redmin-Fragment');
    }

    public function testUserWithoutThePermissionGetsNoVariantData()
    {
        $this->loginAs($this->createUserWithPermissions(array('admin.dashboard.view' => 1)));

        $urls = array(
            '/admin/products/create-variant/' . $this->parent->id,
            '/admin/products/edit-variant/' . $this->parent->id . '/' . $this->variant->id,
            '/admin/products/view-variant/' . $this->variant->id,
        );

        foreach ($urls as $url) {
            $response = $this->ajaxGet($url);

            $response->assertStatus(302);
            $response->assertRedirect('login/unauthorized');
            $response->assertHeaderMissing('X-Redmin-Fragment');
            $response->assertDontSee($this->variant->name, false);
            $response->assertDontSee('variant-sku', false);
        }

        $page = $this->call('GET', '/admin/products/view-variant/' . $this->variant->id);
        $page->assertStatus(302);
        $page->assertRedirect('login/unauthorized');
        $page->assertHeaderMissing('X-Redmin-Fragment');
    }

    public function testValuesAreEscapedInViewAndEditFragments()
    {
        $payload = '"><script>alert(1)</script><img src=x onerror=alert(2)>" onmouseover="alert(8)';

        $this->variant->name = $payload;
        $this->variant->sku = 'SKU<script>alert(3)</script>';
        $this->variant->short_description = 'Short <script>alert(4)</script> " onmouseover="alert(5)';
        $this->variant->long_description = 'Long <script>alert(6)</script> " onmouseover="alert(7)';
        $this->variant->save();

        $translation = new Translation;
        $translation->lang = 'cn';
        $translation->content = json_encode(array(
            'name' => $payload,
            'short_description' => $this->variant->short_description,
            'long_description' => $this->variant->long_description,
        ));
        $this->variant->translations()->save($translation);

        $urls = array(
            '/admin/products/edit-variant/' . $this->parent->id . '/' . $this->variant->id,
            '/admin/products/view-variant/' . $this->variant->id,
        );

        foreach ($urls as $url) {
            $html = $this->ajaxGet($url)->getContent();

            $this->assertStringNotContainsString('<script>', $html);
            $this->assertStringNotContainsString('"><img src=x onerror=', $html);
            $this->assertStringContainsString('&lt;script&gt;', $html);
            $this->assertStringContainsString('&quot;&gt;&lt;script&gt;', $html);
            $this->assertStringContainsString('&quot; onmouseover=&quot;alert(8)', $html);
        }
    }

    public function testFragmentMarkupUsesPrefixedIdsAndMatchingLabels()
    {
        $createUrl = '/admin/products/create-variant/' . $this->parent->id;
        $editUrl = '/admin/products/edit-variant/' . $this->parent->id . '/' . $this->variant->id;
        $viewUrl = '/admin/products/view-variant/' . $this->variant->id;

        $create = $this->ajaxGet($createUrl)->getContent();
        $edit = $this->ajaxGet($editUrl)->getContent();
        $view = $this->ajaxGet($viewUrl)->getContent();

        foreach (array($create, $edit, $view) as $html) {
            $this->assertStringNotContainsString('<iframe', $html);
            $this->assertStringNotContainsString('<script', $html);
            $this->assertStringNotContainsString('<style', $html);
            $this->assertEveryLabelMatchesAnId($html);
        }

        $excluded = array(
            'name', 'short_description', 'long_description', 'sku', 'price', 'tags',
            'weight', 'length', 'width', 'height', 'weight_unit', 'volume_unit',
            'product_id', 'id', 'category_id', 'featured-checker', 'active-checker',
        );

        $expected = array(
            'variant-name', 'variant-short_description', 'variant-long_description',
            'variant-sku', 'variant-price', 'variant-tags', 'variant-weight',
            'variant-weight_unit', 'variant-length', 'variant-width', 'variant-height',
            'variant-volume_unit', 'variant-product_id', 'variant-category_id',
            'variant-featured-checker', 'variant-active-checker',
        );

        foreach (array($create, $edit) as $html) {
            $ids = $this->idsInHtml($html);

            foreach ($excluded as $id) {
                $this->assertNotContains($id, $ids, 'Unprefixed id ' . $id);
            }

            foreach ($expected as $id) {
                $this->assertContains($id, $ids, 'Missing id ' . $id);
            }

            $this->assertStringContainsString('data-fragment-form', $html);
            $this->assertStringContainsString('data-editor-images', $html);
        }

        $this->assertContains('variant-id', $this->idsInHtml($edit));
        $this->assertStringContainsString('data-fragment-title', $create);
        $this->assertStringContainsString('data-fragment-title', $edit);
        $this->assertStringContainsString('data-fragment-title', $view);

        $this->assertStringContainsString('data-fragment-post', $edit);
        $this->assertStringNotContainsString('btn-confirm', $edit);
        $this->assertStringNotContainsString('data-lightbox', $edit);

        $editPage = $this->call('GET', $editUrl)->getContent();
        $this->assertStringContainsString('btn-confirm', $editPage);
        $this->assertStringContainsString('data-lightbox', $editPage);

        $editLink = url('admin/products/edit-variant/' . $this->parent->id . '/' . $this->variant->id);
        $this->assertStringContainsString('href="' . $editLink . '"', $view);
        $this->assertStringContainsString('data-fragment-modal="#product-variant-modal"', $view);

        $createImages = $this->editorImages($create);
        $this->assertSame(array(), $createImages);

        $editImages = $this->editorImages($edit);
        $this->assertCount(1, $editImages);
        $this->assertSame('variant-only', $editImages[0]['title']);
        $this->assertStringContainsString('variant-only.jpg', $editImages[0]['value']);
        $this->assertStringNotContainsString('parent-only.jpg', json_encode($editImages));
    }

    public function testProductEditPageUsesTheFragmentModal()
    {
        $html = $this->call('GET', '/admin/products/edit/' . $this->parent->id)->getContent();

        $this->assertStringNotContainsString('<iframe', $html);
        $this->assertStringContainsString('id="product-variant-modal"', $html);
        $this->assertStringContainsString('id="product-variant-modal-title"', $html);
        $this->assertStringContainsString('modal-lg modal-dialog-scrollable', $html);
        $this->assertStringContainsString('id="modal-progress"', $html);
        $this->assertStringContainsString('data-fragment-loading=', $html);
        $this->assertStringContainsString('data-fragment-loaded=', $html);
        $this->assertStringContainsString('data-fragment-error=', $html);
        $this->assertStringContainsString('data-fragment-unknown=', $html);
        $this->assertStringContainsString('data-fragment-status', $html);

        $variantModal = $this->elementById($html, 'product-variant-modal', 'variant-remove-modal');
        $this->assertStringContainsString('data-fragment-body', $variantModal);
        $this->assertStringContainsString('id="variant-remove-modal"', $html);

        $addButton = $this->elementById($html, 'add-product-variant');
        $this->assertStringContainsString('data-fragment-modal="#product-variant-modal"', $addButton);
    }

    public function testVariantListLinksOpenTheFragmentModal()
    {
        $html = $this->call('GET', '/admin/products/list-variants/' . $this->parent->id)->getContent();

        $edit = $this->anchorTag($html, 'edit-product-variant');
        $this->assertStringContainsString('data-fragment-modal="#product-variant-modal"', $edit);
        $this->assertStringContainsString('href="' . url('admin/products/edit-variant/' . $this->parent->id . '/' . $this->variant->id) . '"', $edit);

        $view = $this->anchorTag($html, 'view-product-variant');
        $this->assertStringContainsString('data-fragment-modal="#product-variant-modal"', $view);
        $this->assertStringContainsString('href="' . url('admin/products/view-variant/' . $this->variant->id) . '"', $view);
    }

    public function testProductEditPageAndVariantFragmentsHaveDisjointIds()
    {
        $page = $this->call('GET', '/admin/products/edit/' . $this->parent->id)->getContent();
        $pageIds = array_flip($this->idsInHtml($page));

        $this->assertNotEmpty($pageIds);

        $fragments = array(
            'create' => $this->ajaxGet('/admin/products/create-variant/' . $this->parent->id)->getContent(),
            'edit' => $this->ajaxGet('/admin/products/edit-variant/' . $this->parent->id . '/' . $this->variant->id)->getContent(),
        );

        foreach ($fragments as $name => $html) {
            $fragmentIds = array_unique($this->idsInHtml($html));

            $this->assertNotEmpty($fragmentIds, 'No ids in the ' . $name . ' fragment');

            foreach ($fragmentIds as $id) {
                $this->assertArrayNotHasKey($id, $pageIds, 'The ' . $name . ' fragment shares id ' . $id . ' with the product edit page');
            }
        }
    }

    public function testCreateVariantValidationRestoresOldInput()
    {
        $input = $this->variantInput(array(
            'name' => '',
            'short_description' => 'Typed create short',
            'sku' => 'TYPED-CREATE-SKU',
            'price' => '0',
            'tags' => 'typed-tag',
            'weight' => '0',
            'length' => '',
            'width' => '',
            'height' => '',
        ));

        $response = $this->ajaxPost('/admin/products/store', $input);
        $response->assertStatus(302);
        $response->assertRedirect('/admin/products/create-variant/' . $this->parent->id);

        $fragment = $this->ajaxGet($this->redirectPath($response));
        $this->assertFragment($fragment);
        $html = $fragment->getContent();

        $this->assertStringContainsString('The name field is required.', $html);
        $this->assertInputValue($html, 'name', '');
        $this->assertInputValue($html, 'weight', '0');
        $this->assertInputValue($html, 'price', '0');
        $this->assertInputValue($html, 'sku', 'TYPED-CREATE-SKU');
        $this->assertInputValue($html, 'length', '');
        $this->assertInputValue($html, 'width', '');
        $this->assertInputValue($html, 'height', '');
        $this->assertStringNotContainsString('checked', $this->inputTagById($html, 'variant-featured-checker'));
    }

    public function testEditVariantValidationRestoresOldInput()
    {
        $tag = new Tag;
        $tag->name = 'OriginalTag';
        $tag->save();
        $this->variant->tags()->attach($tag->id);

        $input = $this->variantInput(array(
            'id' => $this->variant->id,
            'name' => '',
            'short_description' => 'Typed edit short',
            'sku' => 'TYPED-EDIT-SKU',
            'price' => '0',
            'tags' => 'typed-tag',
            'weight' => '0',
            'weight_unit' => 'g',
            'length' => '',
            'width' => '',
            'height' => '',
            'volume_unit' => 'mm',
            'active' => '1',
        ));

        $response = $this->ajaxPost('/admin/products/store', $input);
        $response->assertStatus(302);
        $response->assertRedirect('/admin/products/edit-variant/' . $this->parent->id . '/' . $this->variant->id);

        $fragment = $this->ajaxGet($this->redirectPath($response));
        $this->assertFragment($fragment);
        $html = $fragment->getContent();

        $this->assertStringContainsString('The name field is required.', $html);
        $this->assertInputValue($html, 'name', '');
        $this->assertInputValue($html, 'weight', '0');
        $this->assertInputValue($html, 'price', '0');
        $this->assertInputValue($html, 'sku', 'TYPED-EDIT-SKU');
        $this->assertInputValue($html, 'tags', 'typed-tag');
        $this->assertInputValue($html, 'length', '');
        $this->assertInputValue($html, 'width', '');
        $this->assertInputValue($html, 'height', '');
        $this->assertStringContainsString('value="g" selected', $this->selectBlock($html, 'weight_unit'));
        $this->assertStringContainsString('value="mm" selected', $this->selectBlock($html, 'volume_unit'));
        $this->assertStringNotContainsString('checked', $this->inputTagById($html, 'variant-featured-checker'));
        $this->assertStringContainsString('checked', $this->inputTagById($html, 'variant-active-checker'));
    }

    public function testSuccessfulCreateEndsOnTheViewFragment()
    {
        $input = $this->variantInput(array(
            'name' => 'Successful variant',
            'short_description' => 'Successful short',
            'sku' => 'VARIANT-SUCCESS-001',
            'weight' => '1.5',
            'featured' => '1',
            'active' => '1',
        ));

        $response = $this->ajaxPost('/admin/products/store', $input);
        $response->assertStatus(302);

        $created = Product::where('sku', 'VARIANT-SUCCESS-001')->first();
        $this->assertNotNull($created);
        $response->assertRedirect('/admin/products/view-variant/' . $created->id);

        $fragment = $this->ajaxGet($this->redirectPath($response));
        $this->assertFragment($fragment);
        $this->assertStringContainsString('Successful variant', $fragment->getContent());
        $this->assertStringContainsString('data-fragment-title', $fragment->getContent());
        $this->assertSame(2, $this->parent->variants()->count());
    }

    /**
     * An empty tags box reaches the server as '' or, with ConvertEmptyStringsToNull, as null. Neither may fail validation.
     */
    public function testVariantSavesWithAnEmptyTagsBox()
    {
        foreach (array('' => 'EMPTY', 'null' => 'NULL') as $kind => $label) {
            $tags = $kind === 'null' ? null : '';

            $response = $this->ajaxPost('/admin/products/store', $this->variantInput(array(
                'name' => 'Untagged variant ' . $label,
                'sku' => 'VARIANT-TAGS-' . $label,
                'tags' => $tags,
            )));

            $created = Product::where('sku', 'VARIANT-TAGS-' . $label)->first();
            $this->assertNotNull($created, 'A variant with ' . $label . ' tags was not created');
            $response->assertRedirect('/admin/products/view-variant/' . $created->id);

            $response = $this->ajaxPost('/admin/products/store', $this->variantInput(array(
                'id' => $created->id,
                'name' => 'Untagged variant edited ' . $label,
                'sku' => 'VARIANT-TAGS-' . $label,
                'tags' => $tags,
            )));

            $response->assertSessionHasNoErrors();
            $response->assertRedirect('/admin/products/view-variant/' . $created->id);
            $this->assertSame('Untagged variant edited ' . $label, $created->fresh()->name);
        }
    }

    public function testInvalidImageUploadShowsTheErrorAndNoSuccessView()
    {
        $input = $this->variantInput(array(
            'name' => 'Invalid image variant',
            'short_description' => 'Invalid image short',
            'sku' => 'VARIANT-INVALID-IMAGE-001',
        ));

        $response = $this->ajaxPost('/admin/products/store', $input, array(
            'images' => array($this->invalidUploadedFile()),
        ));
        $response->assertStatus(302);
        $response->assertRedirect('/admin/products/create-variant/' . $this->parent->id);

        $fragment = $this->ajaxGet($this->redirectPath($response));
        $this->assertFragment($fragment);
        $html = $fragment->getContent();

        $this->assertStringContainsString('must be a file of type', $html);
        $this->assertStringContainsString(trans('redminportal::forms.create_product_variation'), $html);
        $this->assertStringNotContainsString(trans('redminportal::forms.view_product'), $html);
        $this->assertSame(0, Product::where('sku', 'VARIANT-INVALID-IMAGE-001')->count());
    }

    private function elementById($html, $id, $nextId = null)
    {
        $start = strpos($html, 'id="' . $id . '"');

        if ($start === false) {
            return '';
        }

        $start = max(0, strrpos(substr($html, 0, $start), '<'));

        if ($nextId === null) {
            $end = strpos($html, '>', $start);

            return $end === false ? substr($html, $start) : substr($html, $start, $end - $start + 1);
        }

        $end = strpos($html, 'id="' . $nextId . '"', $start);

        return $end === false ? substr($html, $start) : substr($html, $start, $end - $start);
    }

    private function anchorTag($html, $class)
    {
        preg_match_all('/<a\b[^>]*>/i', $html, $matches);

        foreach ($matches[0] as $tag) {
            if (strpos($tag, $class) !== false) {
                return $tag;
            }
        }

        $this->fail('Missing anchor with class ' . $class);

        return '';
    }

    private function ajaxGet($url)
    {
        return $this->call('GET', $url, array(), array(), array(), array(
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
        ));
    }

    private function ajaxPost($url, array $input, array $files = array())
    {
        return $this->call('POST', $url, $input, array(), $files, array(
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
        ));
    }

    private function redirectPath($response)
    {
        $path = parse_url($response->getTargetUrl(), PHP_URL_PATH);

        return $path ?: $response->getTargetUrl();
    }

    private function assertFragment($response)
    {
        $response->assertOk();
        $response->assertHeader('X-Redmin-Fragment', '1');
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $response->assertDontSee('<html', false);
    }

    private function variantInput(array $overrides = array())
    {
        return array_merge(array(
            'name' => 'Variant input',
            'short_description' => 'Variant input short',
            'long_description' => 'Variant input long',
            'category_id' => $this->category->id,
            'sku' => 'VARIANT-INPUT-' . uniqid(),
            'price' => '9.99',
            'product_id' => $this->parent->id,
            'weight' => '1.5',
            'weight_unit' => 'kg',
            'volume_unit' => 'm',
        ), $overrides);
    }

    private function idsInHtml($html)
    {
        preg_match_all('/\bid="([^"]*)"/i', $html, $matches);

        return $matches[1];
    }

    private function assertEveryLabelMatchesAnId($html)
    {
        $ids = array_flip($this->idsInHtml($html));
        $targets = $this->labelTargets($html);

        $this->assertNotEmpty($targets);

        foreach ($targets as $target) {
            $this->assertArrayHasKey($target, $ids, 'Missing id for label ' . $target);
        }
    }

    private function labelTargets($html)
    {
        preg_match_all('/<label\b[^>]*\bfor="([^"]*)"/i', $html, $matches);

        return $matches[1];
    }

    private function editorImages($html)
    {
        preg_match('/data-editor-images="([^"]*)"/i', $html, $matches);
        $this->assertNotEmpty($matches, 'Missing data-editor-images attribute');

        $images = json_decode(html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'), true);
        $this->assertIsArray($images);

        return $images;
    }

    private function inputTag($html, $name)
    {
        preg_match('/<(?:input|select|textarea)\b[^>]*\bname="' . preg_quote($name, '/') . '"[^>]*>/i', $html, $matches);

        return isset($matches[0]) ? $matches[0] : '';
    }

    private function inputTagById($html, $id)
    {
        preg_match('/<input\b[^>]*\bid="' . preg_quote($id, '/') . '"[^>]*>/i', $html, $matches);

        return isset($matches[0]) ? $matches[0] : '';
    }

    private function selectBlock($html, $name)
    {
        preg_match('/<select\b[^>]*\bname="' . preg_quote($name, '/') . '".*?<\/select>/is', $html, $matches);

        return isset($matches[0]) ? $matches[0] : '';
    }

    private function assertInputValue($html, $name, $value)
    {
        $tag = $this->inputTag($html, $name);

        $this->assertNotSame('', $tag, 'Missing input ' . $name);

        if ($value === '') {
            if (preg_match('/\bvalue="([^"]*)"/', $tag, $matches)) {
                $this->assertSame('', $matches[1]);
            }

            return;
        }

        $this->assertStringContainsString('value="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '"', $tag);
    }

    private function createUserWithPermissions(array $permissions)
    {
        $user = new User;
        $user->email = 'variant-fragment-' . uniqid() . '@test.com';
        $user->password = Hash::make('test');
        $user->first_name = 'Limited';
        $user->last_name = 'User';
        $user->activated = 1;
        $user->save();

        $group = new Group;
        $group->name = 'Variant fragment group ' . uniqid();
        $group->permissions = json_encode($permissions);
        $group->save();

        $user->groups()->save($group);

        return $user;
    }

    private function loginAs(User $user)
    {
        $this->loginRedminUser($user->id);
        Auth::forgetGuards();
    }
}
