<?php namespace Redooor\Redminportal\Test;

use Database\Seeders\RedminSeeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Redooor\Redminportal\App\Helpers\RImage;
use Redooor\Redminportal\App\Models\Bundle;
use Redooor\Redminportal\App\Models\Category;
use Redooor\Redminportal\App\Models\Group;
use Redooor\Redminportal\App\Models\Image;
use Redooor\Redminportal\App\Models\Page;
use Redooor\Redminportal\App\Models\Post;
use Redooor\Redminportal\App\Models\Product;
use Redooor\Redminportal\App\Models\User;

/**
 * The create/edit forms use one multi-image picker and the edit galleries expose the primary and ordering actions
 * only to a user who may update the module.
 */
class ImageGalleryMarkupTest extends RedminBrowserTestCase
{
    private $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->seed(RedminSeeder::class);

        $this->user = new User;
        $this->user->email = 'peter@test.com';
        $this->user->password = Hash::make('test');
        $this->user->first_name = 'Peter';
        $this->user->last_name = 'Tester';
        $this->user->activated = 1;
        $this->user->save();
    }

    private function createCategory()
    {
        $category = new Category;
        $category->name = 'Gallery category ' . uniqid();
        $category->short_description = 'Gallery category';
        $category->save();

        return $category;
    }

    private function createPost()
    {
        $post = new Post;
        $post->title = 'Gallery post ' . uniqid();
        $post->slug = 'gallery-post-' . uniqid();
        $post->content = 'Gallery post content';
        $post->save();

        return $post;
    }

    private function createProduct()
    {
        $product = new Product;
        $product->name = 'Gallery product ' . uniqid();
        $product->sku = 'GALLERY-' . uniqid();
        $product->short_description = 'Gallery product';
        $product->save();

        return $product;
    }

    private function createBundle()
    {
        $bundle = new Bundle;
        $bundle->name = 'Gallery bundle ' . uniqid();
        $bundle->sku = 'GALLERY-BUNDLE-' . uniqid();
        $bundle->short_description = 'Gallery bundle';
        $bundle->save();

        return $bundle;
    }

    private function createPage()
    {
        $page = new Page;
        $page->title = 'Gallery page ' . uniqid();
        $page->slug = 'gallery-page-' . uniqid();
        $page->content = 'Gallery page content';
        $page->save();

        return $page;
    }

    private function addImages($model, $count = 3)
    {
        $images = array();

        for ($i = 1; $i <= $count; $i++) {
            $image = new Image;
            $image->path = 'gallery/image-' . $i . '.jpg';
            $image->sort_order = $i;
            $image->is_primary = ($i === 1);
            $model->images()->save($image);
            $images[] = $image;
        }

        return $images;
    }

    private function galleryItems($html)
    {
        $document = new \DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="utf-8" ?>' . $html);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $items = array();
        $xpath = new \DOMXPath($document);

        foreach ($xpath->query('//*[@data-image-id]') as $node) {
            $items[(int) $node->getAttribute('data-image-id')] = $document->saveHTML($node);
        }

        return $items;
    }

    private function group(array $permissions)
    {
        $group = new Group;
        $group->name = 'Gallery limited ' . uniqid();
        $group->permissions = json_encode($permissions);
        $group->save();

        return $group;
    }

    private function renderGalleryAs(array $permissions, Page $page)
    {
        $this->user->groups()->save($this->group($permissions));
        Auth::guard('redminguard')->loginUsingId($this->user->id);

        return view('redminportal::partials.image-gallery', [
            'images' => $page->images,
            'imagine' => new RImage,
            'base' => 'admin/pages',
            'alt' => $page->title,
        ])->render();
    }

    public function testImagePickerRendersMultipleInputOnCreateAndEditPages()
    {
        Auth::guard('redminguard')->loginUsingId(1);

        $category = $this->createCategory();
        $post = $this->createPost();
        $product = $this->createProduct();

        $pages = array(
            '/admin/categories/create',
            '/admin/categories/edit/' . $category->id,
            '/admin/posts/create',
            '/admin/posts/edit/' . $post->id,
            '/admin/products/create',
            '/admin/products/edit/' . $product->id,
        );

        foreach ($pages as $page) {
            $response = $this->call('GET', $page);
            $response->assertOk();
            $html = $response->getContent();

            $this->assertMatchesRegularExpression('/<input[^>]*name="images\[\]"[^>]*>/', $html, $page . ' has no images[] picker');
            $this->assertMatchesRegularExpression('/<input[^>]*name="images\[\]"[^>]*\bmultiple\b/', $html, $page . ' picker is not multiple');
            $this->assertMatchesRegularExpression('/<input[^>]*name="images\[\]"[^>]*\baccept="image\/\*"/', $html, $page . ' picker does not accept images');
            $this->assertStringNotContainsString('name="image"', $html, $page . ' still has the single-image input');
        }
    }

    public function testGalleryShowsPrimaryBadgeAndHidesInvalidActions()
    {
        Auth::guard('redminguard')->loginUsingId(1);

        $post = $this->createPost();
        list($first, $second, $third) = $this->addImages($post);

        $response = $this->call('GET', '/admin/posts/edit/' . $post->id);
        $response->assertOk();
        $html = $response->getContent();
        $items = $this->galleryItems($html);

        $this->assertSame(array($first->id, $second->id, $third->id), array_keys($items));

        $this->assertStringContainsString('redmin-gallery-primary', $items[$first->id]);
        $this->assertStringNotContainsString('redmin-gallery-primary', $items[$second->id]);
        $this->assertStringNotContainsString('redmin-gallery-primary', $items[$third->id]);

        $this->assertStringNotContainsString('/admin/posts/imgprimary/' . $first->id . '"', $html);
        $this->assertStringContainsString('/admin/posts/imgprimary/' . $second->id . '"', $items[$second->id]);
        $this->assertStringContainsString('/admin/posts/imgprimary/' . $third->id . '"', $items[$third->id]);

        $this->assertStringNotContainsString('/admin/posts/imgmove/' . $first->id . '/', $html);

        $this->assertStringNotContainsString('/admin/posts/imgmove/' . $second->id . '/earlier', $html);
        $this->assertStringContainsString('/admin/posts/imgmove/' . $second->id . '/later', $items[$second->id]);
        $this->assertStringContainsString('/admin/posts/imgmove/' . $third->id . '/earlier', $items[$third->id]);
        $this->assertStringNotContainsString('/admin/posts/imgmove/' . $third->id . '/later', $html);

        $this->assertStringContainsString('/admin/posts/imgremove/' . $first->id . '"', $items[$first->id]);
        $this->assertStringContainsString('/admin/posts/imgremove/' . $second->id . '"', $items[$second->id]);
        $this->assertStringContainsString('/admin/posts/imgremove/' . $third->id . '"', $items[$third->id]);
    }

    public function testBundleGalleryUsesItsLegacyImageDeleteRoute()
    {
        Auth::guard('redminguard')->loginUsingId(1);

        $bundle = $this->createBundle();
        list($first, $second) = $this->addImages($bundle);

        $response = $this->call('GET', '/admin/bundles/edit/' . $bundle->id);
        $response->assertOk();
        $html = $response->getContent();

        $this->assertStringContainsString('/admin/bundles/imgprimary/' . $second->id . '"', $html);
        $this->assertStringContainsString('/admin/bundles/imgmove/' . $second->id . '/later', $html);
        $this->assertStringContainsString('/admin/images/delete/' . $first->id . '"', $html);
    }

    public function testViewOnlyUserSeesNoGalleryActions()
    {
        $page = $this->createPage();
        $this->addImages($page);

        $html = $this->renderGalleryAs(array('admin.pages.view' => 1), $page);

        $this->assertStringNotContainsString('/admin/pages/imgprimary/', $html);
        $this->assertStringNotContainsString('/admin/pages/imgmove/', $html);
        $this->assertStringNotContainsString('/admin/pages/imgremove/', $html);
        $this->assertStringContainsString('btn-copy', $html);
        $this->assertStringContainsString('data-lightbox="uploaded-images"', $html);
    }

    public function testUpdateUserSeesGalleryActions()
    {
        $page = $this->createPage();
        list($first, $second) = $this->addImages($page);

        $html = $this->renderGalleryAs(array('admin.pages.view' => 1, 'admin.pages.update' => 1), $page);

        $this->assertStringContainsString('/admin/pages/imgprimary/' . $second->id . '"', $html);
        $this->assertStringContainsString('/admin/pages/imgmove/' . $second->id . '/later', $html);
        $this->assertStringContainsString('/admin/pages/imgremove/' . $first->id . '"', $html);
    }
}
