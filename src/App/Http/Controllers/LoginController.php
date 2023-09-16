<?php namespace Redooor\Redminportal\App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    public function getIndex()
    {
        return view('redminportal::users/login');
    }

    public function getUnauthorized()
    {
        return view('redminportal::users/notauthorized');
    }

    public function getLogout()
    {
        // Logs the user out
        Auth::logout();
        return redirect('/');
    }

    public function postLogin()
    {
        if (Auth::check()) {
            return redirect('/');
        }
        
        $rules = array(
            'email'     => 'required|email',
            'password'  => 'required',
        );

        $validation = Validator::make(Input::all(), $rules);

        if ($validation->fails()) {
            return redirect('login')->withErrors($validation)->withInput();
        }
        
        $email      = Input::get('email');
        $password   = Input::get('password');

        if (Auth::attempt(['email' => $email, 'password' => $password, 'activated' => 1])) {
            return redirect()->intended('admin/dashboard');
        }

        $errors = new \Illuminate\Support\MessageBag;
        $errors->add('invalid', "Oops, your email or password is incorrect.");

        return redirect('login')->withErrors($errors)->withInput();
    }
}
