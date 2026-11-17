<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Post;

/**
 * Covers clearing, keeping and rejecting tags on post save.
 */
class TagsClearingPostFixTest extends BaseControllerTest
{
    use CharacterizationSupport;

    public function setUp(): void
    {
        parent::setUp();

        $this->page = '/admin/posts';
        $this->viewhas = array(
            'singular' => 'post',
            'plural' => 'models'
        );
        $this->input = array(
            'create' => $this->postInput(array(
                'title' => 'Base post',
                'slug' => 'base post'
            )),
            'edit' => $this->postInput(array(
                'id' => 1,
                'title' => 'Base post',
                'slug' => 'base post'
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
        $this->postStore($this->postInput(array(
            'title' => 'Tagged post',
            'slug' => 'tagged post',
            'tags' => 'alpha,beta'
        )));

        $this->assertRedirectedTo('/admin/posts');
        $post = Post::first();
        $this->assertSame(array('alpha', 'beta'), $this->tagNames($post));

        $this->postStore($this->postInput(array(
            'id' => $post->id,
            'title' => 'Tagged post updated',
            'slug' => 'tagged post'
        )));

        $this->assertRedirectedTo('/admin/posts');
        $this->assertSame(array('alpha', 'beta'), $this->tagNames(Post::find($post->id)));

        $this->postStore($this->postInput(array(
            'id' => $post->id,
            'title' => 'Tagged post updated',
            'slug' => 'tagged post',
            'tags' => ''
        )));

        $this->assertRedirectedTo('/admin/posts');
        $this->assertSame(array(), $this->tagNames(Post::find($post->id)));

        $this->postStore($this->postInput(array(
            'id' => $post->id,
            'title' => 'Tagged post updated',
            'slug' => 'tagged post',
            'tags' => 'gamma,delta'
        )));

        $this->assertRedirectedTo('/admin/posts');
        $this->assertSame(array('gamma', 'delta'), $this->tagNames(Post::find($post->id)));

        $this->postStore($this->postInput(array(
            'id' => $post->id,
            'title' => 'Tagged post updated',
            'slug' => 'tagged post',
            'tags' => null
        )));

        $this->assertRedirectedTo('/admin/posts');
        $this->assertSame(array(), $this->tagNames(Post::find($post->id)));
    }

    /**
     * Test (Fail): an array tags input is rejected before the post or its tags change
     */
    public function testArrayTagsAreRejectedWithoutChangingPostOrTags()
    {
        $this->postStore($this->postInput(array(
            'title' => 'Tagged post',
            'slug' => 'tagged post',
            'content' => 'Original body',
            'tags' => 'alpha'
        )));

        $post = Post::first();
        $this->assertSame('Original body', $post->content);

        $this->assertValidationFailure(
            '/admin/posts/edit/' . $post->id,
            $this->postInput(array(
                'id' => $post->id,
                'title' => 'Changed title',
                'slug' => 'tagged post',
                'content' => 'Changed body',
                'tags' => array('news')
            )),
            array('tags' => 'The tags field must be a string.')
        );

        $post = Post::find($post->id);
        $this->assertSame('Tagged post', $post->title);
        $this->assertSame('Original body', $post->content);
        $this->assertSame(array('alpha'), $this->tagNames($post));
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
