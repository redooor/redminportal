<?php namespace Redooor\Redminportal\App\Http\Controllers;

use Illuminate\Support\MessageBag;
use Redooor\Redminportal\App\Models\Image;

class ImageController extends Controller
{
    /**
     * Shows a 404 page
     **/
    public function getIndex()
    {
        return view('redminportal::pages.404');
    }
    
    /**
     * Delete Image by ID
     *
     * @param integer Image ID to be removed
     **/
    public function getDelete($sid)
    {
        $image = Image::find($sid);
        
        if ($image == null) {
            $errors = new MessageBag;
            $errors->add('deleteError', trans('redminportal::messages.error_delete_image'));
            return redirect()->back()->withErrors($errors);
        }
        
        $image->delete();
        
        return redirect()->back();
    }
}
