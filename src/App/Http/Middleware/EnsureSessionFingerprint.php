<?php namespace Redooor\Redminportal\App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class EnsureSessionFingerprint
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
            return $next($request);
        }

        $user = Auth::guard($guard)->user();

        if ($user != null) {
            $fingerprint = hash('sha256', $user->password);
            $storedFingerprint = $request->session()->get('redmin_password_fingerprint');

            $storedUserId = $request->session()->get('redmin_fingerprint_user');

            if ($storedFingerprint === null
                || $storedUserId != $user->id
                || $storedFingerprint !== $fingerprint) {
                // Sessions without a usable fingerprint, or with a changed password, must sign in again
                Auth::guard($guard)->logout();
                $request->session()->invalidate();

                if ($request->ajax()) {
                    return response()->view('redminportal::users.notauthorized');
                } else {
                    return redirect('login');
                }
            }
        }

        return $next($request);
    }
}
