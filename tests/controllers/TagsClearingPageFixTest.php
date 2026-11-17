<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Page;

/**
 * Covers clearing, keeping and rejecting tags on page save.
 */
class TagsClearingPageFixTest extends BaseControllerTest
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
            'create' => $this->pageInput(array(
                'title' => 'Base page',
                'slug' => 'base page'
            )),
            'edit' => $this->pageInput(array(
                'id' => 1,
                'title' => 'Base page',
                'slug' => 'base page'
            ))
        );

        $this->configureImageUploads();
    }

    public function tearDown(): void
    {
        $this->cleanupImageUploads();

        parent::tearDown();
    }

    /**
     * Test (Pass): omitted tags are kept, empty and null clear them and values replace them
     */
    public function testOmittedTagsAreKeptAndEmptyOrNullTagsClearThem()
    {
        $this->postStore($this->pageInput(array(
            'title' => 'Tagged page',
            'slug' => 'tagged page',
            'tags' => 'alpha,beta'
        )));

        $this->assertRedirectedTo('/admin/pages');
        $page = Page::first();
        $this->assertSame(array('alpha', 'beta'), $this->tagNames($page));

        $this->postStore($this->pageInput(array(
            'id' => $page->id,
            'title' => 'Tagged page updated',
            'slug' => 'tagged page'
        )));

        $this->assertRedirectedTo('/admin/pages');
        $this->assertSame(array('alpha', 'beta'), $this->tagNames(Page::find($page->id)));

        $this->postStore($this->pageInput(array(
            'id' => $page->id,
            'title' => 'Tagged page updated',
            'slug' => 'tagged page',
            'tags' => ''
        )));

        $this->assertRedirectedTo('/admin/pages');
        $this->assertSame(array(), $this->tagNames(Page::find($page->id)));

        $this->postStore($this->pageInput(array(
            'id' => $page->id,
            'title' => 'Tagged page updated',
            'slug' => 'tagged page',
            'tags' => 'gamma,delta'
        )));

        $this->assertRedirectedTo('/admin/pages');
        $this->assertSame(array('gamma', 'delta'), $this->tagNames(Page::find($page->id)));

        $this->postStore($this->pageInput(array(
            'id' => $page->id,
            'title' => 'Tagged page updated',
            'slug' => 'tagged page',
            'tags' => null
        )));

        $this->assertRedirectedTo('/admin/pages');
        $this->assertSame(array(), $this->tagNames(Page::find($page->id)));
    }

    /**
     * Test (Fail): an array tags input is rejected before the page or its tags change
     */
    public function testArrayTagsAreRejectedWithoutChangingPageOrTags()
    {
        $this->postStore($this->pageInput(array(
            'title' => 'Tagged page',
            'slug' => 'tagged page',
            'content' => 'Original body',
            'tags' => 'alpha'
        )));

        $page = Page::first();
        $this->assertSame('Original body', $page->content);

        $this->assertValidationFailure(
            '/admin/pages/edit/' . $page->id,
            $this->pageInput(array(
                'id' => $page->id,
                'title' => 'Changed title',
                'slug' => 'tagged page',
                'content' => 'Changed body',
                'tags' => array('news')
            )),
            array('tags' => 'The tags field must be a string.')
        );

        $page = Page::find($page->id);
        $this->assertSame('Tagged page', $page->title);
        $this->assertSame('Original body', $page->content);
        $this->assertSame(array('alpha'), $this->tagNames($page));
    }

    /**
     * Build a valid page input
     */
    private function pageInput(array $overrides = array())
    {
        return array_merge(array(
            'title' => 'Page title',
            'slug' => 'page title',
            'content' => 'Page body',
            'private' => '',
            'category_id' => 1
        ), $overrides);
    }
}
