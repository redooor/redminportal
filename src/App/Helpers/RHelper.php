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
