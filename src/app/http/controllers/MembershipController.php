<?php namespace Redooor\Redminportal\App\Http\Controllers;

use Redooor\Redminportal\App\Models\Membership;

class MembershipController extends Controller
{
    public function getIndex()
    {
        $memberships = Membership::orderBy('rank')->orderBy('name')->paginate(20);

        return \View::make('redminportal::memberships/view')->with('memberships', $memberships);
    }

    public function getCreate()
    {
        return \View::make('redminportal::memberships/create');
    }

    public function getEdit($sid)
    {
        // Find the membership using the user id
        $membership = Membership::find($sid);

        if ($membership == null) {
            return \View::make('redminportal::pages/404');
        }

        return \View::make('redminportal::memberships/edit')
            ->with('membership', $membership);
    }

    public function postStore()
    {
        $sid = \Input::get('id');
        
        $rules = array(
            'name'   => 'required|unique:memberships,name' . (isset($sid) ? ',' . $sid : ''),
            'rank'   => 'required|min:0',
        );

        $validation = \Validator::make(\Input::all(), $rules);

        if ($validation->passes()) {
            $name      = \Input::get('name');
            $rank      = \Input::get('rank');

            $membership = (isset($sid) ? Membership::find($sid) : new Membership);
            
            if ($membership == null) {
                $errors = new \Illuminate\Support\MessageBag;
                $errors->add(
                    'editError',
                    "The membership cannot be found because it does not exist or may have been deleted."
                );
                return \Redirect::to('/admin/memberships')->withErrors($errors);
            }
            
            $membership->name = $name;
            $membership->rank = $rank;

            $membership->save();
        //if it validate
        } else {
            if (isset($sid)) {
                return \Redirect::to('admin/memberships/edit/' . $sid)->withErrors($validation)->withInput();
            } else {
                return \Redirect::to('admin/memberships/create')->withErrors($validation)->withInput();
            }
        }

        return \Redirect::to('admin/memberships');
    }

    public function getDelete($sid)
    {
        // Find the membership using the user id
        $membership = Membership::find($sid);

        if ($membership == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add('deleteError', "The membership cannot be found. It could have already been deleted.");
            return \Redirect::to('/admin/memberships')->withErrors($errors);
        }

        // Cannot delete if in use
        $modMediaMembership = ModuleMediaMembership::where('membership_id', $sid)->get();
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
