<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Media;

/**
 * Covers clearing and keeping tags on media save.
 */
class TagsClearingMediaFixTest extends BaseControllerTest
{
    use CharacterizationSupport;

    public function setUp(): void
    {
        parent::setUp();

        $this->page = '/admin/medias';
        $this->viewhas = array(
            'singular' => 'media',
            'plural' => 'models'
        );
        $this->input = array(
            'create' => $this->mediaInput(array(
                'name' => 'Base media',
                'sku' => 'BASE-MEDIA'
            )),
            'edit' => $this->mediaInput(array(
                'id' => 1,
                'name' => 'Base media',
                'sku' => 'BASE-MEDIA'
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
        $this->postStore($this->mediaInput(array(
            'name' => 'Tagged media',
            'sku' => 'TAGGED-MEDIA',
            'tags' => 'alpha,beta'
        )));

        $this->assertRedirectedTo('/admin/medias');
        $media = Media::first();
        $this->assertSame(array('alpha', 'beta'), $this->tagNames($media));

        $this->postStore($this->mediaInput(array(
            'id' => $media->id,
            'name' => 'Tagged media updated',
            'sku' => 'TAGGED-MEDIA'
        )));

        $this->assertRedirectedTo('/admin/medias');
        $this->assertSame(array('alpha', 'beta'), $this->tagNames(Media::find($media->id)));

        $this->postStore($this->mediaInput(array(
            'id' => $media->id,
            'name' => 'Tagged media updated',
            'sku' => 'TAGGED-MEDIA',
            'tags' => ''
        )));

        $this->assertRedirectedTo('/admin/medias');
        $this->assertSame(array(), $this->tagNames(Media::find($media->id)));

        $this->postStore($this->mediaInput(array(
            'id' => $media->id,
            'name' => 'Tagged media updated',
            'sku' => 'TAGGED-MEDIA',
            'tags' => 'gamma,delta'
        )));

        $this->assertRedirectedTo('/admin/medias');
        $this->assertSame(array('gamma', 'delta'), $this->tagNames(Media::find($media->id)));

        $this->postStore($this->mediaInput(array(
            'id' => $media->id,
            'name' => 'Tagged media updated',
            'sku' => 'TAGGED-MEDIA',
            'tags' => null
        )));

        $this->assertRedirectedTo('/admin/medias');
        $this->assertSame(array(), $this->tagNames(Media::find($media->id)));
    }

    /**
     * Build a valid media input
     */
    private function mediaInput(array $overrides = array())
    {
        return array_merge(array(
            'name' => 'Media name',
            'sku' => 'MEDIA-SKU',
            'short_description' => 'Short',
            'long_description' => 'Long',
            'category_id' => 1
        ), $overrides);
    }
}
