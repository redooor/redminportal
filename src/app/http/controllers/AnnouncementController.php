<?php namespace Redooor\Redminportal\App\Http\Controllers;

use Redooor\Redminportal\App\Models\Announcement;
use Redooor\Redminportal\App\Helpers\RImage;

class AnnouncementController extends Controller
{
    public function getIndex()
    {
        $announcements = Announcement::paginate(20);
        
        return view('redminportal::announcements/view')->with('announcements', $announcements);
    }
    
    public function getCreate()
    {
        return view('redminportal::announcements/create');
    }
    
    public function getEdit($sid)
    {
        // Find the announcement using the user id
        $announcement = Announcement::find($sid);
        
        if ($announcement == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add(
                'editError',
                "The announcement cannot be found because it does not exist or may have been deleted."
            );
            return redirect('/admin/announcements')->withErrors($errors);
        }
        
        if (empty($announcement->options)) {
            $announcement_cn = (object) array(
                'name'                  => $announcement->name,
                'short_description'     => $announcement->short_description,
                'long_description'      => $announcement->long_description
            );
        } else {
            $announcement_cn = json_decode($announcement->options);
        }
        
        return view('redminportal::announcements/edit')
            ->with('announcement', $announcement)
            ->with('imagine', new RImage);
    }
    
    public function postStore()
    {
        $sid = \Input::get('id');
            
        $validation = Announcement::validate(\Input::all());
        
        if ($validation->passes()) {
            $title              = \Input::get('title');
            $content            = \Input::get('content');
            $image              = \Input::file('image');
            $private            = (\Input::get('private') == '' ? false : true);
            
            $announcement = (isset($sid) ? Announcement::find($sid) : new Announcement);
            
            if ($announcement == null) {
                $errors = new \Illuminate\Support\MessageBag;
                $errors->add(
                    'editError',
                    "The Announcement cannot be found because it does not exist or may have been deleted."
                );
                return redirect('/admin/announcements')->withErrors($errors);
            }

            $announcement->title = $title;
            $announcement->content = $content;
            $announcement->private = $private;
            
            $announcement->save();
            
            if (\Input::hasFile('image')) {
                //Upload the file
                $helper_image = new RImage;
                $filename = $helper_image->upload($image, 'announcements/' . $announcement->id, true);
                
                if ($filename) {
                    // create photo
                    $newimage = new Image;
                    $newimage->path = $filename;
                    
                    // save photo to the loaded model
                    $announcement->images()->save($newimage);
                }
            }
        //if it validate
        } else {
            if (isset($sid)) {
                return redirect('admin/announcements/edit/' . $sid)->withErrors($validation)->withInput();
            } else {
                return redirect('admin/announcements/create')->withErrors($validation)->withInput();
            }
        }
        
        return redirect('admin/announcements');
    }
    
    public function getDelete($sid)
    {
        // Find the announcement using the id
        $announcement = Announcement::find($sid);
        
        if ($announcement == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add('deleteError', "The data cannot be deleted at this time.");
            return redirect('/admin/announcements')->withErrors($errors);
        }
        
        // Delete the announcement
        $announcement->delete();

        return redirect('admin/announcements');
    }
    
    public function getImgremove($sid)
    {
        $image = Image::find($sid);
        
        if ($image == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add('deleteError', "The image cannot be deleted at this time.");
            return redirect('/admin/announcements')->withErrors($errors);
        }
        
        $announcement_id = $image->imageable_id;
        
        $image->remove();
        
        return redirect('admin/announcements/edit/' . $announcement_id);
    }
}
