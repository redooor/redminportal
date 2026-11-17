<?php namespace Redooor\Redminportal\App\Http\Traits;

/*
 * Add revision capability to controller
 */

trait RevisionableController
{
    /* -- Requires --
    protected $model;
    ----------------- */

    public function getRevisions($sid)
    {
        $model = $this->model->find($sid);
        $ajax = request()->ajax();

        if ($model == null) {
            $message = trans('redminportal::messages.error_find_no_such_record');

            if ($ajax) {
                return response($message, 404)
                    ->header('Cache-Control', 'no-store');
            }

            return response()->view('redminportal::pages.404', [], 404)
                ->header('Cache-Control', 'no-store');
        }

        $data = [
            'model' => $model
        ];

        if ($ajax) {
            $view = view()->exists('redminportal::revisions.table')
                ? 'redminportal::revisions.table'
                : 'redminportal::revisions.view';

            return response()->view($view, $data, 200)
                ->header('Cache-Control', 'no-store')
                ->header('X-Redmin-Fragment', '1');
        }

        return response()->view('redminportal::revisions.view', $data, 200)
            ->header('Cache-Control', 'no-store');
    }
}
