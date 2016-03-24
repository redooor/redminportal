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
    
    /*
     * Generate HTML with all the uploaded images of the given model
     *
     * @param Object Imageable or compatible Eloquent model
     * @return View
     */
    public function uploadedImages($model)
    {
        $data = [
            'model' => $model
        ];
        
        return view('redminportal::partials.uploaded-images', $data);
    }
    
    /*
     * Generate an HTML modal window
     *
     * @param string Modal Unique Id
     * @param string Content title
     * @param string Content body
     * @param string Optional footer
     * @param string Optional modal size
     * @param string Optional modal progress ID
     * @return View
     */
    public function modalWindow(
        $modal_id,
        $modal_title,
        $modal_body,
        $modal_footer = null,
        $modal_size = null,
        $modal_progress = null
    ) {
        $data = [
            'modal_id'          => $modal_id,
            'modal_size'        => $modal_size,
            'modal_progress'    => $modal_progress,
            'modal_title'       => $modal_title,
            'modal_body'        => $modal_body,
            'modal_footer'      => $modal_footer
        ];
        
        return view('redminportal::partials.modal-window', $data);
    }
    
    /*
     * Generate an HTML unordered list menu
     *
     * @param array $menus A list of menus
     * @param string $class Optional class
     * @return string HTML Unordered List
     */
    public function printMenu($menus, $class = null)
    {
        $data = [
            'menus' => $menus,
            'class' => $class
        ];
        
        return view('redminportal::partials.html-print-menu', $data);
    }
}
