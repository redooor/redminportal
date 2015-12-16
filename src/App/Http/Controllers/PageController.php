<?php namespace Redooor\Redminportal\App\Http\Controllers;

use Redooor\Redminportal\App\Models\Page;
use Redooor\Redminportal\App\Models\Category;
use Redooor\Redminportal\App\Models\Image;
use Redooor\Redminportal\App\Models\Translation;
use Redooor\Redminportal\App\Helpers\RImage;

class PageController extends Controller
{
    public function getIndex()
    {
        $pages = Page::orderBy('created_at', 'desc')
            ->paginate(20);

        return view('redminportal::pages/view')->with('pages', $pages);
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
        
        return view('redminportal::pages/edit')
            ->with('page', $page)
            ->with('translated', $translated)
            ->with('imagine', new RImage)
            ->with('categories', $categories);
    }

    public function postStore()
    {
        $sid = \Input::get('id');

        /*
         * Validate
         */
        $rules = array(
            'image'         => 'mimes:jpg,jpeg,png,gif|max:500',
            'title'         => 'required|regex:/^[a-z,0-9 ._\(\)-?]+$/i',
            'slug'          => 'required|regex:/^[a-z,0-9 ._\(\)-?]+$/i',
            'content'       => 'required',
        );

        $validation = \Validator::make(\Input::all(), $rules);

        if ($validation->passes()) {
            $title              = \Input::get('title');
            $slug               = \Input::get('slug');
            $content            = \Input::get('content');
            $image              = \Input::file('image');
            $private            = (\Input::get('private') == '' ? false : true);
            $category_id        = \Input::get('category_id');
            
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
                $translated_model = $page->translations->where('lang', $lang)->first();
                if ($translated_model == null) {
                    $translated_model = new Translation;
                }

                $translated_model->lang = $lang;
                $translated_model->content = json_encode($translated_content);

                $page->translations()->save($translated_model);
            }

            if (\Input::hasFile('image')) {
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
        //if it validate
        } else {
            if (isset($sid)) {
                return redirect('admin/pages/edit/' . $sid)->withErrors($validation)->withInput();
            } else {
                return redirect('admin/pages/create')->withErrors($validation)->withInput();
            }
        }

        return redirect('admin/pages');
    }

    public function getDelete($sid)
    {
        // Find the page using the user id
        $page = Page::find($sid);

        if ($page == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add('deleteError', "The data cannot be deleted at this time.");
            return redirect('/admin/pages')->withErrors($errors);
        }
        
        // Delete the page
        $page->delete();

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
