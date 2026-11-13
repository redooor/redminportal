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
        }

        return $next($request);
    }
}
