<?php namespace Redooor\Redminportal\App\Http\Controllers;

use Illuminate\Support\Facades\Request;
use Redooor\Redminportal\App\Http\Traits\SorterController;
use Redooor\Redminportal\App\Http\Traits\DeleterController;
use Redooor\Redminportal\App\Models\Announcement;
use Redooor\Redminportal\App\Classes\ImageService;
use Redooor\Redminportal\App\Http\Traits\ImageGalleryController;
use Redooor\Redminportal\App\Helpers\RImage;
use Redooor\Redminportal\App\Http\Concerns\RemovesGalleryImages;
use Redooor\Redminportal\App\Http\Concerns\RedirectsNotFound;

class AnnouncementController extends Controller
{
    use SorterController, DeleterController, ImageGalleryController, RemovesGalleryImages, RedirectsNotFound;
    
    public function __construct(Announcement $model)
    {
        $this->model = $model;
        $this->sortBy = 'created_at';
        $this->orderBy = 'desc';
        $this->perpage = config('redminportal::pagination.size');
        $this->pageView = 'redminportal::announcements.view';
        $this->pageRoute = 'admin/announcements';
        
        // For sorting
        $this->query = $this->model;
    }
    
    public function getIndex()
    {
        $models = Announcement::orderBy($this->sortBy, $this->orderBy)->paginate($this->perpage);
        
        $data = [
            'models' => $models,
            'sortBy' => $this->sortBy,
            'orderBy' => $this->orderBy
        ];
        
        return view('redminportal::announcements/view', $data);
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
            return $this->redirectNotFoundWithMessage(
                '/admin/announcements',
                "The announcement cannot be found because it does not exist or may have been deleted."
            );
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
        $sid = Request::input('id');
            
        $validation = Announcement::validate(Request::all());
        
        if ($validation->passes()) {
            $title              = Request::input('title');
            $content            = Request::input('content');
            $image              = Request::file('image');
            $private            = (Request::input('private') == '' ? false : true);
            
            $announcement = (isset($sid) ? Announcement::find($sid) : new Announcement);
            
            if ($announcement == null) {
                return $this->redirectNotFoundWithMessage(
                    '/admin/announcements',
                    "The Announcement cannot be found because it does not exist or may have been deleted."
                );
            }

            $announcement->title = $title;
            $announcement->content = $content;
            $announcement->private = $private;
            
            $announcement->save();
            
            $imageFiles = ImageService::filesFrom();

            if (! empty($imageFiles)) {
                (new ImageService)->attach($announcement, $imageFiles, 'announcements');
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
    
    protected function galleryModelClass(): string
    {
        return Announcement::class;
    }

    protected function galleryPath(): string
    {
        return 'admin/announcements';
    }
}
