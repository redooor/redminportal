<?php namespace Redooor\Redminportal\App\Http\Traits;

/*
 * Add revision capability to controller
 */

use Illuminate\Support\MessageBag;

trait RevisionableController
{
    /* -- Requires --
    protected $model;
    ----------------- */
    
    public function getRevisions($sid)
    {
        $model = $this->model->find($sid);
        
        if ($model == null) {
            $errors = new MessageBag;
            $errors->add('getError', trans('redminportal::messages.error_find_no_such_record'));
            return redirect()->back()->withErrors($errors);
        }
        
        $data = [
            'model' => $model
        ];
        
        return view('redminportal::revisions.view', $data);
    }
}
