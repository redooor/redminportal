<?php namespace Redooor\Redminportal\App\Http\Controllers;

use Redooor\Redminportal\App\Http\Traits\SorterController;
use Redooor\Redminportal\App\Http\Traits\MediaUploaderController;
use Redooor\Redminportal\App\Models\Media;
use Redooor\Redminportal\App\Models\ModuleMediaMembership;
use Redooor\Redminportal\App\Models\Category;
use Redooor\Redminportal\App\Models\Image;
use Redooor\Redminportal\App\Models\Translation;
use Redooor\Redminportal\App\Models\Tag;
use Redooor\Redminportal\App\Helpers\RImage;
use Redooor\Redminportal\App\Classes\File as FileInfo;

class MediaController extends Controller
{
    protected $model;
    protected $perpage;
    protected $sortBy;
    protected $orderBy;
    
    use SorterController, MediaUploaderController;
    
    public function __construct(Media $model)
    {
        $this->model = $model;
        $this->sortBy = 'created_at';
        $this->orderBy = 'desc';
        $this->perpage = config('redminportal::pagination.size');
        // For sorting
        $this->query = $this->model
            ->LeftJoin('categories', 'medias.category_id', '=', 'categories.id')
            ->select('medias.*', 'categories.name as category_name');
        $this->sort_success_view = 'redminportal::medias.view';
        $this->sort_fail_redirect = 'admin/medias';
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
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add(
                'editError',
                "The media cannot be found because it does not exist or may have been deleted."
            );
            return redirect('/admin/medias')->withErrors($errors);
        }

        $categories = Category::where('active', true)
            ->where('category_id', 0)
            ->orWhere('category_id', null)
            ->orderBy('name')
            ->get();

        $tagString = "";
        foreach ($media->tags as $tag) {
            if (! empty($tagString)) {
                $tagString .= ",";
            }

            $tagString .= $tag->name;
        }

        $translated = array();
        foreach ($media->translations as $translation) {
            $translated[$translation->lang] = json_decode($translation->content);
        }

        return view('redminportal::medias/edit')
            ->with('media', $media)
            ->with('translated', $translated)
            ->with('categories', $categories)
            ->with('tagString', $tagString)
            ->with('imagine', new RImage);
    }

    public function postStore()
    {
        $sid = \Input::get('id');
        
        $rules = array(
            'image'             => 'mimes:jpg,jpeg,png,gif|max:500',
            'name'              => 'required|unique:medias,name' . (isset($sid) ? ',' . $sid : ''),
            'short_description' => 'required',
            'sku'               => 'required|alpha_dash|unique:medias,sku' . (isset($sid) ? ',' . $sid : ''),
            'category_id'       => 'required|numeric|min:1',
            'tags'              => 'regex:/^[a-z,0-9 -]+$/i',
        );
        
        $messages = [
            'category_id.min' => 'The category field is required.'
        ];

        $validation = \Validator::make(\Input::all(), $rules, $messages);
        
        if ($validation->fails()) {
            return redirect('admin/medias/' . (isset($sid) ? 'edit/' . $sid : 'create'))
                ->withErrors($validation)
                ->withInput();
        }
        
        $name               = \Input::get('name');
        $sku                = \Input::get('sku');
        $short_description  = \Input::get('short_description');
        $long_description   = \Input::get('long_description');
        $image              = \Input::file('image');
        $featured           = (\Input::get('featured') == '' ? false : true);
        $active             = (\Input::get('active') == '' ? false : true);
        $category_id        = \Input::get('category_id');
        $tags               = \Input::get('tags');

        $media = (isset($sid) ? Media::find($sid) : new Media);

        if ($media == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add(
                'editError',
                "The media cannot be found because it does not exist or may have been deleted."
            );
            return redirect('/admin/medias')->withErrors($errors);
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
                \File::copyDirectory($old_cat_folder, $new_cat_folder);
                // Delete old media folder
                \File::deleteDirectory($old_cat_folder);
            }
        } else {
            $media->path = 'broken.pdf';
        }

        $media->category_id = $category_id;

        // Create or save changes
        $media->save();

        // Save translations
        $translations = \Config::get('redminportal::translation');
        foreach ($translations as $translation) {
            $lang = $translation['lang'];
            if ($lang == 'en') {
                continue;
            }

            $translated_content = array(
                'name'                  => \Input::get($lang . '_name'),
                'short_description'     => \Input::get($lang . '_short_description'),
                'long_description'      => \Input::get($lang . '_long_description')
            );

            // Check if lang exist
            $translated_model = $media->translations->where('lang', $lang)->first();
            if ($translated_model == null) {
                $translated_model = new Translation;
            }

            $translated_model->lang = $lang;
            $translated_model->content = json_encode($translated_content);

            $media->translations()->save($translated_model);
        }

        if (! empty($tags)) {
            // Delete old tags
            $media->tags()->detach();

            // Save tags
            foreach (explode(',', $tags) as $tagName) {
                Tag::addTag($media, $tagName);
            }
        }

        if (\Input::hasFile('image')) {
            //Upload the file
            $helper_image = new RImage;
            $filename = $helper_image->upload($image, 'medias/' . $media->id, true);

            if ($filename) {
                // create photo
                $newimage = new Image;
                $newimage->path = $filename;

                // save photo to the loaded model
                $media->images()->save($newimage);
            }
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
            return redirect('admin/medias')->withErrors($errors);
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
                return redirect('admin/medias')->withErrors($errors);
            }
        }
        
        // Delete the media
        $media->delete();

        return redirect('admin/medias');
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
    
    public function getImgremove($sid)
    {
        $image = Image::find($sid);

        if ($image == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add('deleteError', "The image cannot be deleted at this time.");
            return redirect('/admin/medias')->withErrors($errors);
        }

        $model_id = $image->imageable_id;

        $image->delete();

        return redirect('admin/medias/edit/' . $model_id);
    }
}
