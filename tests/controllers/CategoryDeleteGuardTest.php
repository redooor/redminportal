<?php namespace Redooor\Redminportal\Test;

use Database\Seeders\RedminSeeder;
use Redooor\Redminportal\App\Models\Bundle;
use Redooor\Redminportal\App\Models\Category;
use Redooor\Redminportal\App\Models\Media;
use Redooor\Redminportal\App\Models\Module;
use Redooor\Redminportal\App\Models\Page;
use Redooor\Redminportal\App\Models\Portfolio;
use Redooor\Redminportal\App\Models\Post;
use Redooor\Redminportal\App\Models\Product;

class CategoryDeleteGuardTest extends RedminBrowserTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->seed(RedminSeeder::class);
        $this->loginRedminUser(1);
    }

    public function testRefusesCategoryWithBundles()
    {
        $category = $this->category();

        $this->content(Bundle::class, $category, array(
            'name' => 'Bundle',
            'sku' => 'BUNDLE-1',
            'short_description' => 'Short',
        ));

        $this->assertRefused($category, '1 bundle', Bundle::class);
    }

    public function testRefusesCategoryWithMedias()
    {
        $category = $this->category();

        $this->content(Media::class, $category, array(
            'name' => 'Media',
            'path' => 'media.pdf',
            'short_description' => 'Short',
        ));

        $this->assertRefused($category, '1 media', Media::class);
    }

    public function testRefusesCategoryWithModules()
    {
        $category = $this->category();

        $this->content(Module::class, $category, array(
            'name' => 'Module',
            'short_description' => 'Short',
        ));

        $this->assertRefused($category, '1 module', Module::class);
    }

    public function testRefusesCategoryWithPages()
    {
        $category = $this->category();

        $this->content(Page::class, $category, array(
            'title' => 'Page',
            'slug' => 'page',
            'content' => 'Body',
        ));

        $this->assertRefused($category, '1 page', Page::class);
    }

    public function testRefusesCategoryWithPortfolios()
    {
        $category = $this->category();

        $this->content(Portfolio::class, $category, array(
            'name' => 'Portfolio',
            'short_description' => 'Short',
        ));

        $this->assertRefused($category, '1 portfolio', Portfolio::class);
    }

    public function testRefusesCategoryWithPosts()
    {
        $category = $this->category();

        $this->content(Post::class, $category, array(
            'title' => 'Post',
            'slug' => 'post',
            'content' => 'Body',
        ));

        $this->assertRefused($category, '1 post', Post::class);
    }

    public function testRefusesCategoryWithProducts()
    {
        $category = $this->category();

        $this->content(Product::class, $category, array(
            'name' => 'Product',
            'sku' => 'PRODUCT-1',
            'short_description' => 'Short',
        ));

        $this->assertRefused($category, '1 product', Product::class);
    }

    public function testRefusalMessageNamesTheKindsAndCounts()
    {
        $category = $this->category();

        for ($index = 1; $index <= 3; $index++) {
            $this->content(Product::class, $category, array(
                'name' => 'Product ' . $index,
                'sku' => 'PRODUCT-' . $index,
                'short_description' => 'Short',
            ));
        }

        $this->content(Media::class, $category, array(
            'name' => 'Media',
            'path' => 'media.pdf',
            'short_description' => 'Short',
        ));

        $this->call('POST', '/admin/categories/delete/' . $category->id);

        $this->assertResponseStatus(302);
        $this->assertSessionHasErrors('deleteError');

        $message = session('errors')->getBag('default')->first('deleteError');

        $this->assertStringContainsString("The category 'Parent' cannot be deleted", $message);
        $this->assertStringContainsString('3 products', $message);
        $this->assertStringContainsString('1 media', $message);
        $this->assertStringContainsString('Move or delete them first.', $message);
    }

    public function testEmptyCategoryIsDeleted()
    {
        $category = $this->category();

        $this->call('POST', '/admin/categories/delete/' . $category->id);

        $this->assertResponseStatus(302);
        $this->assertNull(Category::find($category->id));
    }

    public function testCategoryWithOnlyChildCategoryUsesOldMessage()
    {
        $category = $this->category();
        $this->category('Child', $category->id);

        $this->call('POST', '/admin/categories/delete/' . $category->id);

        $this->assertResponseStatus(302);
        $this->assertSessionHasErrors('deleteError');
        $this->assertSame(
            "The category 'Parent' cannot be deleted because it has 1 children categories.",
            session('errors')->getBag('default')->first('deleteError')
        );
        $this->assertNotNull(Category::find($category->id));
        $this->assertSame(1, Category::where('category_id', $category->id)->count());
    }

    public function testContentIsUntouchedAfterRefusal()
    {
        $category = $this->category();

        $this->content(Product::class, $category, array(
            'name' => 'Product',
            'sku' => 'PRODUCT-1',
            'short_description' => 'Short',
        ));

        $this->content(Media::class, $category, array(
            'name' => 'Media',
            'path' => 'media.pdf',
            'short_description' => 'Short',
        ));

        $this->call('POST', '/admin/categories/delete/' . $category->id);

        $this->assertResponseStatus(302);
        $this->assertSessionHasErrors('deleteError');
        $this->assertNotNull(Category::find($category->id));
        $this->assertSame(1, Product::where('category_id', $category->id)->count());
        $this->assertSame(1, Media::where('category_id', $category->id)->count());
        $this->assertSame(
            array('medias' => 1, 'products' => 1),
            $category->fresh()->contentCounts()
        );
    }

    private function category($name = 'Parent', $parentId = null)
    {
        $category = new Category;
        $category->name = $name;
        $category->short_description = 'Short';
        $category->long_description = 'Long';
        $category->active = true;
        $category->order = 1;
        $category->category_id = $parentId;
        $category->save();

        return $category;
    }

    private function content($modelClass, Category $category, array $attributes)
    {
        $model = new $modelClass;

        foreach ($attributes as $key => $value) {
            $model->$key = $value;
        }

        $model->category_id = $category->id;
        $model->save();

        return $model;
    }

    private function assertRefused(Category $category, $messagePart, $modelClass)
    {
        $this->call('POST', '/admin/categories/delete/' . $category->id);

        $this->assertResponseStatus(302);
        $this->assertSessionHasErrors('deleteError');

        $message = session('errors')->getBag('default')->first('deleteError');

        $this->assertStringContainsString($messagePart, $message);
        $this->assertNotNull(Category::find($category->id));
        $this->assertSame(1, $modelClass::where('category_id', $category->id)->count());
    }
}
