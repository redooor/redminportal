<?php namespace Redooor\Redminportal\Test;

use PHPUnit\Framework\TestCase;

/**
 * The "view" button on uploaded images opens the lightbox: every such link must opt in, and the layout must provide the modal and the script.
 */
class ImageLightboxMarkupTest extends TestCase
{
    public function testEveryViewImageLinkOpensTheLightbox()
    {
        $views = array_merge(
            glob(dirname(__DIR__) . '/src/resources/views/*/edit*.blade.php'),
            glob(dirname(__DIR__) . '/src/resources/views/partials/uploaded-images.blade.php')
        );
        $found = 0;

        foreach ($views as $view) {
            preg_match_all('/<a [^\n]*>\s*<i class="fa-solid fa-eye"><\/i>/', file_get_contents($view), $links);

            foreach ($links[0] as $link) {
                $found++;
                $this->assertStringContainsString('data-lightbox="uploaded-images"', $link, basename(dirname($view)) . '/' . basename($view) . ' has a view link that opens a new tab');
                $this->assertStringContainsString('data-lightbox-title=', $link);
                $this->assertStringContainsString('aria-label=', $link, 'icon-only link needs an accessible name');
            }
        }

        $this->assertGreaterThanOrEqual(11, $found);
    }

    public function testLayoutProvidesTheLightboxModalAndScript()
    {
        $layout = file_get_contents(dirname(__DIR__) . '/src/resources/views/layouts/master.blade.php');

        foreach (['id="lightbox-modal"', 'id="lightbox-image"', 'id="lightbox-title"', 'id="lightbox-previous"', 'id="lightbox-next"', 'redmin-lightbox.min.js'] as $needle) {
            $this->assertStringContainsString($needle, $layout);
        }
    }
}
