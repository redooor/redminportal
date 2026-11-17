<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Announcement;
use Redooor\Redminportal\App\Models\Bundle;
use Redooor\Redminportal\App\Models\Category;
use Redooor\Redminportal\App\Models\Coupon;
use Redooor\Redminportal\App\Models\Mailinglist;
use Redooor\Redminportal\App\Models\Media;
use Redooor\Redminportal\App\Models\Module;
use Redooor\Redminportal\App\Models\Page;
use Redooor\Redminportal\App\Models\Portfolio;
use Redooor\Redminportal\App\Models\Post;
use Redooor\Redminportal\App\Models\Product;
use Redooor\Redminportal\App\Models\Promotion;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Covers the bundled edit views posting checkbox value 1 while the controllers
 * keep accepting any non-empty value as true.
 */
class CheckboxValueFixTest extends BaseControllerTest
{
    use CharacterizationSupport;

    protected $category;

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
                'title' => 'Base announcement',
                'content' => 'Base content'
            ),
            'edit' => array(
                'id' => 1,
                'title' => 'Base announcement',
                'content' => 'Base content'
            )
        );

        $this->category = $this->createModel(Category::class, array(
            'name' => 'Checkbox category',
            'short_description' => 'Checkbox category short',
            'order' => 1,
            'active' => true
        ));

        $this->configureImageUploads();
    }

    public function tearDown(): void
    {
        $this->cleanupImageUploads();

        parent::tearDown();
    }

    /**
     * Test (Pass): every bundled edit view posts 1 and reflects the saved flag
     */
    public function testBundledEditViewsRenderCheckboxValueOneAndReflectFlag()
    {
        foreach ($this->bundledCheckboxViews() as $label => $case) {
            list($modelClass, $attributes, $url, $fields) = $case;

            $off = $this->createFlaggedModel($modelClass, $attributes, $fields, false, $label . ' off');
            $on = $this->createFlaggedModel($modelClass, $attributes, $fields, true, $label . ' on');

            foreach ($fields as $field) {
                $this->assertCheckboxState($url . $off->id, $field, false, $label);
                $this->assertCheckboxState($url . $on->id, $field, true, $label);
            }
        }
    }

    /**
     * Test (Pass): the product variant edit and view pages also post 1
     */
    public function testProductVariantViewsRenderCheckboxValueOneAndReflectFlag()
    {
        $parent = $this->createModel(Product::class, array(
            'name' => 'Variant parent',
            'sku' => 'VARIANT-PARENT',
            'short_description' => 'Short',
            'price' => 10,
            'category_id' => $this->category->id,
            'active' => true
        ));

        $off = $this->createModel(Product::class, array(
            'name' => 'Variant off',
            'sku' => 'VARIANT-OFF',
            'short_description' => 'Short',
            'price' => 10,
            'category_id' => $this->category->id,
            'featured' => false,
            'active' => false
        ));

        $on = $this->createModel(Product::class, array(
            'name' => 'Variant on',
            'sku' => 'VARIANT-ON',
            'short_description' => 'Short',
            'price' => 10,
            'category_id' => $this->category->id,
            'featured' => true,
            'active' => true
        ));

        foreach (array('featured', 'active') as $field) {
            $this->assertCheckboxState(
                '/admin/products/edit-variant/' . $parent->id . '/' . $off->id,
                $field,
                false,
                'product variant edit'
            );
            $this->assertCheckboxState(
                '/admin/products/edit-variant/' . $parent->id . '/' . $on->id,
                $field,
                true,
                'product variant edit'
            );
            $this->assertCheckboxState('/admin/products/view-variant/' . $off->id, $field, false, 'product variant view');
            $this->assertCheckboxState('/admin/products/view-variant/' . $on->id, $field, true, 'product variant view');
        }
    }

    /**
     * Assert the rendered checkbox for a field posts 1 and has the expected checked state
     */
    private function assertCheckboxState($url, $field, $checked, $label)
    {
        $this->call('GET', $url);
        $this->assertResponseOk();

        $crawler = new Crawler($this->response->getContent());
        $inputs = $crawler->filter('input[type="checkbox"][name="' . $field . '"]');

        $this->assertCount(1, $inputs, "Expected one [{$field}] checkbox on the {$label} page.");
        $input = $inputs->first();

        $this->assertSame('1', $input->attr('value'), "Checkbox [{$field}] on the {$label} page must post value 1.");

        if ($checked) {
            $this->assertNotNull($input->attr('checked'), "Checkbox [{$field}] on the {$label} page must be checked.");
        } else {
            $this->assertNull($input->attr('checked'), "Checkbox [{$field}] on the {$label} page must not be checked.");
        }
    }

    /**
     * Test (Pass): posting active=1 on a disabled category turns it on
     */
    public function testCategoryEditWithFlagOneEnables()
    {
        $category = $this->createModel(Category::class, array(
            'name' => 'Toggle category one',
            'short_description' => 'Short',
            'order' => 1,
            'active' => false
        ));

        $this->assertSame(0, (int) $category->active);

        $this->call('POST', '/admin/categories/store', $this->categoryInput($category, array('active' => '1')));

        $this->assertRedirectedTo('/admin/categories');
        $this->assertSame(1, (int) $category->fresh()->active);
    }

    /**
     * Test (Pass): posting active=0 still turns it on because any non-empty value is true
     */
    public function testCategoryEditWithZeroStillEnablesBecauseAnyNonEmptyValueIsTrue()
    {
        $category = $this->createModel(Category::class, array(
            'name' => 'Toggle category zero',
            'short_description' => 'Short',
            'order' => 1,
            'active' => false
        ));

        $this->assertSame(0, (int) $category->active);

        $this->call('POST', '/admin/categories/store', $this->categoryInput($category, array('active' => '0')));

        $this->assertRedirectedTo('/admin/categories');
        $this->assertSame(1, (int) $category->fresh()->active);
    }

    /**
     * Test (Pass): omitting active on an enabled category turns it off
     */
    public function testCategoryEditWithOmittedFlagDisables()
    {
        $category = $this->createModel(Category::class, array(
            'name' => 'Toggle category omitted',
            'short_description' => 'Short',
            'order' => 1,
            'active' => true
        ));

        $this->assertSame(1, (int) $category->active);

        $this->call('POST', '/admin/categories/store', $this->categoryInput($category, array(), array('active')));

        $this->assertRedirectedTo('/admin/categories');
        $this->assertSame(0, (int) $category->fresh()->active);
    }

    /**
     * Test (Pass): posting active=1 on a disabled bundle turns it on
     */
    public function testBundleEditWithFlagOneEnables()
    {
        list($bundle, $product) = $this->createBundleFixture('Toggle bundle one', 'TOGGLE-BUNDLE-ONE', false);

        $this->assertSame(0, (int) $bundle->active);

        $this->call('POST', '/admin/bundles/store', $this->bundleInput($bundle, $product, array('active' => '1')));

        $this->assertRedirectedTo('/admin/bundles');
        $this->assertSame(1, (int) $bundle->fresh()->active);
    }

    /**
     * Test (Pass): posting active=0 still turns it on because any non-empty value is true
     */
    public function testBundleEditWithZeroStillEnablesBecauseAnyNonEmptyValueIsTrue()
    {
        list($bundle, $product) = $this->createBundleFixture('Toggle bundle zero', 'TOGGLE-BUNDLE-ZERO', false);

        $this->assertSame(0, (int) $bundle->active);

        $this->call('POST', '/admin/bundles/store', $this->bundleInput($bundle, $product, array('active' => '0')));

        $this->assertRedirectedTo('/admin/bundles');
        $this->assertSame(1, (int) $bundle->fresh()->active);
    }

    /**
     * Test (Pass): omitting active on an enabled bundle turns it off
     */
    public function testBundleEditWithOmittedFlagDisables()
    {
        list($bundle, $product) = $this->createBundleFixture('Toggle bundle omitted', 'TOGGLE-BUNDLE-OMITTED', true);

        $this->assertSame(1, (int) $bundle->active);

        $this->call('POST', '/admin/bundles/store', $this->bundleInput($bundle, $product, array(), array('active')));

        $this->assertRedirectedTo('/admin/bundles');
        $this->assertSame(0, (int) $bundle->fresh()->active);
    }

    /**
     * Test (Pass): the announcement edit page still renders for an existing announcement
     */
    public function testAnnouncementEditPageStillRendersForExistingAnnouncement()
    {
        $announcement = $this->createModel(Announcement::class, array(
            'title' => 'Existing announcement',
            'content' => 'Existing announcement body'
        ));

        $this->call('GET', '/admin/announcements/edit/' . $announcement->id);

        $this->assertResponseOk();
        $this->assertViewHas('announcement');
    }

    /**
     * Create a bundle with its required category and product fixtures
     */
    private function createBundleFixture($name, $sku, $active)
    {
        $product = $this->createModel(Product::class, array(
            'name' => $name . ' product',
            'sku' => $sku . '-PRODUCT',
            'short_description' => 'Short',
            'price' => 10,
            'category_id' => $this->category->id,
            'active' => true
        ));

        $bundle = $this->createModel(Bundle::class, array(
            'name' => $name,
            'sku' => $sku,
            'short_description' => 'Short',
            'long_description' => 'Long',
            'price' => 10,
            'category_id' => $this->category->id,
            'active' => $active
        ));

        return array($bundle, $product);
    }

    /**
     * Build a valid category edit input, optionally removing keys
     */
    private function categoryInput(Category $category, array $overrides = array(), array $remove = array())
    {
        $input = array_merge(array(
            'id' => $category->id,
            'name' => $category->name,
            'short_description' => $category->short_description,
            'long_description' => $category->long_description,
            'order' => $category->order,
            'parent_id' => 0
        ), $overrides);

        foreach ($remove as $key) {
            unset($input[$key]);
        }

        return $input;
    }

    /**
     * Build a valid bundle edit input, optionally removing keys
     */
    private function bundleInput(Bundle $bundle, Product $product, array $overrides = array(), array $remove = array())
    {
        $input = array_merge(array(
            'id' => $bundle->id,
            'name' => $bundle->name,
            'sku' => $bundle->sku,
            'short_description' => $bundle->short_description,
            'long_description' => $bundle->long_description,
            'price' => $bundle->price,
            'category_id' => $this->category->id,
            'product_id' => array($product->id),
            'pricelist_id' => array()
        ), $overrides);

        foreach ($remove as $key) {
            unset($input[$key]);
        }

        return $input;
    }

    /**
     * Create a model with every flag set to the same value and unique text fields
     */
    private function createFlaggedModel($modelClass, array $attributes, array $fields, $value, $suffix)
    {
        foreach ($fields as $field) {
            $attributes[$field] = $value;
        }

        foreach (array('name', 'title') as $key) {
            if (array_key_exists($key, $attributes)) {
                $attributes[$key] .= ' ' . $suffix;
            }
        }

        foreach (array('sku', 'code') as $key) {
            if (array_key_exists($key, $attributes)) {
                $attributes[$key] = $this->uniqueSuffix($suffix) . '-' . $attributes[$key];
            }
        }

        if (array_key_exists('email', $attributes)) {
            $attributes['email'] = strtolower($this->uniqueSuffix($suffix)) . '-' . $attributes['email'];
        }

        if (array_key_exists('slug', $attributes)) {
            $attributes['slug'] = strtolower($this->uniqueSuffix($suffix)) . '-' . $attributes['slug'];
        }

        return $this->createModel($modelClass, $attributes);
    }

    /**
     * Turn a display suffix into something safe for sku, slug, code and email fields
     */
    private function uniqueSuffix($suffix)
    {
        return trim(preg_replace('/[^a-z0-9]+/i', '-', $suffix), '-');
    }

    /**
     * The bundled edit views, their fixture attributes, url prefix and flag fields
     */
    private function bundledCheckboxViews()
    {
        $categoryId = $this->category->id;

        return array(
            'bundles' => array(Bundle::class, array(
                'name' => 'Checkbox bundle',
                'sku' => 'CHECKBOX-BUNDLE',
                'short_description' => 'Short',
                'price' => 10,
                'category_id' => $categoryId
            ), '/admin/bundles/edit/', array('featured', 'active')),
            'categories' => array(Category::class, array(
                'name' => 'Checkbox category row',
                'short_description' => 'Short',
                'order' => 1
            ), '/admin/categories/edit/', array('active')),
            'coupons' => array(Coupon::class, array(
                'code' => 'CHECKBOX10',
                'description' => 'Checkbox coupon',
                'amount' => 10,
                'start_date' => '2016-05-02 17:39:00',
                'end_date' => '2016-05-03 17:39:00'
            ), '/admin/coupons/edit/', array(
                'is_percent',
                'multiple_coupons',
                'exclude_sale_item',
                'automatically_apply'
            )),
            'medias' => array(Media::class, array(
                'name' => 'Checkbox media',
                'path' => 'broken.pdf',
                'sku' => 'CHECKBOX-MEDIA',
                'short_description' => 'Short',
                'category_id' => $categoryId
            ), '/admin/medias/edit/', array('featured', 'active')),
            'mailinglists' => array(Mailinglist::class, array(
                'email' => 'checkbox@example.com',
                'first_name' => 'Checkbox',
                'last_name' => 'Person'
            ), '/admin/mailinglists/edit/', array('active')),
            'modules' => array(Module::class, array(
                'name' => 'Checkbox module',
                'sku' => 'CHECKBOX-MODULE',
                'short_description' => 'Short',
                'category_id' => $categoryId
            ), '/admin/modules/edit/', array('featured', 'active')),
            'pages' => array(Page::class, array(
                'title' => 'Checkbox page',
                'slug' => 'checkbox-page',
                'content' => 'Body',
                'category_id' => $categoryId
            ), '/admin/pages/edit/', array('private')),
            'portfolios' => array(Portfolio::class, array(
                'name' => 'Checkbox portfolio',
                'short_description' => 'Short',
                'category_id' => $categoryId
            ), '/admin/portfolios/edit/', array('active')),
            'posts' => array(Post::class, array(
                'title' => 'Checkbox post',
                'slug' => 'checkbox-post',
                'content' => 'Body',
                'category_id' => $categoryId
            ), '/admin/posts/edit/', array('featured', 'private')),
            'products' => array(Product::class, array(
                'name' => 'Checkbox product',
                'sku' => 'CHECKBOX-PRODUCT',
                'short_description' => 'Short',
                'price' => 10,
                'category_id' => $categoryId
            ), '/admin/products/edit/', array('featured', 'active')),
            'promotions' => array(Promotion::class, array(
                'name' => 'Checkbox promotion',
                'short_description' => 'Short',
                'long_description' => 'Long',
                'start_date' => '2016-02-29',
                'end_date' => '2016-03-01'
            ), '/admin/promotions/edit/', array('active'))
        );
    }
}
