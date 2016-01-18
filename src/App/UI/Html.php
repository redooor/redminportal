<?php namespace Redooor\Redminportal\App\UI;

class Html
{
    /*
     * Generate an HTML link to a sorting route, e.g. sort/sort_by/order_by
     *
     * @param string Sorting route
     * @param string Field name
     * @param string Sort by name
     * @param string Order by (asc or desc)
     * @param string Display name (optional)
     * @return View
     */
    public function sorter($sort_url, $field_name, $sortBy, $orderBy, $display_name = null)
    {
        if ($sortBy == $field_name && $orderBy == 'asc') {
            $sorted = 'desc';
        } else {
            $sorted = 'asc';
        }
        
        $url = url($sort_url) . '/sort/' . $field_name . '/' . $sorted;
        if (!$display_name) {
             $display_name = trans('redminportal::forms.' . $field_name);
        }
        
        $data = [
            'url' => $url,
            'field_name' => $field_name,
            'display_name' => $display_name,
            'sortBy' => $sortBy,
            'orderBy' => $orderBy
        ];
        
        return view('redminportal::partials.link-sorter', $data);
    }
}
