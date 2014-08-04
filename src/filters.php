<?php

/*
|--------------------------------------------------------------------------
| Authentication Filters
|--------------------------------------------------------------------------
|
| The following filters are used to verify that the user of the current
| session is logged into this application.
|
*/
Route::filter('auth.sentry', function()
{
    if ( ! Sentry::check())
    {
        return View::make('redminportal::users/login');
    }
    
    try
    {
        // Get the current active/logged in user
        $user = Sentry::getUser();
        
        if ( ! $user->hasAccess('admin') )
        {
            return Redirect::to('login/unauthorized');
        }
    }
    catch (Cartalyst\Sentry\Users\UserNotFoundException $e)
    {
        // User wasn't found, should only happen if the user was deleted
        // when they were already logged in or had a "remember me" cookie set
        // and they were deleted.
        return View::make('redminportal::users/login');
    }
});