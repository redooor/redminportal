<?php namespace Redooor\Redminportal\Test;

use Database\Seeders\RedminSeeder;
use Illuminate\Support\Facades\Auth;
use Redooor\Redminportal\App\Helpers\RImage;
use Redooor\Redminportal\App\Models\Category;
use Redooor\Redminportal\App\Models\Image;
use Redooor\Redminportal\App\Models\Page;
use Redooor\Redminportal\App\Models\Post;

class ImageGalleryControllerTest extends RedminBrowserTestCase
{
    private $relativeRoot;

    public function setUp(): void
    {
        parent::setUp();

        $this->seed(RedminSeeder::class);
        Auth::guard('redminguard')->loginUsingId(1);

        $this->relativeRoot = 'assets/img/redmin_gallery_' . uniqid();
    }

    public function tearDown(): void
    {
        if ($this->relativeRoot) {
            $root = getcwd() . '/' . $this->relativeRoot;
            $this->deleteFolder($root);
            @rmdir(dirname($root));
            @rmdir(dirname(dirname($root)));
        }

        $this->relativeRoot = null;

        parent::tearDown();
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

    private function createPage()
    {
        $page = new Page;
        $page->title = 'Gallery page ' . uniqid();
        $page->slug = 'gallery-page-' . uniqid();
        $page->content = 'Gallery page content';
        $page->save();

        return $page;
    }

    private function storedImage($model, $filename, $sortOrder, $isPrimary = false)
    {
        $path = $this->relativeRoot . '/stored/' . $filename;
        $absolute = getcwd() . '/' . $path;
        $thumbnail = getcwd() . '/' . (new RImage)->getUrl($path, 'thumb');

        foreach (array($absolute, $thumbnail) as $file) {
            if (! is_dir(dirname($file))) {
                mkdir(dirname($file), 0777, true);
            }
            file_put_contents($file, 'test image file');
        }

        $image = new Image;
        $image->path = $path;
        $image->sort_order = $sortOrder;
        $image->is_primary = $isPrimary;
        $model->images()->save($image);

        return $image;
    }

    private function orderedIds($model)
    {
        return array_map('intval', $model->images()->pluck('id')->all());
    }

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

    public function testMakePrimaryFlipsPrimaryAndRedirectsForCategory()
    {
        $category = $this->createCategory();
        $first = $this->storedImage($category, 'first.png', 1, true);
        $second = $this->storedImage($category, 'second.png', 2);
        $this->storedImage($category, 'third.png', 3);

        $this->call('POST', '/admin/categories/imgprimary/' . $second->id);

        $this->assertRedirectedTo('/admin/categories/edit/' . $category->id);
        $this->assertSame(1, (int) Image::find($second->id)->is_primary);
        $this->assertSame(0, (int) Image::find($first->id)->is_primary);
        $this->assertSame(1, $category->images()->where('is_primary', 1)->count());
        $this->assertSame($second->id, $category->images()->first()->id);
    }

    public function testMakePrimaryFlipsPrimaryAndRedirectsForPost()
    {
        $post = $this->createPost();
        $first = $this->storedImage($post, 'first.png', 1, true);
        $second = $this->storedImage($post, 'second.png', 2);
        $this->storedImage($post, 'third.png', 3);

        $this->call('POST', '/admin/posts/imgprimary/' . $second->id);

        $this->assertRedirectedTo('/admin/posts/edit/' . $post->id);
        $this->assertSame(1, (int) Image::find($second->id)->is_primary);
        $this->assertSame(0, (int) Image::find($first->id)->is_primary);
        $this->assertSame(1, $post->images()->where('is_primary', 1)->count());
        $this->assertSame($second->id, $post->images()->first()->id);
    }

    public function testMissingImageRedirectsWithError()
    {
        $this->call('POST', '/admin/categories/imgprimary/99999');

        $this->assertRedirectedTo('/admin/categories');
        $this->assertSessionHasErrors('deleteError');
    }

    public function testMoveLaterAndEarlierSwapsOrderForCategory()
    {
        $category = $this->createCategory();
        $first = $this->storedImage($category, 'first.png', 1, true);
        $second = $this->storedImage($category, 'second.png', 2);
        $third = $this->storedImage($category, 'third.png', 3);

        $this->call('POST', '/admin/categories/imgmove/' . $second->id . '/later');

        $this->assertRedirectedTo('/admin/categories/edit/' . $category->id);
        $this->assertSame(3, (int) Image::find($second->id)->sort_order);
        $this->assertSame(2, (int) Image::find($third->id)->sort_order);
        $this->assertSame(array($first->id, $third->id, $second->id), $this->orderedIds($category));

        $this->call('POST', '/admin/categories/imgmove/' . $second->id . '/earlier');

        $this->assertRedirectedTo('/admin/categories/edit/' . $category->id);
        $this->assertSame(2, (int) Image::find($second->id)->sort_order);
        $this->assertSame(3, (int) Image::find($third->id)->sort_order);
        $this->assertSame(array($first->id, $second->id, $third->id), $this->orderedIds($category));
    }

    public function testMoveLaterAndEarlierSwapsOrderForPost()
    {
        $post = $this->createPost();
        $first = $this->storedImage($post, 'first.png', 1, true);
        $second = $this->storedImage($post, 'second.png', 2);
        $third = $this->storedImage($post, 'third.png', 3);

        $this->call('POST', '/admin/posts/imgmove/' . $second->id . '/later');

        $this->assertRedirectedTo('/admin/posts/edit/' . $post->id);
        $this->assertSame(3, (int) Image::find($second->id)->sort_order);
        $this->assertSame(2, (int) Image::find($third->id)->sort_order);
        $this->assertSame(array($first->id, $third->id, $second->id), $this->orderedIds($post));

        $this->call('POST', '/admin/posts/imgmove/' . $second->id . '/earlier');

        $this->assertRedirectedTo('/admin/posts/edit/' . $post->id);
        $this->assertSame(2, (int) Image::find($second->id)->sort_order);
        $this->assertSame(3, (int) Image::find($third->id)->sort_order);
        $this->assertSame(array($first->id, $second->id, $third->id), $this->orderedIds($post));
    }

    public function testMovingAtEndsIsNoOpForCategory()
    {
        $category = $this->createCategory();
        $first = $this->storedImage($category, 'first.png', 1, true);
        $second = $this->storedImage($category, 'second.png', 2);
        $third = $this->storedImage($category, 'third.png', 3);

        $this->call('POST', '/admin/categories/imgmove/' . $third->id . '/later');

        $this->assertRedirectedTo('/admin/categories/edit/' . $category->id);
        $this->assertSame(1, (int) Image::find($first->id)->sort_order);
        $this->assertSame(2, (int) Image::find($second->id)->sort_order);
        $this->assertSame(3, (int) Image::find($third->id)->sort_order);

        // The primary image always sorts first, so moving it has no visible effect
        $this->call('POST', '/admin/categories/imgmove/' . $first->id . '/later');

        $this->assertRedirectedTo('/admin/categories/edit/' . $category->id);
        $this->assertSame(array($first->id, $second->id, $third->id), $this->orderedIds($category));
        $this->assertSame(1, (int) Image::find($first->id)->is_primary);
    }

    public function testMovingAtEndsIsNoOpForPost()
    {
        $post = $this->createPost();
        $first = $this->storedImage($post, 'first.png', 1, true);
        $second = $this->storedImage($post, 'second.png', 2);
        $third = $this->storedImage($post, 'third.png', 3);

        $this->call('POST', '/admin/posts/imgmove/' . $third->id . '/later');

        $this->assertRedirectedTo('/admin/posts/edit/' . $post->id);
        $this->assertSame(1, (int) Image::find($first->id)->sort_order);
        $this->assertSame(2, (int) Image::find($second->id)->sort_order);
        $this->assertSame(3, (int) Image::find($third->id)->sort_order);

        $this->call('POST', '/admin/posts/imgmove/' . $first->id . '/earlier');

        $this->assertRedirectedTo('/admin/posts/edit/' . $post->id);
        $this->assertSame(array($first->id, $second->id, $third->id), $this->orderedIds($post));
    }

    public function testInvalidDirectionReturns404()
    {
        $category = $this->createCategory();
        $image = $this->storedImage($category, 'first.png', 1, true);

        $this->call('POST', '/admin/categories/imgmove/' . $image->id . '/sideways');

        $this->assertResponseStatus(404);
        $this->assertSame(1, (int) Image::find($image->id)->sort_order);
    }

    public function testForeignImageIsRefusedForCategory()
    {
        $category = $this->createCategory();
        $categoryImage = $this->storedImage($category, 'category.png', 1, true);

        $page = $this->createPage();
        $pageImage = $this->storedImage($page, 'page.png', 1, true);
        $pageSecond = $this->storedImage($page, 'page-second.png', 2);
        $pageOrder = $this->orderedIds($page);

        $this->call('POST', '/admin/categories/imgprimary/' . $pageImage->id);

        $this->assertRedirectedTo('/admin/categories');
        $this->assertSessionHasErrors('deleteError');
        $this->assertSame(1, (int) Image::find($pageImage->id)->is_primary);
        $this->assertSame(1, (int) Image::find($categoryImage->id)->is_primary);

        $this->call('POST', '/admin/categories/imgmove/' . $pageImage->id . '/later');

        $this->assertRedirectedTo('/admin/categories');
        $this->assertSessionHasErrors('deleteError');
        $this->assertSame($pageOrder, $this->orderedIds($page));
        $this->assertSame(1, (int) Image::find($pageImage->id)->sort_order);
        $this->assertSame(2, (int) Image::find($pageSecond->id)->sort_order);
    }

    public function testGuestIsRedirectedToLogin()
    {
        $category = $this->createCategory();
        $image = $this->storedImage($category, 'first.png', 1, true);

        Auth::guard('redminguard')->logout();

        $this->call('POST', '/admin/categories/imgprimary/' . $image->id);

        $this->assertRedirectedTo('/login');
        $this->assertSame(1, (int) Image::find($image->id)->is_primary);
    }
}
