<?php namespace Redooor\Redminportal\Test;

class LayoutsExistMarkupTest extends RedminTestCase
{
    /**
     * Test (Pass): every layout a bundled view extends exists, and the removed bare layout is gone.
     */
    public function testBundledViewsExtendExistingLayouts()
    {
        $views = dirname(__DIR__, 2) . '/src/resources/views';
        $this->assertFileDoesNotExist($views . '/layouts/bare.blade.php');

        $found = 0;
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($views, \FilesystemIterator::SKIP_DOTS));
        foreach ($files as $file) {
            if (substr($file->getFilename(), -10) !== '.blade.php') {
                continue;
            }
            $source = file_get_contents($file->getPathname());
            if (! preg_match_all("/@extends\\(.*?'redminportal::layouts\\.([a-z]+)'/", $source, $matches)) {
                continue;
            }
            foreach ($matches[1] as $layout) {
                $found++;
                $this->assertFileExists(
                    $views . '/layouts/' . $layout . '.blade.php',
                    $file->getPathname() . ' extends a missing layout: ' . $layout
                );
            }
        }
        $this->assertGreaterThan(0, $found);
    }
}
