<?php namespace Redooor\Redminportal\App\Http\Controllers;

use Auth;
use Hash;
use Input;
use Lang;
use Validator;
use Illuminate\Support\MessageBag;
use Redooor\Redminportal\App\Models\User;
use Redooor\Redminportal\App\Models\Group;

class MyaccountController extends Controller
{
    
    public function __construct(User $model)
    {
        $this->model = $model;
        $this->pageView = 'redminportal::users.myaccount';
        $this->pageRoute = 'myaccount';
    }
    
    public function getIndex()
    {
        if (! Auth::check()) {
            return redirect('login');
        }
        
        $user = Auth::user();
        
        $data = array(
            'user' => $user
        );
        
        return view($this->pageView, $data);
    }
    
    public function postStore()
    {
        if (! Auth::check()) {
            return redirect('login');
        }
        
        $user = Auth::user();
        $errors = new MessageBag;
        $sid = $user->id;
        
        $rules = array(
            'first_name'    => 'required',
            'last_name'     => 'required',
            'old_password'  => 'required',
            'password'      => 'confirmed|min:6'
        );
        
        $messages = array(
            'old_password.required' => trans('redminportal::messages.user_error_old_password_required')
        );

        $validation = Validator::make(Input::all(), $rules, $messages);
        
        if ($validation->fails()) {
            return redirect($this->pageRoute)->withErrors($validation)->withInput();
        }
        
        $old_password = Input::get('old_password');
        
        // Check that the existing password mataches
        if (Hash::check($old_password, $user->password)) {
            // Update password if not empty
            $password = Input::get('password');
            if ($password != '') {
                $user->password = Hash::make($password);
            }
            // Update First and Last name
            $user->first_name = Input::get('first_name');
            $user->last_name = Input::get('last_name');
            
            if (! $user->save()) {
                $errors->add(
                    'saveError',
                    Lang::get('redminportal::messages.user_error_update_unknown')
                );
                return redirect($this->pageRoute)->withErrors($errors)->withInput();
            }
            
            if ($password != '') {
                Auth::logout();
                return redirect($this->pageRoute);
            } else {
                $message = "Your changes have been saved.";
                return redirect($this->pageRoute)->with('success_message', $message);
            }
        }
        
        $errors->add(
            'saveError',
            Lang::get('redminportal::messages.user_authentication_error')
        );
        return redirect($this->pageRoute)->withErrors($errors)->withInput();
    }
}
