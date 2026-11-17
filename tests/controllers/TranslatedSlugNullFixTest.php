<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Page;
use Redooor\Redminportal\App\Models\Post;

/**
 * Covers null translated slugs and the normal space replacement on page and post save.
 */
class TranslatedSlugNullFixTest extends BaseControllerTest
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
     * Test (Pass): a missing translated slug is stored as null for pages and posts
     */
    public function testMissingTranslatedSlugIsStoredAsNull()
    {
        $this->postStore($this->pageInput(array(
            'title' => 'No translated slug',
            'slug' => 'no translated slug',
            'cn_title' => 'CN title',
            'cn_content' => 'CN content'
        )));

        $this->call('POST', '/admin/posts/store', $this->postInput(array(
            'title' => 'No translated slug',
            'slug' => 'no translated slug',
            'cn_title' => 'CN title',
            'cn_content' => 'CN content'
        )));

        $this->assertTranslations(Page::first(), array(
            'cn' => array(
                'title' => 'CN title',
                'slug' => null,
                'content' => 'CN content'
            )
        ));

        $this->assertTranslations(Post::first(), array(
            'cn' => array(
                'title' => 'CN title',
                'slug' => null,
                'content' => 'CN content'
            )
        ));
    }

    /**
     * Test (Pass): a blank translated slug is stored as null for pages and posts
     */
    public function testBlankTranslatedSlugIsStoredAsNull()
    {
        $this->postStore($this->pageInput(array(
            'title' => 'Blank translated slug',
            'slug' => 'blank translated slug',
            'cn_title' => 'CN title',
            'cn_slug' => '   ',
            'cn_content' => 'CN content'
        )));

        $this->call('POST', '/admin/posts/store', $this->postInput(array(
            'title' => 'Blank translated slug',
            'slug' => 'blank translated slug',
            'cn_title' => 'CN title',
            'cn_slug' => '',
            'cn_content' => 'CN content'
        )));

        $this->assertTranslations(Page::first(), array(
            'cn' => array(
                'title' => 'CN title',
                'slug' => null,
                'content' => 'CN content'
            )
        ));

        $this->assertTranslations(Post::first(), array(
            'cn' => array(
                'title' => 'CN title',
                'slug' => null,
                'content' => 'CN content'
            )
        ));
    }

    /**
     * Test (Pass): a normal translated slug still replaces spaces with underscores
     */
    public function testNormalTranslatedSlugReplacesSpacesWithUnderscores()
    {
        $this->postStore($this->pageInput(array(
            'title' => 'Translated slug',
            'slug' => 'translated slug',
            'cn_title' => 'CN title',
            'cn_slug' => 'CN slug here',
            'cn_content' => 'CN content'
        )));

        $this->call('POST', '/admin/posts/store', $this->postInput(array(
            'title' => 'Translated slug',
            'slug' => 'translated slug',
            'cn_title' => 'CN title',
            'cn_slug' => 'CN slug here',
            'cn_content' => 'CN content'
        )));

        $this->assertTranslations(Page::first(), array(
            'cn' => array(
                'title' => 'CN title',
                'slug' => 'CN_slug_here',
                'content' => 'CN content'
            )
        ));

        $this->assertTranslations(Post::first(), array(
            'cn' => array(
                'title' => 'CN title',
                'slug' => 'CN_slug_here',
                'content' => 'CN content'
            )
        ));
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

    /**
     * Build a valid post input
     */
    private function postInput(array $overrides = array())
    {
        return array_merge(array(
            'title' => 'Post title',
            'slug' => 'post title',
            'content' => 'Post body',
            'private' => '',
            'featured' => '',
            'category_id' => 1
        ), $overrides);
    }
}
