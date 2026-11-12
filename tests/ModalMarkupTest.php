<?php namespace Redooor\Redminportal\Test;

use PHPUnit\Framework\TestCase;

/**
 * Every modal needs tabindex="-1", otherwise Bootstrap cannot focus it and the Escape key does not close it.
 */
class ModalMarkupTest extends TestCase
{
    public function testEveryModalCanBeClosedWithEscape()
    {
        $views = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(dirname(__DIR__) . '/src/resources/views'));
        $found = 0;

        foreach ($views as $view) {
            if (substr($view->getFilename(), -10) !== '.blade.php') {
                continue;
            }

            preg_match_all('/<div [^>]*class="modal fade"[^>]*>/', file_get_contents($view->getPathname()), $modals);

            foreach ($modals[0] as $modal) {
                $found++;
                $this->assertStringContainsString('tabindex="-1"', $modal, $view->getFilename() . ' has a modal without tabindex');
            }
        }

        $this->assertGreaterThanOrEqual(4, $found);
    }
}
