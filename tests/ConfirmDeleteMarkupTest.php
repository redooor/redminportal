<?php namespace Redooor\Redminportal\Test;

use PHPUnit\Framework\TestCase;

/**
 * The delete confirmation modal is not part of the layout. Only views with a delete link (.btn-confirm) include it.
 */
class ConfirmDeleteMarkupTest extends TestCase
{
    private function views()
    {
        return array_filter(
            glob(dirname(__DIR__) . '/src/resources/views/{,*/}*.blade.php', GLOB_BRACE),
            function ($file) {
                return basename(dirname($file)) !== 'layouts' && basename($file) !== 'confirm-delete.blade.php';
            }
        );
    }

    public function testLayoutDoesNotRenderTheModalOnEveryPage()
    {
        $layout = file_get_contents(dirname(__DIR__) . '/src/resources/views/layouts/master.blade.php');

        $this->assertStringNotContainsString('confirm-modal', $layout);
        $this->assertStringContainsString("@stack('modals')", $layout);
        $this->assertStringContainsString("@stack('scripts')", $layout);
    }

    public function testEveryViewWithADeleteLinkIncludesTheModal()
    {
        $found = 0;

        foreach ($this->views() as $view) {
            $source = file_get_contents($view);

            if (strpos($source, 'btn-confirm') === false) {
                continue;
            }

            // categories/detail is loaded by Ajax into categories/view, which includes the modal
            if (basename($view) === 'detail.blade.php') {
                continue;
            }

            $found++;
            $this->assertStringContainsString("@include('redminportal::partials.confirm-delete')", $source, basename(dirname($view)) . '/' . basename($view) . ' has a delete link but no confirmation modal');
        }

        $this->assertGreaterThanOrEqual(25, $found);
    }

    public function testViewsWithoutADeleteLinkDoNotIncludeTheModal()
    {
        foreach ($this->views() as $view) {
            $source = file_get_contents($view);

            // uploaded-images has its own delete links; categories/view hosts the Ajax categories/detail with one
            $hasDeleteLink = strpos($source, 'btn-confirm') !== false || in_array(basename($view), ['uploaded-images.blade.php'])
                || substr($view, -strlen('categories/view.blade.php')) === 'categories/view.blade.php';

            if ($hasDeleteLink) {
                continue;
            }

            $this->assertStringNotContainsString('partials.confirm-delete', $source, basename(dirname($view)) . '/' . basename($view) . ' includes the modal without a delete link');
        }
    }

    public function testLayoutsOfViewsWithTheModalRenderItsStacks()
    {
        $viewsDir = dirname(__DIR__) . '/src/resources/views/';

        foreach ($this->views() as $view) {
            $source = file_get_contents($view);

            if (strpos($source, "partials.confirm-delete'") === false || ! preg_match("/@extends\\('redminportal::layouts\\.(\\w+)'\\)/", $source, $layout)) {
                continue;
            }

            $layoutSource = file_get_contents($viewsDir . 'layouts/' . $layout[1] . '.blade.php');

            $this->assertStringContainsString("@stack('modals')", $layoutSource, basename($view) . ' extends layouts.' . $layout[1] . ' which does not render the modal');
            $this->assertStringContainsString("@stack('scripts')", $layoutSource, basename($view) . ' extends layouts.' . $layout[1] . ' which does not render the handler');
        }
    }
}
