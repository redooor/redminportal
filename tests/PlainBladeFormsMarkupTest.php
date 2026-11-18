<?php namespace Redooor\Redminportal\Test;

use PHPUnit\Framework\TestCase;

/**
 * The bundled views are plain Blade, so a host can override them without the Collective helpers.
 */
class PlainBladeFormsMarkupTest extends TestCase
{
    private function views()
    {
        $files = [];
        $views = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(dirname(__DIR__) . '/src/resources/views'));

        foreach ($views as $view) {
            if ($view->isFile() && substr($view->getFilename(), -10) === '.blade.php') {
                $files[] = $view->getPathname();
            }
        }

        sort($files);

        return $files;
    }

    public function testNoBundledViewUsesFormOrHtmlHelpers()
    {
        $offenders = [];
        $views = $this->views();

        foreach ($views as $view) {
            if (preg_match('/\b(?:Form|HTML|Html)::/', file_get_contents($view))) {
                $offenders[] = str_replace(dirname(__DIR__) . '/src/resources/views/', '', $view);
            }
        }

        $this->assertGreaterThan(50, count($views));
        $this->assertEmpty($offenders, "These views still use Form:: or HTML::\n" . implode("\n", $offenders));
    }

    public function testEveryFormViewUsesTheTokenAndOldInput()
    {
        foreach (['mailinglists/create', 'mailinglists/edit', 'users/login', 'products/edit'] as $name) {
            $source = file_get_contents(dirname(__DIR__) . '/src/resources/views/' . $name . '.blade.php');

            $this->assertStringContainsString('@csrf', $source, $name);
            $this->assertStringContainsString('old(', $source, $name);
        }
    }
}
