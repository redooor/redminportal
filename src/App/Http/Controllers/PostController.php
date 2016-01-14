<?php namespace Redooor\Redminportal\App\Http\Controllers;

use Redooor\Redminportal\App\Http\Traits\SorterController;
use Redooor\Redminportal\App\Models\Post;
use Redooor\Redminportal\App\Models\Category;
use Redooor\Redminportal\App\Models\Image;
use Redooor\Redminportal\App\Models\Translation;
use Redooor\Redminportal\App\Models\Tag;
use Redooor\Redminportal\App\Helpers\RImage;

class PostController extends Controller
{
    protected $model;
    protected $perpage;
    protected $sortBy;
    protected $orderBy;
    
    use SorterController;
    
    public function __construct(Post $model)
    {
        $this->model = $model;
        $this->sortBy = 'created_at';
        $this->orderBy = 'desc';
        $this->perpage = config('redminportal::pagination.size');
        // For sorting
        $this->query = $this->model
            ->LeftJoin('categories', 'posts.category_id', '=', 'categories.id')
            ->select('posts.*', 'categories.name as category_name');
        $this->sort_success_view = 'redminportal::posts.view';
        $this->sort_fail_redirect = 'admin/posts';
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
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add(
                'editError',
                "The post cannot be found because it does not exist or may have been deleted."
            );
            return redirect('admin/posts')->withErrors($errors);
        }
        
        $translated = array();
        foreach ($post->translations as $translation) {
            $translated[$translation->lang] = json_decode($translation->content);
        }

        $categories = Category::where('active', true)
            ->where('category_id', 0)
            ->orWhere('category_id', null)
            ->orderBy('name')
            ->get();
        
        $tagString = "";
        foreach ($post->tags as $tag) {
            if (! empty($tagString)) {
                $tagString .= ",";
            }

            $tagString .= $tag->name;
        }
        
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
        $sid = \Input::get('id');
        
        $rules = array(
            'image'         => 'mimes:jpg,jpeg,png,gif|max:500',
            'title'         => 'required|regex:/^[a-z,0-9 ._\(\)-?]+$/i',
            'slug'          => 'required|regex:/^[a-z,0-9 ._\(\)-?]+$/i',
            'content'       => 'required',
        );

        $validation = \Validator::make(\Input::all(), $rules);
        
        if ($validation->fails()) {
            return redirect('admin/posts/' . (isset($sid) ? 'edit/' . $sid : 'create'))
                ->withErrors($validation)
                ->withInput();
        }
        
        $title              = \Input::get('title');
        $slug               = \Input::get('slug');
        $content            = \Input::get('content');
        $image              = \Input::file('image');
        $private            = (\Input::get('private') == '' ? false : true);
        $featured           = (\Input::get('featured') == '' ? false : true);
        $category_id        = \Input::get('category_id');
        $tags               = \Input::get('tags');

        $post = (isset($sid) ? Post::find($sid) : new Post);

        if ($post == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add(
                'editError',
                "The post cannot be found because it does not exist or may have been deleted."
            );
            return redirect('/admin/posts')->withErrors($errors);
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
        $translations = \Config::get('redminportal::translation');
        foreach ($translations as $translation) {
            $lang = $translation['lang'];
            if ($lang == 'en') {
                continue;
            }

            $translated_content = array(
                'title'     => \Input::get($lang . '_title'),
                'slug'      => str_replace(' ', '_', \Input::get($lang . '_slug')),
                'content'   => \Input::get($lang . '_content')
            );

            // Check if lang exist
            $translated_model = $post->translations->where('lang', $lang)->first();
            if ($translated_model == null) {
                $translated_model = new Translation;
            }

            $translated_model->lang = $lang;
            $translated_model->content = json_encode($translated_content);

            $post->translations()->save($translated_model);
        }

        if (\Input::hasFile('image')) {
            //Upload the file
            $helper_image = new RImage;
            $filename = $helper_image->upload($image, 'posts/' . $post->id, true);

            if ($filename) {
                // create photo
                $newimage = new Image;
                $newimage->path = $filename;

                // save photo to the loaded model
                $post->images()->save($newimage);
            }
        }

        if (! empty($tags)) {
            // Delete old tags
            $post->tags()->detach();

            // Save tags
            foreach (explode(',', $tags) as $tagName) {
                Tag::addTag($post, $tagName);
            }
        }

        return redirect('admin/posts');
    }

    public function getDelete($sid)
    {
        // Find the post using the user id
        $post = Post::find($sid);

        if ($post == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add('deleteError', "The data cannot be deleted at this time.");
            return redirect('/admin/posts')->withErrors($errors);
        }
        
        // Delete the post
        $post->delete();

        return redirect('admin/posts');
    }

    public function getImgremove($sid)
    {
        $image = Image::find($sid);

        if ($image == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add('deleteError', "The image cannot be deleted at this time.");
            return redirect('/admin/posts')->withErrors($errors);
        }

        $post_id = $image->imageable_id;

        $image->delete();

        return redirect('admin/posts/edit/' . $post_id);
    }
}
