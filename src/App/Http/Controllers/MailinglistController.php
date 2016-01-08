<?php namespace Redooor\Redminportal\App\Http\Controllers;

use Redooor\Redminportal\App\Models\Mailinglist;

class MailinglistController extends Controller
{
    private $perpage;
    
    public function __construct()
    {
        $this->perpage = config('redminportal::pagination.size');
    }
    
    public function getIndex()
    {
        $mailinglists = Mailinglist::orderBy('email')->paginate($this->perpage);

        return view('redminportal::mailinglists/view')
            ->with('sortBy', 'email')
            ->with('orderBy', 'asc')
            ->with('mailinglists', $mailinglists);
    }

    public function getSort($sortBy = 'email', $orderBy = 'asc')
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
            return redirect('admin/mailinglists')->withErrors($validation);
        }
        
        if ($orderBy != 'asc' && $orderBy != 'desc') {
            $orderBy = 'asc';
        }
        
        $mailinglists = Mailinglist::orderBy($sortBy, $orderBy)->paginate($this->perpage);

        return view('redminportal::mailinglists/view')
            ->with('sortBy', $sortBy)
            ->with('orderBy', $orderBy)
            ->with('mailinglists', $mailinglists);
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

        if (isset($sid)) {
            // Find the mailinglist using the user id
            $mailinglist = Mailinglist::find($sid);

            // No such id
            if ($mailinglist == null) {
                $errors = new \Illuminate\Support\MessageBag;
                $errors->add(
                    'storeError',
                    "We are having problem editing this entry. It may have already been deleted."
                );
                return redirect('admin/mailinglists')->withErrors($errors);
            }
        }

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

    public function getDelete($sid)
    {
        // Find the mailinglist using the user id
        $mailinglist = Mailinglist::find($sid);

        // Delete the mailinglist
        if ($mailinglist == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add('deleteError', "We are having problem deleting this entry. It may have already been deleted.");
            return redirect('admin/mailinglists')->withErrors($errors);
        }

        $mailinglist->delete();

        return redirect('admin/mailinglists');
    }
}
