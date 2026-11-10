<?php namespace Redooor\Redminportal\Test;

use PHPUnit\Framework\TestCase;

/**
 * Wide list tables must scroll inside their own wrapper on small screens, and the row menus inside them must use fixed
 * positioning so the wrapper does not clip them.
 */
class ListViewMarkupTest extends TestCase
{
    public function testEveryListTableIsResponsiveWithUnclippedRowMenus()
    {
        $views = glob(dirname(__DIR__) . '/src/resources/views/*/view.blade.php');

        $this->assertNotEmpty($views);

        foreach ($views as $view) {
            $source = file_get_contents($view);
            $name = basename(dirname($view));

            if (strpos($source, '<table') === false) {
                continue;
            }

            $this->assertStringContainsString('<div class="table-responsive">', $source, "$name list table is not wrapped in .table-responsive");

            preg_match_all('/<button[^>]*dropdown-toggle[^>]*>/', $source, $toggles);

            foreach ($toggles[0] as $toggle) {
                $this->assertStringContainsString('data-bs-popper-config=\'{"strategy":"fixed"}\'', $toggle, "$name has a row menu that the wrapper would clip");
            }
        }
    }
}
