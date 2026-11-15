<?php namespace Redooor\Redminportal\App\Http\Controllers;

use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Request;
use Redooor\Redminportal\App\Http\Concerns\PreparesEditData;
use Redooor\Redminportal\App\Http\Concerns\RedirectsNotFound;
use Redooor\Redminportal\App\Http\Concerns\RemovesGalleryImages;
use Redooor\Redminportal\App\Http\Concerns\SavesTranslations;
use Redooor\Redminportal\App\Http\Concerns\SyncsTags;
use Redooor\Redminportal\App\Http\Concerns\ValidatesInput;
use Redooor\Redminportal\App\Http\Validation\MediaRules;
use Redooor\Redminportal\App\Http\Traits\SorterController;
use Redooor\Redminportal\App\Http\Traits\MediaUploaderController;
use Redooor\Redminportal\App\Models\Media;
use Redooor\Redminportal\App\Models\ModuleMediaMembership;
use Redooor\Redminportal\App\Models\Category;
use Redooor\Redminportal\App\Classes\ImageService;
use Redooor\Redminportal\App\Http\Traits\ImageGalleryController;
use Redooor\Redminportal\App\Helpers\RImage;
use Redooor\Redminportal\App\Classes\File as FileInfo;

class MediaController extends Controller
{
    use SorterController, MediaUploaderController, ImageGalleryController, PreparesEditData, RedirectsNotFound, RemovesGalleryImages, SavesTranslations, SyncsTags, ValidatesInput;
    
    public function __construct(Media $model)
    {
        $this->model = $model;
        $this->sortBy = 'created_at';
        $this->orderBy = 'desc';
        $this->perpage = config('redminportal::pagination.size');
        $this->pageView = 'redminportal::medias.view';
        $this->pageRoute = 'admin/medias';
        
        // For sorting
        $this->query = $this->model
            ->LeftJoin('categories', 'medias.category_id', '=', 'categories.id')
            ->select('medias.*', 'categories.name as category_name');
    }
    
    public function getIndex()
    {
        $models = Media::orderBy($this->sortBy, $this->orderBy)->paginate($this->perpage);
        
        $data = [
            'models' => $models,
            'sortBy' => $this->sortBy,
            'orderBy' => $this->orderBy
        ];

        return view('redminportal::medias/view', $data);
    }

    public function getCreate()
    {
        $categories = Category::where('active', true)
            ->where('category_id', 0)
            ->orWhere('category_id', null)
            ->orderBy('name')
            ->get();

        return view('redminportal::medias/create')
            ->with('categories', $categories);
    }

    public function getEdit($sid)
    {
        // Find the media using the user id
        $media = Media::find($sid);

        // No such id
        if ($media == null) {
            return $this->redirectNotFoundWithMessage(
                '/admin/medias',
                "The media cannot be found because it does not exist or may have been deleted."
            );
        }

        $categories = Category::where('active', true)
            ->where('category_id', 0)
            ->orWhere('category_id', null)
            ->orderBy('name')
            ->get();

        $tagString = $this->tagStringFor($media);

        $translated = $this->translatedContentFor($media);

        return view('redminportal::medias/edit')
            ->with('media', $media)
            ->with('translated', $translated)
            ->with('categories', $categories)
            ->with('tagString', $tagString)
            ->with('imagine', new RImage);
    }

    public function postStore()
    {
        $sid = Request::input('id');
        
        $rules = new MediaRules;
        $failure = $this->validateInputOrRedirect($rules->rules($sid), $rules->messages(), 'admin/medias/' . (isset($sid) ? 'edit/' . $sid : 'create'));
        if ($failure) {
            return $failure;
        }
        
        $name               = Request::input('name');
        $sku                = Request::input('sku');
        $short_description  = Request::input('short_description');
        $long_description   = Request::input('long_description');
        $image              = Request::file('image');
        $featured           = (Request::input('featured') == '' ? false : true);
        $active             = (Request::input('active') == '' ? false : true);
        $category_id        = Request::input('category_id');
        $tags               = Request::input('tags');

        $media = (isset($sid) ? Media::find($sid) : new Media);

        if ($media == null) {
            return $this->redirectNotFoundWithMessage(
                '/admin/medias',
                "The media cannot be found because it does not exist or may have been deleted."
            );
        }

        $media->name = $name;
        $media->sku = $sku;
        $media->short_description = $short_description;
        $media->long_description = $long_description;
        $media->featured = $featured;
        $media->active = $active;

        if (isset($sid)) {
            // Check if category has changed
            if ($media->category_id != $category_id) {
                $old_cat_folder = public_path() . '/assets/medias/' . $media->category_id . '/' . $sid;
                $new_cat_folder = public_path() . '/assets/medias/' . $category_id . '/' . $sid;
                // Create the directory
                if (!file_exists($new_cat_folder)) {
                    mkdir($new_cat_folder, 0777, true);
                }
                // Copy existing media to new category
                File::copyDirectory($old_cat_folder, $new_cat_folder);
                // Delete old media folder
                File::deleteDirectory($old_cat_folder);
            }
        } else {
            $media->path = 'broken.pdf';
        }

        $media->category_id = $category_id;

        // Create or save changes
        $media->save();

        // Save translations
        $this->saveTranslationsFromInput($media, array('name', 'short_description', 'long_description'));

        $this->syncTagsFromInput($media, $tags);

        $imageFiles = ImageService::filesFrom();

        if (! empty($imageFiles)) {
            (new ImageService)->attach($media, $imageFiles, 'medias');
        }

        return redirect('admin/medias');
    }

    public function getDelete($sid)
    {
        // Find the media using the user id
        $media = Media::find($sid);

        if ($media == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add('deleteError', "We are having problem deleting this entry. Please try again.");
            return redirect()->back()->withErrors($errors);
        }

        // Check if used by Module
        $mmms = ModuleMediaMembership::where('media_id', $sid)->get();
        if (count($mmms) > 0) {
            // Check for orphan
            $orphan = true;
            foreach ($mmms as $mmm) {
                if ($mmm->module != null) {
                    $orphan = false;
                    break;
                }
            }
            if (! $orphan) {
                $errors = new \Illuminate\Support\MessageBag;
                $errors->add('deleteError', "This media cannot be deleted because it is link to a module.");
                return redirect()->back()->withErrors($errors);
            }
        }
        
        // Delete the media
        $media->delete();

        return redirect()->back();
    }
    
    public function getDuration($sid)
    {
        $media = Media::find($sid);
        $status = array();
        $status['data'] = '';

        if ($media == null) {
            $status['status'] = 'error';
            return $status;
        }

        $media_folder = public_path() . '/assets/medias/' . $media->category_id . '/' . $sid;
        $filepath = $media_folder . "/" . $media->path;

        if (file_exists($filepath) && $media->mimetype != 'application/pdf') {
            $file = new FileInfo($filepath);
            // Get playtime of the file
            $playtime = $file->getPlaytime();
            // Save playtime
            $options = json_decode($media->options);
            if (! is_array($options)) {
                $options = array(); // Create a new array
            }
            $options['duration'] = ($playtime) ? $playtime : '';
            $status['data'] = $options['duration'];
            $media->options = json_encode($options);

            try {
                $media->save();
            } catch (Exception $exp) {
                $status['status'] = 'error';
                return $status;
            }
        }

        $status['status'] = 'success';
        return $status;
    }
    
    protected function galleryModelClass(): string
    {
        return Media::class;
    }

    protected function galleryPath(): string
    {
        return 'admin/medias';
    }
}
