<?php namespace Redooor\Redminportal\App\Helpers;

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
    
    public static function printMenu($menus, $class = null)
    {
        echo '<ul' . ($class ? ' class="' . $class . '"' : '') . '>';
        
        foreach ($menus as $menu) {
            if  (!$menu['hide']) {
                if  (!array_key_exists('path', $menu)) {
                    echo '<li><span>' . \Lang::get('redminportal::menus.' . $menu['name']) . '</span>';
                } else if ($menu['path'] == '') {
                    echo '<li><span>' . \Lang::get('redminportal::menus.' . $menu['name']) . '</span>';
                } else {
                    if (\Request::is($menu['path']) or \Request::is($menu['path'] . '/*')) {
                        echo '<li class="active">';
                    } else {
                        echo '<li>';
                    }
                    echo '<a href="' . \URL::to($menu['path']) . '">' . \Lang::get('redminportal::menus.' . $menu['name']) . '</a>';
                }
                // If got children menu
                if  (array_key_exists('children', $menu)) {
                    RHelper::printMenu($menu['children']);
                }
                echo '</li>';
            }
        }
        echo '</ul>';
    }
}
