<?php namespace Redooor\Redminportal\App\Http\Controllers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Redooor\Redminportal\App\Http\Traits\SorterController;
use Redooor\Redminportal\App\Http\Traits\DeleterController;
use Redooor\Redminportal\App\Models\Page;
use Redooor\Redminportal\App\Models\Category;
use Redooor\Redminportal\App\Models\Image;
use Redooor\Redminportal\App\Models\Translation;
use Redooor\Redminportal\App\Models\Tag;
use Redooor\Redminportal\App\Helpers\RImage;

class PageController extends Controller
{
    use SorterController, DeleterController;
    
    public function __construct(Page $model)
    {
        $this->model = $model;
        $this->sortBy = 'created_at';
        $this->orderBy = 'desc';
        $this->perpage = config('redminportal::pagination.size');
        $this->pageView = 'redminportal::pages.view';
        $this->pageRoute = 'admin/pages';
        
        // For sorting
        $this->query = $this->model
            ->LeftJoin('categories', 'pages.category_id', '=', 'categories.id')
            ->select('pages.*', 'categories.name as category_name');
    }
    
    public function getIndex()
    {
        $models = Page::orderBy($this->sortBy, $this->orderBy)->paginate($this->perpage);
        
        $data = [
            'models' => $models,
            'sortBy' => $this->sortBy,
            'orderBy' => $this->orderBy
        ];
        
        return view('redminportal::pages/view', $data);
    }

    public function getCreate()
    {
        $categories = Category::where('active', true)
            ->where('category_id', 0)
            ->orWhere('category_id', null)
            ->orderBy('name')
            ->get();

        return view('redminportal::pages/create')->with('categories', $categories);
    }

    public function getEdit($sid)
    {
        // Find the page using the user id
        $page = Page::find($sid);

        if ($page == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add(
                'editError',
                "The page cannot be found because it does not exist or may have been deleted."
            );
            return redirect('admin/pages')->withErrors($errors);
        }
        
        $translated = array();
        foreach ($page->translations as $translation) {
            $translated[$translation->lang] = json_decode($translation->content);
        }

        $categories = Category::where('active', true)
            ->where('category_id', 0)
            ->orWhere('category_id', null)
            ->orderBy('name')
            ->get();
        
        $tagString = "";
        foreach ($page->tags as $tag) {
            if (! empty($tagString)) {
                $tagString .= ",";
            }

            $tagString .= $tag->name;
        }
        
        $data = [
            'page' => $page,
            'translated' => $translated,
            'imagine' => new RImage,
            'categories' => $categories,
            'tagString' => $tagString
        ];
        
        return view('redminportal::pages/edit', $data);
    }

    public function postStore()
    {
        $sid = Input::get('id');
        
        $rules = array(
            'image'         => 'mimes:jpg,jpeg,png,gif|max:500',
            'title'         => 'required|regex:/^[a-z,0-9 ._\(\)-?]+$/i',
            'slug'          => 'required|regex:/^[a-z,0-9 ._\(\)-?]+$/i',
            'content'       => 'required',
        );

        $validation = Validator::make(Input::all(), $rules);
        
        if ($validation->fails()) {
            return redirect('admin/pages/' . (isset($sid) ? 'edit/' . $sid : 'create'))
                ->withErrors($validation)
                ->withInput();
        }
        
        $title              = Input::get('title');
        $slug               = Input::get('slug');
        $content            = Input::get('content');
        $image              = Input::file('image');
        $private            = (Input::get('private') == '' ? false : true);
        $category_id        = Input::get('category_id');
        $tags               = Input::get('tags');

        $page = (isset($sid) ? Page::find($sid) : new Page);

        if ($page == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add(
                'editError',
                "The page cannot be found because it does not exist or may have been deleted."
            );
            return redirect('/admin/pages')->withErrors($errors);
        }

        $page->title = $title;
        $page->slug = str_replace(' ', '_', $slug); // Replace all space with underscore
        $page->content = $content;
        $page->private = $private;
        if ($category_id) {
            $page->category_id = $category_id;
        } else {
            $page->category_id = null;
        }

        $page->save();

        // Save translations
        $translations = Config::get('redminportal::translation');
        foreach ($translations as $translation) {
            $lang = $translation['lang'];
            if ($lang == 'en') {
                continue;
            }

            $translated_content = array(
                'title'     => Input::get($lang . '_title'),
                'slug'      => str_replace(' ', '_', Input::get($lang . '_slug')),
                'content'   => Input::get($lang . '_content')
            );

            // Check if lang exist
            $translated_model = $page->translations->where('lang', $lang)->first();
            if ($translated_model == null) {
                $translated_model = new Translation;
            }

            $translated_model->lang = $lang;
            $translated_model->content = json_encode($translated_content);

            $page->translations()->save($translated_model);
        }

        if (Input::hasFile('image')) {
            //Upload the file
            $helper_image = new RImage;
            $filename = $helper_image->upload($image, 'pages/' . $page->id, true);

            if ($filename) {
                // create photo
                $newimage = new Image;
                $newimage->path = $filename;

                // save photo to the loaded model
                $page->images()->save($newimage);
            }
        }

        if (! empty($tags)) {
            // Delete old tags
            $page->tags()->detach();

            // Save tags
            foreach (explode(',', $tags) as $tagName) {
                Tag::addTag($page, $tagName);
            }
        }

        return redirect('admin/pages');
    }

    public function getImgremove($sid)
    {
        $image = Image::find($sid);

        if ($image == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add('deleteError', "The image cannot be deleted at this time.");
            return redirect('/admin/pages')->withErrors($errors);
        }

        $page_id = $image->imageable_id;

        $image->delete();

        return redirect('admin/pages/edit/' . $page_id);
    }
}
