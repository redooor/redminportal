<?php namespace Redooor\Redminportal\App\Http\Concerns;

/*
 * Internal helper for rendering views as modal fragments.
 */
trait RendersFragments
{
    /**
     * Render a view as a fragment for Ajax requests.
     *
     * @param string $view
     * @param array $data
     * @return \Illuminate\Http\Response
     */
    protected function fragmentResponse($view, array $data = [])
    {
        if (! request()->ajax()) {
            return response()->view($view, $data, 200)
                ->header('Cache-Control', 'no-store');
        }

        $html = view($view, $data)->render();
        $response = response($html, 200)
            ->header('Content-Type', 'text/html; charset=UTF-8')
            ->header('Cache-Control', 'no-store');

        // A published view may render a full document, which is not a fragment.
        $trimmed = ltrim($html);
        $fullDocument = stripos($trimmed, '<!doctype') === 0 || stripos($trimmed, '<html') === 0;

        if (! $fullDocument) {
            $response->header('X-Redmin-Fragment', '1');
        }

        return $response;
    }
}
