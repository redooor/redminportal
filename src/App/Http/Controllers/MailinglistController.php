<?php namespace Redooor\Redminportal\App\Http\Controllers;

use Redooor\Redminportal\App\Http\Traits\SorterController;
use Redooor\Redminportal\App\Http\Traits\DeleterController;
use Redooor\Redminportal\App\Models\Mailinglist;

class MailinglistController extends Controller
{
    protected $model;
    protected $perpage;
    protected $sortBy;
    protected $orderBy;
    
    use SorterController, DeleterController;
    
    public function __construct(Mailinglist $model)
    {
        $this->model = $model;
        $this->sortBy = 'created_at';
        $this->orderBy = 'desc';
        $this->perpage = config('redminportal::pagination.size');
        // For sorting
        $this->query = $this->model;
        $this->sort_success_view = 'redminportal::mailinglists.view';
        $this->sort_fail_redirect = 'admin/mailinglists';
    }
    
    public function getIndex()
    {
        $models = Mailinglist::orderBy($this->sortBy, $this->orderBy)->paginate($this->perpage);

        $data = [
            'models' => $models,
            'sortBy' => $this->sortBy,
            'orderBy' => $this->orderBy
        ];
        
        return view('redminportal::mailinglists/view', $data);
    }

    public function getCreate()
    {
        return view('redminportal::mailinglists/create');
    }

    public function getEdit($sid)
    {
        // Find the mailinglist using the user id
        $mailinglist = Mailinglist::find($sid);

        // No such id
        if ($mailinglist == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add(
                'editError',
                "The mailing detail cannot be found because it does not exist or may have been deleted."
            );
            return redirect('admin/mailinglists')->withErrors($errors);
        }

        return view('redminportal::mailinglists/edit')
            ->with('mailinglist', $mailinglist);
    }

    public function postStore()
    {
        $sid = \Input::get('id');

        /*
         * Validate
         */
        $rules = array(
            'email'          => 'required|unique:mailinglists,email' . (isset($sid) ? ',' . $sid : ''),
            'first_name'     => 'required',
            'last_name'      => 'required'
        );

        $validation = \Validator::make(\Input::all(), $rules);

        if ($validation->passes()) {
            $email       = \Input::get('email');
            $first_name  = \Input::get('first_name');
            $last_name   = \Input::get('last_name');
            $active      = (\Input::get('active') == '' ? false : true);

            $mailinglist = (isset($sid) ? Mailinglist::find($sid) : new Mailinglist);
            
            // No such id
            if ($mailinglist == null) {
                $errors = new \Illuminate\Support\MessageBag;
                $errors->add(
                    'storeError',
                    "We are having problem editing this entry. It may have already been deleted."
                );
                return redirect('admin/mailinglists')->withErrors($errors);
            }
            
            $mailinglist->email = $email;
            $mailinglist->first_name = $first_name;
            $mailinglist->last_name = $last_name;
            $mailinglist->active = $active;

            $mailinglist->save();

        } else {
            if (isset($sid)) {
                return redirect('admin/mailinglists/edit/' . $sid)->withErrors($validation)->withInput();
            } else {
                return redirect('admin/mailinglists/create')->withErrors($validation)->withInput();
            }
        }

        return redirect('admin/mailinglists');
    }
}
