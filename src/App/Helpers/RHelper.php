<?php namespace Redooor\Redminportal\App\Helpers;

use Redooor\Redminportal\App\UI\Html;

class RHelper
{
    public static function formatCurrency($value, $currency)
    {
        return $currency . number_format($value, 2);
    }

    public static function keywords($tags)
    {
        $keywords = "";

        foreach ($tags as $tag) {
            if ($keywords == "") {
                $keywords .= $tag->name;
            } else {
                $keywords .= ',' . $tag->name;
            }
        }

        return $keywords;
    }

    /*
     * Sanitise user supplied HTML before rendering it unescaped.
     *
     * @param string|null $html
     * @return string Clean HTML
     */
    public static function cleanHtml(?string $html): string
    {
        if ($html === null || $html === '') {
            return '';
        }

        $config = \HTMLPurifier_Config::createDefault();
        $config->set('Cache.DefinitionImpl', null);
        $config->set('HTML.Allowed', implode(',', [
            'p',
            'br',
            'strong',
            'b',
            'em',
            'i',
            'u',
            's',
            'ul',
            'ol',
            'li',
            'a[href|title|target|rel]',
            'img[src|alt|width|height|title]',
            'h1',
            'h2',
            'h3',
            'h4',
            'h5',
            'h6',
            'blockquote',
            'pre',
            'code',
            'hr',
            'table',
            'thead',
            'tbody',
            'tr',
            'th',
            'td',
            'span',
            'div',
        ]));
        $config->set('Attr.AllowedFrameTargets', ['_blank', '_self', '_parent', '_top']);
        $config->set('URI.AllowedSchemes', [
            'http' => true,
            'https' => true,
            'mailto' => true,
        ]);
        $config->set('HTML.TargetNoopener', true);
        $config->set('HTML.TargetNoreferrer', true);

        $purifier = new \HTMLPurifier($config);

        return $purifier->purify($html);
    }

    /*
     * Prepare a free-text report cell for CSV/Excel export.
     *
     * @param mixed $value
     * @return string Export-safe cell
     */
    public static function exportCell($value): string
    {
        if ($value === null) {
            return '';
        }

        $value = str_replace(',', '.', (string) $value);

        if ($value === '') {
            return '';
        }

        $trimmed = ltrim($value);
        $first = $trimmed === '' ? '' : $trimmed[0];

        if (in_array($first, ['=', '+', '-', '@'], true)
            || $value[0] === "\t"
            || $value[0] === "\r") {
            return "'" . $value;
        }

        return $value;
    }

    public static function cleanSlug($string)
    {
        $string = trim($string); // Removes any white space in front or behind.
        $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
        $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
        return preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.
    }
    
    public static function joinPaths()
    {
        $paths = array();
        
        foreach (func_get_args() as $arg) {
            if ($arg !== '') {
                $paths[] = $arg;
            }
        }
        
        return preg_replace('#/+#', '/', join('/', $paths));
    }
    
    /*
     * Generate an HTML unordered list menu
     *
     * For backward compatibility. This function has been moved to UI\Html.
     *
     * @param array $menus A list of menus
     * @param string $class Optional class
     * @return string HTML Unordered List
     */
    public static function printMenu($menus, $class = null)
    {
        $html = new Html;
        return $html->printMenu($menus, $class);
    }
}
