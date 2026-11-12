<?php namespace Redooor\Redminportal\Test;

use PHPUnit\Framework\TestCase;

/**
 * Every Create, Edit, Delete, Activate, Deactivate, Upload and Revision link in the list views must sit inside @permitted so a user never sees a button that leads
 * to a "not authorized" page.
 */
class ListActionLinksMarkupTest extends TestCase
{
    public function testEveryListActionLinkIsWrappedInPermitted()
    {
        $dir = dirname(__DIR__) . '/src/resources/views/';
        $views = array_merge(glob($dir . '*/view.blade.php'), [$dir . 'categories/detail.blade.php', $dir . 'partials/html-view-nav-controls.blade.php']);
        $links = 0;

        foreach ($views as $view) {
            $lines = file($view);

            foreach ($lines as $number => $line) {
                if (! preg_match("#(?:URL::to|url|HTML::link)\\('admin/(?:[a-z-]+/(?:create|edit/|delete/|activate/|deactivate/|uploadform/|revisions/)|' \\. \\\$model_view \\. '/create')#", $line)) {
                    continue;
                }

                $links++;
                $previous = $lines[$number - 1] ?? '';

                // The link, or the <li> around it, is directly preceded by @permitted(...)
                $guarded = strpos($previous, '@permitted(') !== false
                    || (strpos($previous, '<li>') !== false && strpos($lines[$number - 2] ?? '', '@permitted(') !== false);

                $this->assertTrue($guarded, basename(dirname($view)) . '/' . basename($view) . ':' . ($number + 1) . ' has an action link outside @permitted');
            }
        }

        $this->assertGreaterThanOrEqual(35, $links);
    }

    public function testOrderStatusChangeIsOnlyAMenuWhenUpdateIsPermitted()
    {
        $source = file_get_contents(dirname(__DIR__) . '/src/resources/views/orders/view.blade.php');

        $this->assertStringContainsString("@permitted('admin/orders/update/status/'", $source);
    }
}
