<?php namespace Redooor\Redminportal\Test;

use Database\Seeders\RedminSeeder;
use Illuminate\Support\Facades\Auth;
use Redooor\Redminportal\App\Models\Page;
use Redooor\Redminportal\App\Models\Tag;

class TagAddTagTest extends BaseModelTest
{
    public function setUp(): void
    {
        parent::setUp();

        $this->prepare(new Tag, array(
            'name' => 'news'
        ));
    }

    private function createPage($title = 'Page title', $slug = 'page-title')
    {
        $page = new Page;
        $page->title = $title;
        $page->slug = $slug;
        $page->content = 'Page content';
        $page->save();

        return $page;
    }

    public function testExistingTagIsReusedAfterTrimAndLowercase()
    {
        $existing = new Tag;
        $existing->name = 'news';
        $existing->save();

        $page = $this->createPage();

        $this->assertTrue(Tag::addTag($page, ' News '));
        $this->assertSame(1, Tag::count());
        $this->assertSame($existing->id, $page->tags()->first()->id);
        $this->assertSame(array('news'), $page->tags()->orderBy('tags.id')->pluck('name')->all());
    }

    public function testCaseVariantsMapToOneTagRow()
    {
        $firstPage = $this->createPage('First page', 'first-page');
        $secondPage = $this->createPage('Second page', 'second-page');

        $this->assertTrue(Tag::addTag($firstPage, 'NEWS'));
        $this->assertTrue(Tag::addTag($secondPage, 'news'));

        $this->assertSame(1, Tag::count());
        $this->assertSame($firstPage->tags()->first()->id, $secondPage->tags()->first()->id);
    }

    public function testWhitespaceOnlyNameReturnsFalseAndCreatesNothing()
    {
        $page = $this->createPage();

        $this->assertFalse(Tag::addTag($page, '   '));
        $this->assertSame(0, Tag::count());
        $this->assertSame(array(), $page->tags()->pluck('name')->all());
    }

    public function testZeroNameReturnsFalseAndCreatesNothing()
    {
        $page = $this->createPage();

        $this->assertFalse(Tag::addTag($page, '0'));
        $this->assertSame(0, Tag::count());
        $this->assertSame(array(), $page->tags()->pluck('name')->all());
    }

    public function testCommaListThroughPageControllerSkipsBlankTags()
    {
        $this->seed(RedminSeeder::class);
        $this->loginRedminUser(1);

        $response = $this->call('POST', '/admin/pages/store', array(
            'title' => 'Comma tags page',
            'slug' => 'comma tags page',
            'content' => 'Body',
            'tags' => 'news, ,events'
        ));

        $response->assertRedirect('/admin/pages');
        $this->assertSame(array('events', 'news'), Tag::orderBy('name')->pluck('name')->all());
        $this->assertSame(
            array('news', 'events'),
            Page::first()->tags()->orderBy('tags.id')->pluck('name')->all()
        );
    }
}
