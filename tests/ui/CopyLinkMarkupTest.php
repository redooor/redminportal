<?php namespace Redooor\Redminportal\Test;

use PHPUnit\Framework\TestCase;

/**
 * redmin-post.js copies the href of a.btn-copy and announces the result from these attributes.
 */
class CopyLinkMarkupTest extends TestCase
{
    private function root()
    {
        return dirname(__DIR__, 2) . '/src/resources';
    }

    public function testEveryCopyLinkCarriesItsMessages()
    {
        $found = 0;
        $views = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->root() . '/views'));

        foreach ($views as $view) {
            if (! $view->isFile() || substr($view->getFilename(), -10) !== '.blade.php') {
                continue;
            }

            preg_match_all('/<a\b(?:[^>"\']|"[^"]*"|\'[^\']*\')*>/s', file_get_contents($view->getPathname()), $tags);

            foreach ($tags[0] as $tag) {
                if (! preg_match('/\bbtn-copy\b/', $tag)) {
                    continue;
                }

                $found++;
                $this->assertStringContainsString('data-copied="', $tag, $view->getFilename());
                $this->assertStringContainsString('data-copy-failed="', $tag, $view->getFilename());
                $this->assertStringContainsString('aria-label="', $tag, $view->getFilename());
            }
        }

        $this->assertSame(3, $found);
    }

    public function testMessagesAreTranslated()
    {
        foreach (['en', 'zh-cn', 'zh-tw'] as $locale) {
            $forms = require $this->root() . '/lang/' . $locale . '/forms.php';

            foreach (['copy_link', 'link_copied', 'link_copy_failed'] as $key) {
                $this->assertNotEmpty($forms[$key] ?? null, $locale . ' forms.' . $key);
            }
        }
    }

    public function testHandlerShipsInThePostScript()
    {
        $source = file_get_contents($this->root() . '/assets/js/redmin-post.js');
        $built = file_get_contents(dirname($this->root()) . '/public/js/redmin-post.min.js');

        $this->assertStringContainsString("a.btn-copy", $source);
        $this->assertStringContainsString('a.btn-copy', $built, 'run npm run build:js');
    }
}
