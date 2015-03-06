<?php namespace Redooor\Redminportal;

class GroupController extends BaseController
{
    public function getIndex()
    {
        //$groups = \Sentry::getGroupProvider()->createModel()->paginate(20);
        //return \View::make('redminportal::groups/view')->with('groups', $groups);
        
        $sortBy = 'name';
        $orderBy = 'asc';
        
        $groups = Group::orderBy($sortBy, $orderBy)->paginate(20);

        return \View::make('redminportal::groups/view')
            ->with('sortBy', $sortBy)
            ->with('orderBy', $orderBy)
            ->with('groups', $groups);
    }

    public function getCreate()
    {
        return \View::make('redminportal::groups/create');
    }
    
    public function getEdit($sid)
    {
        try {
            $group = \Sentry::findGroupById($sid);
        } catch (\Exception $exp) {
            return \View::make('redminportal::pages/404');
        }
        
        if (isset($group->permissions['admin'])) {
            $checkbox_admin = $this->checkPermission($group->permissions['admin']);
        } else {
            $checkbox_admin = false;
        }
        
        if (isset($group->permissions['users'])) {
            $checkbox_users = $this->checkPermission($group->permissions['users']);
        } else {
            $checkbox_users = false;
        }
        
        return \View::make('redminportal::groups/edit')
            ->with('group', $group)
            ->with('checkbox_admin', $checkbox_admin)
            ->with('checkbox_users', $checkbox_users);
    }
    
    private function checkPermission($permission)
    {
        return ($permission == '1') ? true : false;
    }
    
    public function postStore()
    {
        $sid = \Input::get('id');
        
        $rules = array(
            'name' => 'required'
        );

        $validation = \Validator::make(\Input::all(), $rules);

        if ($validation->passes()) {
            $name 	= \Input::get('name');
            $admin 	= (\Input::get('admin') == '' ? false : true);
            $user 	= (\Input::get('user') == '' ? false : true);

            if (isset($sid)) {
                // Edit existing
                try {
                    $group = \Sentry::findGroupById($sid);

                    // Update the group details
                    $group->name = $name;
                    $group->permissions = array(
                        'admin' => $admin,
                        'users' => $user
                    );
                    
                    // Update the group
                    if ($group->save()) {
                        return \Redirect::to('admin/groups');
                    } else {
                        $errors = new \Illuminate\Support\MessageBag;
                        $errors->add(
                            'editError',
                            "The group cannot be updated due to some problem. Please try again."
                        );
                        return \Redirect::to('admin/groups/edit/' . $sid)->withErrors($errors)->withInput();
                    }
                } catch (\Exception $exp) {
                    $errors = new \Illuminate\Support\MessageBag;
                    $errors->add(
                        'editError',
                        "The group cannot be found because it does not exist or may have been deleted."
                    );
                    return \Redirect::to('/admin/groups')->withErrors($errors);
                }
            } else {
                // Create new
                try {
                    // Create the group
                    \Sentry::getGroupProvider()->create(array(
                        'name'        => $name,
                        'permissions' => array(
                            'admin' => $admin,
                            'users' => $user,
                        ),
                    ));
                } catch (\Exception $exp) {
                    $errors = new \Illuminate\Support\MessageBag;
                    $errors->add(
                        'editError',
                        "The group cannot be created due to some problem. Please try again."
                    );
                    return \Redirect::to('admin/groups/create')->withErrors($errors)->withInput();
                }
            }
            
        //if it validate
        } else {
            if (isset($sid)) {
                return \Redirect::to('admin/groups/edit/' . $sid)->withErrors($validation)->withInput();
            } else {
                return \Redirect::to('admin/groups/create')->withErrors($validation)->withInput();
            }
        }

        return \Redirect::to('admin/groups');
    }
    
    public function getDelete($sid)
    {
        try {
            $group = \Sentry::findGroupById($sid);
            
            // Find any users still in this group
            $users = \Sentry::findAllUsersInGroup($group);
            
            if (count($users) > 0) {
                // Prevent deletion of this group
                $errors = new \Illuminate\Support\MessageBag;
                $errors->add(
                    'deleteError',
                    "The group cannot be deleted because it is in use. Try moving the users to another group first."
                );
                return \Redirect::to('/admin/groups')->withErrors($errors);
            } else {
                $group->delete();
            }
            
        } catch (\Exception $exp) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add('deleteError', "The group cannot be deleted at this time. It may have already been deleted.");
            return \Redirect::to('/admin/groups')->withErrors($errors);
        }

        return \Redirect::to('admin/groups');
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
            return \Redirect::to('admin/groups')->withErrors($validation);
        }
        
        if ($orderBy != 'asc' && $orderBy != 'desc') {
            $orderBy = 'asc';
        }

        $groups = Group::orderBy($sortBy, $orderBy)->paginate(20);

        return \View::make('redminportal::groups/view')
            ->with('sortBy', $sortBy)
            ->with('orderBy', $orderBy)
            ->with('groups', $groups);
    }
}
