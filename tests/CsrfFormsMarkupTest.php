<?php namespace Redooor\Redminportal\Test;

use PHPUnit\Framework\TestCase;

/**
 * Admin pages submit changes with POST, so every plain form needs a token and every link to a
 * POST-only route must either open the confirmation modal or submit through redmin-post.
 */
class CsrfFormsMarkupTest extends TestCase
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

    private function viewName($file)
    {
        return str_replace(dirname(__DIR__) . '/src/resources/views/', '', $file);
    }

    private function postRoutes()
    {
        return ['/delete/', 'imgremove', 'variant-imgremove', 'delete-variant-json', 'activate/', 'deactivate/', 'orders/update', 'logout', 'medias/duration'];
    }

    public function testEveryPlainPostFormHasACsrfToken()
    {
        $found = 0;

        foreach ($this->views() as $view) {
            $source = file_get_contents($view);

            preg_match_all('/<form\b[^>]*\bmethod\s*=\s*["\']?POST["\']?[^>]*>/i', $source, $forms, PREG_OFFSET_CAPTURE);

            foreach ($forms[0] as $form) {
                $found++;
                $end = strpos($source, '</form>', $form[1]);
                $markup = substr($source, $form[1], $end === false ? 1000 : $end - $form[1]);

                $this->assertMatchesRegularExpression(
                    '/@csrf\b|csrf_field\s*\(|name="_token"/',
                    $markup,
                    $this->viewName($view) . ' has a POST form without @csrf, csrf_field() or a _token input'
                );
            }
        }

        $this->assertGreaterThanOrEqual(5, $found);
    }

    public function testEveryChangedRouteLinkPosts()
    {
        $offenders = [];
        $links = 0;

        foreach ($this->views() as $view) {
            $source = file_get_contents($view);
            preg_match_all('/<a\b(?:[^>"\']|"[^"]*"|\'[^\']*\')*>/is', $source, $tags, PREG_OFFSET_CAPTURE);

            foreach ($tags[0] as $tag) {
                if (! preg_match('/\bhref\s*=\s*(["\'])(.*?)\1/is', $tag[0], $href)) {
                    continue;
                }

                $matches = false;

                foreach ($this->postRoutes() as $route) {
                    if (stripos($href[2], $route) !== false) {
                        $matches = true;
                        break;
                    }
                }

                if (! $matches) {
                    continue;
                }

                $links++;

                $allowed = strpos($tag[0], 'btn-confirm') !== false
                    || preg_match('/\bdata-method\s*=\s*["\']post["\']/i', $tag[0]);

                if (! $allowed) {
                    $line = substr_count(substr($source, 0, $tag[1]), "\n") + 1;
                    $offenders[] = $this->viewName($view) . ':' . $line . ' ' . preg_replace('/\s+/', ' ', $tag[0]);
                }
            }
        }

        $this->assertGreaterThanOrEqual(30, $links, 'No links to the POST-only routes were found');
        $this->assertEmpty(
            $offenders,
            "These links point at POST-only routes without btn-confirm or data-method=\"post\":\n" . implode("\n", $offenders)
        );
    }

    public function testLayoutsProvideTheTokenAndPostHandler()
    {
        foreach (['master', 'bare', 'plain'] as $layout) {
            $source = file_get_contents(dirname(__DIR__) . '/src/resources/views/layouts/' . $layout . '.blade.php');

            $this->assertStringContainsString('<meta name="csrf-token" content="{{ csrf_token() }}">', $source, $layout . ' layout has no csrf-token meta tag');
            $this->assertStringContainsString("vendor/redooor/redminportal/js/redmin-post.min.js", $source, $layout . ' layout does not load redmin-post');
            $this->assertStringContainsString('defer', $source, $layout . ' layout does not defer redmin-post');
        }
    }

    public function testMediaDurationIsPostedWithTheToken()
    {
        $source = file_get_contents(dirname(__DIR__) . '/src/resources/views/medias/view.blade.php');

        $this->assertStringContainsString('$.post(url, { _token: window.redminCsrfToken() }', $source);
        $this->assertStringNotContainsString('$.get(url', $source);
    }
}
