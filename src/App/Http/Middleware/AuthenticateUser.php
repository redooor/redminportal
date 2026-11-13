<?php namespace Redooor\Redminportal\App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AuthenticateUser
{
    /**
     * Require a signed-in, active user without checking a route permission.
     *
     * This is used by the shared tag API, which any signed-in user may call
     * while editing content they already have permission to edit.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = 'redminguard')
    {
        if (Auth::guard($guard)->guest()) {
            if ($request->ajax()) {
                return view('redminportal::users.notauthorized');
            } else {
                return redirect('login');
            }
        }

        return (new EnsureSessionFingerprint)->handle($request, function ($request) use ($next, $guard) {
            $user = Auth::guard($guard)->user();

            if ($user != null) {
                if (! $user->activated) {
                    // User logged in but was deactivated after
                    // Log out this user and bring to login page
                    Auth::guard($guard)->logout();
                    return redirect('login');
                }

                return $next($request);
            }

            return redirect('login/unauthorized');
        }, $guard);
    }
}
