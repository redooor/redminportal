<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Image;

/**
 * The editor, tags and file input plugins expose per-container initialisers so a fetched
 * fragment can mount and tear down its own widgets without touching the page's instances.
 */
class WidgetPluginInitialisersMarkupTest extends RedminTestCase
{
    public function testEditorPartialDefinesPerContainerInitialisers()
    {
        $image = new Image;
        $image->path = 'gallery/image-1.jpg';

        $html = view('redminportal::plugins.editor', [
            'editorImages' => collect([$image]),
        ])->render();

        $this->assertStringContainsString('window.redminInitEditors', $html);
        $this->assertStringContainsString('window.redminDestroyEditors', $html);
        $this->assertStringContainsString('window.redminInitEditors(document)', $html);
        $this->assertStringContainsString("textarea.closest('[data-editor-images]')", $html);
        $this->assertStringContainsString('redminImages', $html);
        $this->assertStringContainsString("root === document ? pageImages : []", $html);
        $this->assertStringContainsString('image-1', $html);
    }

    public function testTagsPartialDefinesPerContainerInitialisers()
    {
        $html = view('redminportal::plugins.tagsinput')->render();

        $this->assertStringContainsString('window.redminInitTags', $html);
        $this->assertStringContainsString('window.redminDestroyTags', $html);
        $this->assertStringContainsString('window.redminInitTags(document)', $html);
        $this->assertStringContainsString('[data-redmin-tags]', $html);
        $this->assertStringContainsString('#tags, .tagsinput', $html);
        $this->assertStringContainsString('.closest(\'.bootstrap-tagsinput\')', $html);
    }

    public function testBuiltFileinputExposesReleaseHook()
    {
        $path = dirname(__DIR__, 2) . '/src/public/js/redmin-fileinput.min.js';

        $this->assertFileExists($path);
        $this->assertStringContainsString('redminFileinputRelease', file_get_contents($path));
    }
}
