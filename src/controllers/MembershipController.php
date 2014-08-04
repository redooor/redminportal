<?php namespace Redooor\Redminportal;

class MembershipController extends BaseController {

    protected $model;

    public function __construct(Membership $membership)
    {
        $this->model = $membership;
    }

    public function getIndex()
    {
        $memberships = Membership::orderBy('rank')->orderBy('name')->paginate(20);

        return \View::make('redminportal::memberships/view')->with('memberships', $memberships);
    }

    public function getCreate()
    {
        return \View::make('redminportal::memberships/create');
    }

    public function getEdit($id)
    {
        // Find the membership using the user id
        $membership = Membership::find($id);

        if($membership == null) {
            return \View::make('redminportal::pages/404');
        }

        return \View::make('redminportal::memberships/edit')
            ->with('membership', $membership);
    }

    public function postStore()
    {
        $id = \Input::get('id');

        /*
         * Validate
         */
        $rules = array(
            'name'   => 'required|unique:memberships,name' . (isset($id) ? ',' . $id : ''),
            'rank'   => 'required|min:0',
        );

        $validation = \Validator::make(\Input::all(), $rules);

        if( $validation->passes() )
        {
            $name      = \Input::get('name');
            $rank      = \Input::get('rank');

            $membership = (isset($id) ? Membership::find($id) : new Membership);
            $membership->name = $name;
            $membership->rank = $rank;

            $membership->save();

        }//if it validate
        else {
            if(isset($id))
            {
                return \Redirect::to('admin/memberships/edit/' . $id)->withErrors($validation)->withInput();
            }
            else
            {
                return \Redirect::to('admin/memberships/create')->withErrors($validation)->withInput();
            }
        }

        return \Redirect::to('admin/memberships');
    }

    public function getDelete($id)
    {
        // Find the membership using the user id
        $membership = Membership::find($id);

        if ($membership == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add('deleteError', "The membership cannot be found. It could have already been deleted.");
            return \Redirect::to('/admin/memberships')->withErrors($errors);
        }

        // Cannot delete if in use
        $modMediaMembership = ModuleMediaMembership::where('membership_id', $id)->get();
        if (count($modMediaMembership) > 0) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add('deleteError', "The membership cannot be deleted because it is in used.");
            return \Redirect::to('/admin/memberships')->withErrors($errors);
        }

        // Delete the membership
        $membership->delete();

        return \Redirect::to('admin/memberships');
    }

}
