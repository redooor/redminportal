<?php namespace Redooor\Redminportal\App\Http\Traits;

/*
 * Add sorting capability to controller
 */

use Validator;

trait SorterController
{
    /* -- Requires --
    protected $query;
    protected $pageView;
    protected $pageRoute;
    protected $data;
    ----------------- */
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
        
        $validation = Validator::make($inputs, $rules);

        if ($validation->fails()) {
            return redirect($this->pageRoute)->withErrors($validation);
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
        
        // Merge if there're default data
        if (! empty($this->data)) {
            $data = array_merge($this->data, $data);
        }
        
        return view($this->pageView, $data);
    }
}
