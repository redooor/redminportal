<?php namespace Redooor\Redminportal\App\Http\Controllers;

use Illuminate\Support\Facades\Request;
use Redooor\Redminportal\App\Http\Concerns\ValidatesInput;
use Redooor\Redminportal\App\Http\Validation\MailinglistRules;
use Redooor\Redminportal\App\Http\Traits\SorterController;
use Redooor\Redminportal\App\Http\Traits\DeleterController;
use Redooor\Redminportal\App\Models\Mailinglist;

class MailinglistController extends Controller
{
    use SorterController, DeleterController, ValidatesInput;
    
    public function __construct(Mailinglist $model)
    {
        $this->model = $model;
        $this->sortBy = 'created_at';
        $this->orderBy = 'desc';
        $this->perpage = config('redminportal::pagination.size');
        $this->pageView = 'redminportal::mailinglists.view';
        $this->pageRoute = 'admin/mailinglists';
        
        // For sorting
        $this->query = $this->model;
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
        $sid = Request::input('id');

        $return_path = (isset($sid) ? 'admin/mailinglists/edit/' . $sid : 'admin/mailinglists/create');

        /*
         * Validate
         */
        $rules = new MailinglistRules;
        $failure = $this->validateInputOrRedirect($rules->rules($sid), $rules->messages(), $return_path);
        if ($failure) {
            return $failure;
        }

        $email       = Request::input('email');
        $first_name  = Request::input('first_name');
        $last_name   = Request::input('last_name');
        $active      = (Request::input('active') == '' ? false : true);

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

        return redirect('admin/mailinglists');
    }
}
