<?php namespace Redooor\Redminportal\Test;

use Illuminate\Support\Facades\Route;
use Redooor\Redminportal\App\Http\Controllers\PageController;
use Redooor\Redminportal\App\Http\Controllers\ProductController;
use Redooor\Redminportal\App\Models\Page;
use Redooor\Redminportal\App\Models\Product;

/**
 * Pins the behaviour before the controller refactor; do not edit to make a refactor pass.
 */
class HostSubclassCompatibilityTest extends BaseControllerTest
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
                'title' => 'Host page title',
                'slug' => 'host page slug',
                'content' => 'Host page body'
            ),
            'edit' => array(
                'id' => 1,
                'title' => 'Host page title',
                'slug' => 'host page slug',
                'content' => 'Host page body'
            )
        );
    }

    /**
     * Test (Pass): a host subclass overriding postStore and adding helpers still creates a page
     */
    public function testHostPageSubclassCreatesPageLikeStockController()
    {
        Route::post('/admin/host-pages/store', [CharacterizationHostPageController::class, 'postStore'])
            ->middleware(['redminsession', 'redminauth']);

        $this->call('POST', '/admin/host-pages/store', array(
            'title' => 'Host subclass page',
            'slug' => 'host subclass page',
            'content' => 'Host subclass body'
        ));

        $this->assertRedirectedTo('/admin/pages');
        $this->assertSame(1, Page::count());
        $this->assertSame('Host subclass page', Page::first()->title);
        $this->assertSame('host_subclass_page', Page::first()->slug);

        $this->call('POST', '/admin/pages/store', array(
            'title' => 'Stock page',
            'slug' => 'stock page',
            'content' => 'Stock body'
        ));

        $this->assertRedirectedTo('/admin/pages');
        $this->assertSame(2, Page::count());

        $controller = new CharacterizationHostPageController(new Page);
        $this->assertSame('host-page-save', $controller->save());
        $this->assertSame('host-page-validate', $controller->validate());
    }

    /**
     * Test (Pass): a host subclass overriding the gallery methods does not change image removal
     */
    public function testHostGalleryOverridesDoNotChangeImageRemoval()
    {
        $page = $this->createNewModel(new Page, array(
            'title' => 'Gallery host page',
            'slug' => 'gallery_host_page',
            'content' => 'Body'
        ));
        $image = new \Redooor\Redminportal\App\Models\Image();
        $image->path = 'dummy.jpg';
        $page->images()->save($image);

        $controller = new CharacterizationGalleryOverridePageController(new Page);
        $response = $controller->getImgremove($image->id);

        $this->assertSame(url('admin/pages/edit/' . $page->id), $response->getTargetUrl());
        $this->assertNull(\Redooor\Redminportal\App\Models\Image::find($image->id));

        $missing = $controller->getImgremove(999999);
        $this->assertSame(url('/admin/pages'), $missing->getTargetUrl());
    }

    /**
     * Test (Pass): a host subclass overriding postStore and adding helpers still creates a product
     */
    public function testHostProductSubclassCreatesProductLikeStockController()
    {
        Route::post('/admin/host-products/store', [CharacterizationHostProductController::class, 'postStore'])
            ->middleware(['redminsession', 'redminauth']);

        $this->call('POST', '/admin/host-products/store', array(
            'name' => 'Host subclass product',
            'short_description' => 'Host subclass short',
            'sku' => 'HOST-SUBCLASS-PRODUCT',
            'category_id' => 1
        ));

        $this->assertRedirectedTo('/admin/products');
        $this->assertSame(1, Product::count());
        $this->assertSame('Host subclass product', Product::first()->name);

        $this->call('POST', '/admin/products/store', array(
            'name' => 'Stock product',
            'short_description' => 'Stock short',
            'sku' => 'STOCK-PRODUCT',
            'category_id' => 1
        ));

        $this->assertRedirectedTo('/admin/products');
        $this->assertSame(2, Product::count());

        $controller = new CharacterizationHostProductController(new Product);
        $this->assertSame('host-product-save', $controller->save());
        $this->assertSame('host-product-validate', $controller->validate());
    }
}

/**
 * Host-like Page controller with a same-signature override and extra helper names.
 */
class CharacterizationHostPageController extends PageController
{
    public function postStore()
    {
        return parent::postStore();
    }

    public function save()
    {
        return 'host-page-save';
    }

    public function validate()
    {
        return 'host-page-validate';
    }
}

/**
 * Host-like Product controller with a same-signature override and extra helper names.
 */
class CharacterizationHostProductController extends ProductController
{
    public function postStore()
    {
        return parent::postStore();
    }

    public function save()
    {
        return 'host-product-save';
    }

    public function validate()
    {
        return 'host-product-validate';
    }
}

/**
 * Host-like Page controller that overrides the gallery methods used by the primary and order actions.
 */
class CharacterizationGalleryOverridePageController extends PageController
{
    protected function galleryModelClass(): string
    {
        return \Redooor\Redminportal\App\Models\Category::class;
    }

    protected function galleryPath(): string
    {
        return 'admin/elsewhere';
    }
}
