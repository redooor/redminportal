<?php namespace Redooor\Redminportal;

class LoginController extends BaseController {

    protected $model;

    public function __construct(User $user)
    {
        $this->model = $user;
    }

    public function getIndex()
    {
        return \View::make('redminportal::users/login');
    }

    public function getUnauthorized()
    {
        return \View::make('redminportal::users/notauthorized');
    }

    public function getLogout()
    {
        // Logs the user out
        \Sentry::logout();
        return \Redirect::to('/');
    }

    public function postLogin()
    {
        /*
         * Validate
         */
        $rules = array(
            'email'     => 'required|email',
            'password'  => 'required',
        );

        $validation = \Validator::make(\Input::all(), $rules);

        if( $validation->passes() )
        {
            $email      = \Input::get('email');
            $password   = \Input::get('password');

            try
            {
                $user = \Sentry::getUserProvider()->findByCredentials(array(
                    'email'      => $email,
                    'password'   => $password,
                ));

                // Log the user in
                \Sentry::login($user, false);
            }
            catch (Cartalyst\Sentry\Users\LoginRequiredException $e)
            {
                return 'Login field is required.';
            }
            catch (Cartalyst\Sentry\Users\UserNotActivatedException $e)
            {
                return 'User not activated.';
            }
            catch (Cartalyst\Sentry\Users\UserNotFoundException $e)
            {
                $errors = new Illuminate\Support\MessageBag;
                $errors->add('invalid', "Oops, your email or password is incorrect.");

                return \Redirect::to('admin')->withErrors($errors)->withInput();
            }

            return \Redirect::to('admin');
        }

        return \Redirect::to('admin')->withErrors($validation)->withInput();
    }

}
