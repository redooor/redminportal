<?php namespace Redooor\Redminportal\App\Http\Middleware;

use Closure;

/*
 * Stop other sites from embedding the package pages in a frame (clickjacking).
 * Pages may still be framed by the same origin, which the iframe modals rely on.
 * A host that already sends its own X-Frame-Options keeps its choice.
 */
class PreventFraming
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        if (! isset($response->headers) || $response->headers->has('X-Frame-Options')) {
            return $response;
        }

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Added next to any host policy, since browsers apply every Content-Security-Policy header
        $response->headers->set('Content-Security-Policy', "frame-ancestors 'self'", false);

        return $response;
    }
}
