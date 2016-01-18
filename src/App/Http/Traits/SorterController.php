<?php namespace Redooor\Redminportal\App\Http\Traits;

/*
 * Add sorting capability to controller
 */

trait SorterController
{
    /* -- Requires --
    protected $query;
    ----------------- */
    protected $sort_success_view;
    protected $sort_fail_redirect;
    
    public function getSort($sortBy = 'create_at', $orderBy = 'desc')
    {
        $inputs = array(
            'sortBy' => $sortBy,
            'orderBy' => $orderBy
        );
        
        $rules = array(
            'sortBy'  => 'required|regex:/^[a-zA-Z0-9 _-]*$/',
            'orderBy' => 'required|regex:/^[a-zA-Z0-9 _-]*$/'
        );
        
        $validation = \Validator::make($inputs, $rules);

        if ($validation->fails()) {
            return redirect($this->sort_fail_redirect)->withErrors($validation);
        }
        
        if ($orderBy != 'asc' && $orderBy != 'desc') {
            $orderBy = 'asc';
        }
        
        $models = $this->query->orderBy($sortBy, $orderBy)->paginate($this->perpage);
        
        $data = [
            'models' => $models,
            'sortBy' => $sortBy,
            'orderBy' => $orderBy
        ];
        
        return view($this->sort_success_view, $data);
    }
}
