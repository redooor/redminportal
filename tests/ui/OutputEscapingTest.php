<?php namespace Redooor\Redminportal\Test;

use Illuminate\Support\Facades\Auth;
use Redooor\Redminportal\App\Models\Category;
use Redooor\Redminportal\App\Models\Product;

class OutputEscapingTest extends RedminBrowserTestCase
{
    private $category;
    private $product;

    public function setUp(): void
    {
        parent::setUp();

        $this->seed('RedminSeeder');
        Auth::guard('redminguard')->loginUsingId(1);

        $this->category = new Category;
        $this->category->name = '<img src=x onerror=alert(1)>';
        $this->category->short_description = 'Short description';
        $this->category->long_description = '<p>ok</p><script>alert(1)</script><img src=x onerror=alert(1)>';
        $this->category->active = true;
        $this->category->order = 1;
        $this->category->save();

        $this->product = new Product;
        $this->product->name = '<img src=x onerror=alert(1)>';
        $this->product->short_description = '<script>alert(1)</script>';
        $this->product->long_description = '<p>ok</p><script>alert(1)</script>';
        $this->product->category_id = $this->category->id;
        $this->product->sku = 'XSS-SKU-1';
        $this->product->save();
    }

    public function testCategoryListEscapesStoredName()
    {
        $this->call('GET', '/admin/categories');

        $this->assertResponseOk();
        $this->response->assertSee('&lt;img', false);
        $this->response->assertDontSee('<img src=x onerror=alert(1)>', false);
        $this->assertNoRawEventHandler();
    }

    public function testCategoryDetailSanitisesDescription()
    {
        $this->call('GET', '/admin/categories/detail/' . $this->category->id);

        $this->assertResponseOk();
        $this->response->assertSee('&lt;img', false);
        $this->response->assertSee('<p>ok</p>', false);
        $this->response->assertDontSee('<script>alert(1)</script>', false);
        $this->assertNoRawEventHandler();
    }

    public function testProductViewEscapesStoredName()
    {
        $this->call('GET', '/admin/products');

        $this->assertResponseOk();
        $this->response->assertSee('&lt;img', false);
        $this->response->assertDontSee('<img src=x onerror=alert(1)>', false);
        $this->assertNoRawEventHandler();
    }

    public function testProductEditEscapesStoredDescription()
    {
        $this->call('GET', '/admin/products/edit/' . $this->product->id);

        $this->assertResponseOk();
        $this->response->assertDontSee('<script>alert(1)</script>', false);
        $this->assertNoRawEventHandler();
    }

    private function assertNoRawEventHandler()
    {
        $this->assertDoesNotMatchRegularExpression(
            '/<(?:img|script|svg|iframe|a|p|div|span)\b[^>]*\sonerror\s*=/i',
            $this->response->getContent()
        );
    }
}
