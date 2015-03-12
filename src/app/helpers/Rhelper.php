<?php

class Rhelper extends Eloquent {

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

    public static function cleanSlug($string) {
        $string = trim($string); // Removes any white space in front or behind.
        $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
        $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
        return preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.
    }
}
