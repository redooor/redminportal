<?php namespace Redooor\Redminportal\App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Authenticate
{
    /**
     * Handle an incoming request.
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

        $user = Auth::guard($guard)->user();

        // Check if user is activated
        if ($user != null) {
            $fingerprint = hash('sha256', $user->password);
            $storedFingerprint = $request->session()->get('redmin_password_fingerprint');

            $storedUserId = $request->session()->get('redmin_fingerprint_user');

            if ($storedFingerprint === null || $storedUserId != $user->id) {
                // First request for this user in this session, remember the current password
                $request->session()->put('redmin_fingerprint_user', $user->id);
                $request->session()->put('redmin_password_fingerprint', $fingerprint);
            } elseif ($storedFingerprint !== $fingerprint) {
                // The password has changed since this session started
                Auth::guard($guard)->logout();
                $request->session()->invalidate();

                if ($request->ajax()) {
                    return view('redminportal::users.notauthorized');
                } else {
                    return redirect('login');
                }
            }

            if (! $user->activated) {
                // User logged in but was deactivated after
                // Log out this user and bring to login page
                Auth::guard($guard)->logout();
                return redirect('login');
            }

            // Proceed to check user permission
            if ($user->hasAccess($request)) {
                // Save login time
                $user->last_login = date('Y-m-d H:i:s');
                $user->save();
                
                return $next($request);
            }
        }
        
        return redirect('login/unauthorized');
    }
}
