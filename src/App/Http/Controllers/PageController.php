<?php namespace Redooor\Redminportal\App\Http\Controllers;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;
use Redooor\Redminportal\App\Http\Concerns\PreparesEditData;
use Redooor\Redminportal\App\Http\Concerns\RedirectsNotFound;
use Redooor\Redminportal\App\Http\Concerns\RemovesGalleryImages;
use Redooor\Redminportal\App\Http\Concerns\SavesTranslations;
use Redooor\Redminportal\App\Http\Concerns\SyncsTags;
use Redooor\Redminportal\App\Http\Traits\SorterController;
use Redooor\Redminportal\App\Http\Traits\DeleterController;
use Redooor\Redminportal\App\Models\Page;
use Redooor\Redminportal\App\Models\Category;
use Redooor\Redminportal\App\Classes\ImageService;
use Redooor\Redminportal\App\Http\Traits\ImageGalleryController;
use Redooor\Redminportal\App\Helpers\RImage;

class PageController extends Controller
{
    use SorterController, DeleterController, ImageGalleryController, PreparesEditData, RedirectsNotFound, RemovesGalleryImages, SavesTranslations, SyncsTags;
    
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
            return $this->redirectNotFoundWithMessage(
                'admin/pages',
                "The page cannot be found because it does not exist or may have been deleted."
            );
        }
        
        $translated = $this->translatedContentFor($page);

        $categories = Category::where('active', true)
            ->where('category_id', 0)
            ->orWhere('category_id', null)
            ->orderBy('name')
            ->get();
        
        $tagString = $this->tagStringFor($page);
        
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
        $sid = Request::input('id');
        
        $rules = array(
            'image'         => 'mimes:jpg,jpeg,png,gif|max:500',
            'images'        => 'array',
            'images.*'      => 'mimes:jpg,jpeg,png,gif|max:500',
            'title'         => 'required|regex:/^[a-z,0-9 ._\(\)-?]+$/i',
            'slug'          => 'required|regex:/^[a-z,0-9 ._\(\)-?]+$/i',
            'content'       => 'required',
        );

        $validation = Validator::make(Request::all(), $rules);
        
        if ($validation->fails()) {
            return redirect('admin/pages/' . (isset($sid) ? 'edit/' . $sid : 'create'))
                ->withErrors($validation)
                ->withInput();
        }
        
        $title              = Request::input('title');
        $slug               = Request::input('slug');
        $content            = Request::input('content');
        $image              = Request::file('image');
        $private            = (Request::input('private') == '' ? false : true);
        $category_id        = Request::input('category_id');
        $tags               = Request::input('tags');

        $page = (isset($sid) ? Page::find($sid) : new Page);

        if ($page == null) {
            return $this->redirectNotFoundWithMessage(
                '/admin/pages',
                "The page cannot be found because it does not exist or may have been deleted."
            );
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
        $this->saveTranslationsFromInput($page, array('title', 'slug', 'content'), function ($field, $value) {
            return $field == 'slug' ? str_replace(' ', '_', $value) : $value;
        });

        $imageFiles = ImageService::filesFrom();

        if (! empty($imageFiles)) {
            (new ImageService)->attach($page, $imageFiles, 'pages');
        }

        $this->syncTagsFromInput($page, $tags);

        return redirect('admin/pages');
    }

    protected function galleryModelClass(): string
    {
        return Page::class;
    }

    protected function galleryPath(): string
    {
        return 'admin/pages';
    }
}
