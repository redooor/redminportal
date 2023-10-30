<?php namespace Redooor\Redminportal\App\Http\Traits;

/*
 * Add search capability to controller
 */

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;

trait SearcherController
{
    /* -- Requires --
    protected $model;
    protected $query;
    protected $sortBy;
    protected $orderBy;
    protected $pageView;
    protected $pageRoute;
    ----------------- */
    
    protected $searchable_fields; // array('property_name' => 'Display Name')
    
    public function postSearch()
    {
        $search_pattern = '/^[a-zA-Z0-9 _\-@.]+$/';
        $field_pattern = '/^[a-zA-Z0-9_]+$/';
        
        $rules = [
            'search' => 'required|regex:' . $search_pattern,
            'field' => 'regex:' . $field_pattern
        ];
        
        $messages = [
            'search.required'   => trans('redminportal::messages.search_error_text_missing'),
            'field.regex'       => trans('redminportal::messages.error_remove_special_characters'),
            'search.regex'      => trans('redminportal::messages.error_remove_special_characters')
        ];

        $validation = Validator::make(Input::all(), $rules, $messages);
        
        if ($validation->fails()) {
            return redirect($this->pageRoute)->withErrors($validation)->withInput();
        }
        
        $search = trim(Input::get('search'));
        $field  = trim(Input::get('field'));
        
        if ($field and $field != 'all') {
            // Perform a field search
            return redirect($this->pageRoute . '/search/' . $field . '/' . $search);
        }
        
        // Else perform search all
        return redirect($this->pageRoute . '/search-all/' . $search);
    }
    
    public function getSearchAll($search = null)
    {
        $search_pattern = '/^[a-zA-Z0-9 _\-@.]+$/';
        
        $rules = [
            'search' => 'required|regex:' . $search_pattern
        ];
        
        $inputs = [
            'search' => $search
        ];
        
        $messages = [
            'search.required'   => trans('redminportal::messages.search_error_text_missing'),
            'search.regex'      => trans('redminportal::messages.error_remove_special_characters')
        ];

        $validation = Validator::make($inputs, $rules, $messages);

        if ($validation->fails()) {
            return redirect($this->pageRoute)->withErrors($validation)->with('search', $search);
        }
        
        foreach (array_keys($this->searchable_fields) as $field) {
            if ($field != 'all') {
                $this->query->orWhere($field, 'LIKE', '%' . $search . '%');
            }
        }
        
        $models = $this->query
            ->orderBy($this->sortBy, $this->orderBy)
            ->paginate($this->perpage);
        
        $data = array(
            'sortBy' => $this->sortBy,
            'orderBy' => $this->orderBy,
            'models' => $models,
            'search' => $search,
            'searchable_fields' => $this->searchable_fields
        );
        
        // Merge if there're default data
        if (! empty($this->data)) {
            $data = array_merge($this->data, $data);
        }

        return view($this->pageView, $data);
    }
    
    public function getSearch($field = null, $search = null)
    {
        $search_pattern = '/^[a-zA-Z0-9 _\-@.]+$/';
        $field_pattern = '/^[a-zA-Z0-9_]+$/';
        
        $rules = [
            'search' => 'required|regex:' . $search_pattern,
            'field' => 'required|regex:' . $field_pattern
        ];
        
        $inputs = [
            'search' => $search,
            'field' => $field
        ];
        
        $messages = [
            'field.required'    => trans('redminportal::messages.search_error_field_missing'),
            'search.required'   => trans('redminportal::messages.search_error_text_missing'),
            'field.regex'       => trans('redminportal::messages.error_remove_special_characters'),
            'search.regex'      => trans('redminportal::messages.error_remove_special_characters')
        ];

        $validation = Validator::make($inputs, $rules, $messages);

        if ($validation->fails()) {
            return redirect($this->pageRoute)->withErrors($validation)->with('search', $search);
        }
        
        if (array_key_exists($field, $this->searchable_fields)) {
            $this->query->orWhere($field, 'LIKE', '%' . $search . '%');
        
            $models = $this->query
                ->orderBy($this->sortBy, $this->orderBy)
                ->paginate($this->perpage);
            
            $data = array(
                'sortBy' => $this->sortBy,
                'orderBy' => $this->orderBy,
                'models' => $models,
                'search' => $search,
                'field' => $field,
                'searchable_fields' => $this->searchable_fields
            );
            
            // Merge if there're default data
            if (! empty($this->data)) {
                $data = array_merge($this->data, $data);
            }

            return view($this->pageView, $data);
        }
        
        $errors = new MessageBag;
        $errors->add(
            'stringError',
            trans('redminportal::messages.search_error_unknown')
        );
        
        return redirect($this->pageRoute)->withErrors($errors);
    }
}
