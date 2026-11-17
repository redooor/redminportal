<?php namespace Redooor\Redminportal\App\Http\Controllers;

use Illuminate\Support\Facades\Request;
use Redooor\Redminportal\App\Http\Concerns\PreparesEditData;
use Redooor\Redminportal\App\Http\Concerns\RedirectsNotFound;
use Redooor\Redminportal\App\Http\Concerns\RemovesGalleryImages;
use Redooor\Redminportal\App\Http\Concerns\SavesTranslations;
use Redooor\Redminportal\App\Http\Concerns\SyncsTags;
use Redooor\Redminportal\App\Http\Concerns\ValidatesInput;
use Redooor\Redminportal\App\Http\Validation\PostRules;
use Redooor\Redminportal\App\Http\Traits\SorterController;
use Redooor\Redminportal\App\Http\Traits\DeleterController;
use Redooor\Redminportal\App\Models\Post;
use Redooor\Redminportal\App\Models\Category;
use Redooor\Redminportal\App\Classes\ImageService;
use Redooor\Redminportal\App\Http\Traits\ImageGalleryController;
use Redooor\Redminportal\App\Helpers\RImage;

class PostController extends Controller
{
    use SorterController, DeleterController, ImageGalleryController, PreparesEditData, RedirectsNotFound, RemovesGalleryImages, SavesTranslations, SyncsTags, ValidatesInput;
    
    public function __construct(Post $model)
    {
        $this->model = $model;
        $this->sortBy = 'created_at';
        $this->orderBy = 'desc';
        $this->perpage = config('redminportal::pagination.size');
        $this->pageView = 'redminportal::posts.view';
        $this->pageRoute = 'admin/posts';
        
        // For sorting
        $this->query = $this->model
            ->LeftJoin('categories', 'posts.category_id', '=', 'categories.id')
            ->select('posts.*', 'categories.name as category_name');
    }
    
    public function getIndex()
    {
        $models = Post::orderBy($this->sortBy, $this->orderBy)->paginate($this->perpage);
        
        $data = [
            'models' => $models,
            'sortBy' => $this->sortBy,
            'orderBy' => $this->orderBy
        ];

        return view('redminportal::posts/view', $data);
    }

    public function getCreate()
    {
        $categories = Category::where('active', true)
            ->where('category_id', 0)
            ->orWhere('category_id', null)
            ->orderBy('name')
            ->get();

        return view('redminportal::posts/create')->with('categories', $categories);
    }

    public function getEdit($sid)
    {
        // Find the post using the user id
        $post = Post::find($sid);

        if ($post == null) {
            return $this->redirectNotFoundWithMessage(
                'admin/posts',
                "The post cannot be found because it does not exist or may have been deleted."
            );
        }
        
        $translated = $this->translatedContentFor($post);

        $categories = Category::where('active', true)
            ->where('category_id', 0)
            ->orWhere('category_id', null)
            ->orderBy('name')
            ->get();
        
        $tagString = $this->tagStringFor($post);
        
        $data = [
            'post' => $post,
            'translated' => $translated,
            'imagine' => new RImage,
            'categories' => $categories,
            'tagString' => $tagString
        ];
        
        return view('redminportal::posts/edit', $data);
    }

    public function postStore()
    {
        $sid = Request::input('id');
        
        $rules = new PostRules;
        $failure = $this->validateInputOrRedirect($rules->rules($sid), $rules->messages(), 'admin/posts/' . (isset($sid) ? 'edit/' . $sid : 'create'));
        if ($failure) {
            return $failure;
        }
        
        $title              = Request::input('title');
        $slug               = Request::input('slug');
        $content            = Request::input('content');
        $image              = Request::file('image');
        $private            = (Request::input('private') == '' ? false : true);
        $featured           = (Request::input('featured') == '' ? false : true);
        $category_id        = Request::input('category_id');
        $tags               = Request::input('tags');

        $post = (isset($sid) ? Post::find($sid) : new Post);

        if ($post == null) {
            return $this->redirectNotFoundWithMessage(
                '/admin/posts',
                "The post cannot be found because it does not exist or may have been deleted."
            );
        }

        $post->title = $title;
        $post->slug = str_replace(' ', '_', $slug); // Replace all space with underscore
        $post->content = $content;
        $post->private = $private;
        $post->featured = $featured;
        if ($category_id) {
            $post->category_id = $category_id;
        } else {
            $post->category_id = null;
        }

        $post->save();

        // Save translations
        $this->saveTranslationsFromInput($post, array('title', 'slug', 'content'), function ($field, $value) {
            if ($field == 'slug') {
                // A missing or blank translated slug is stored as null.
                if ($value === null || (is_string($value) && trim($value) === '')) {
                    return null;
                }

                return str_replace(' ', '_', $value);
            }

            return $value;
        });

        $imageFiles = ImageService::filesFrom();

        if (! empty($imageFiles)) {
            (new ImageService)->attach($post, $imageFiles, 'posts');
        }

        $this->syncTagsFromInput($post, $tags);

        return redirect('admin/posts');
    }

    public function getImgremove($sid)
    {
        return $this->removeOwnedGalleryImage($sid, Post::class, 'admin/posts');
    }

    protected function galleryModelClass(): string
    {
        return Post::class;
    }

    protected function galleryPath(): string
    {
        return 'admin/posts';
    }
}
