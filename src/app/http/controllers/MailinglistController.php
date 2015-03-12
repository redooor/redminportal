<?php namespace Redooor\Redminportal;

class MailinglistController extends BaseController {

    protected $model;

    public function __construct(Mailinglist $mailinglist)
    {
        $this->model = $mailinglist;
    }

    public function getIndex()
    {
        $mailinglists = Mailinglist::orderBy('email')->paginate(20);

        return \View::make('redminportal::mailinglists/view')
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

        if( ! $validation->passes() )
        {
            return \Redirect::to('admin/mailinglists')->withErrors($validation);
        }
        
        if ($orderBy != 'asc' && $orderBy != 'desc') {
            $orderBy = 'asc';
        }

        $mailinglists = Mailinglist::orderBy($sortBy, $orderBy)->paginate(20);

        return \View::make('redminportal::mailinglists/view')
            ->with('sortBy', $sortBy)
            ->with('orderBy', $orderBy)
            ->with('mailinglists', $mailinglists);
    }

    public function getCreate()
    {
        return \View::make('redminportal::mailinglists/create');
    }

    public function getEdit($id)
    {
        // Find the mailinglist using the user id
        $mailinglist = Mailinglist::find($id);

        // No such id
        if ($mailinglist == null) {
            return \View::make('redminportal::pages/404');
        }

        return \View::make('redminportal::mailinglists/edit')
            ->with('mailinglist', $mailinglist);
    }

    public function postStore()
    {
        $id = \Input::get('id');

        if (isset($id)) {
            // Find the mailinglist using the user id
            $mailinglist = Mailinglist::find($id);

            // No such id
            if ($mailinglist == null) {
                $errors = new \Illuminate\Support\MessageBag;
                $errors->add('storeError', "We are having problem editing this entry. It may have already been deleted.");
                return \Redirect::to('admin/mailinglists')->withErrors($errors);
            }
        }

        /*
         * Validate
         */
        $rules = array(
            'email'          => 'required|unique:mailinglists,email' . (isset($id) ? ',' . $id : ''),
            'first_name'     => 'required',
            'last_name'      => 'required'
        );

        $validation = \Validator::make(\Input::all(), $rules);

        if( $validation->passes() )
        {
            $email       = \Input::get('email');
            $first_name  = \Input::get('first_name');
            $last_name   = \Input::get('last_name');
            $active      = (\Input::get('active') == '' ? FALSE : TRUE);

            $mailinglist = (isset($id) ? Mailinglist::find($id) : new Mailinglist);
            $mailinglist->email = $email;
            $mailinglist->first_name = $first_name;
            $mailinglist->last_name = $last_name;
            $mailinglist->active = $active;

            $mailinglist->save();

        }//if it validate
        else {
            if(isset($id))
            {
                return \Redirect::to('admin/mailinglists/edit/' . $id)->withErrors($validation)->withInput();
            }
            else
            {
                return \Redirect::to('admin/mailinglists/create')->withErrors($validation)->withInput();
            }
        }

        return \Redirect::to('admin/mailinglists');
    }

    public function getDelete($id)
    {
        // Find the mailinglist using the user id
        $mailinglist = Mailinglist::find($id);

        // Delete the mailinglist
        if ($mailinglist == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add('deleteError', "We are having problem deleting this entry. It may have already been deleted.");
            return \Redirect::to('admin/mailinglists')->withErrors($errors);
        }

        $mailinglist->delete();

        return \Redirect::to('admin/mailinglists');
    }

}
