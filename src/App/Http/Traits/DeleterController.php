<?php namespace Redooor\Redminportal\App\Http\Traits;

/*
 * Add delete capability to controller
 */

use Illuminate\Support\MessageBag;

trait DeleterController
{
    /* -- Requires --
    protected $model;
    ----------------- */
    
    public function getDelete($sid)
    {
        $model = $this->model->find($sid);
        
        if ($model == null) {
            $errors = new MessageBag;
            $errors->add('deleteError', trans('redminportal::messages.error_delete_entry'));
            return redirect()->back()->withErrors($errors);
        }
        
        $model->delete();
        
        return redirect()->back();
    }
}
