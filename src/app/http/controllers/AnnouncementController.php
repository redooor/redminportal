<?php namespace Redooor\Redminportal\App\Http\Controllers;

use Redooor\Redminportal\App\Models\Announcement;

class AnnouncementController extends Controller
{
    public function getIndex()
    {
        $announcements = Announcement::paginate(20);
        
        return \View::make('redminportal::announcements/view')->with('announcements', $announcements);
    }
    
    public function getCreate()
    {
        return \View::make('redminportal::announcements/create');
    }
    
    public function getEdit($sid)
    {
        // Find the announcement using the user id
        $announcement = Announcement::find($sid);
        
        if ($announcement == null) {
            return \View::make('redminportal::pages/404');
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
        
        return \View::make('redminportal::announcements/edit')
            ->with('announcement', $announcement)
            ->with('imagine', new Helper\Image());
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
                return \Redirect::to('/admin/announcements')->withErrors($errors);
            }

            $announcement->title = $title;
            $announcement->content = $content;
            $announcement->private = $private;
            
            $announcement->save();
            
            if (\Input::hasFile('image')) {
                // Delete all existing images for edit
                //if(isset($sid)) $announcement->deleteAllImages();
                
                //Upload the file
                $helper_image = new Helper\Image();
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
                return \Redirect::to('admin/announcements/edit/' . $sid)->withErrors($validation)->withInput();
            } else {
                return \Redirect::to('admin/announcements/create')->withErrors($validation)->withInput();
            }
        }
        
        return \Redirect::to('admin/announcements');
    }
    
    public function getDelete($sid)
    {
        // Find the announcement using the id
        $announcement = Announcement::find($sid);
        
        if ($announcement == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add('deleteError', "The data cannot be deleted at this time.");
            return \Redirect::to('/admin/announcements')->withErrors($errors);
        }
        
        // Delete all images
        $announcement->deleteAllImages();
        
        // Delete the announcement
        $announcement->delete();

        return \Redirect::to('admin/announcements');
    }
    
    public function getImgremove($sid)
    {
        $image = Image::find($sid);
        
        if ($image == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add('deleteError', "The image cannot be deleted at this time.");
            return \Redirect::to('/admin/announcements')->withErrors($errors);
        }
        
        $announcement_id = $image->imageable_id;
        
        $image->remove();
        
        return \Redirect::to('admin/announcements/edit/' . $announcement_id);
    }
}
