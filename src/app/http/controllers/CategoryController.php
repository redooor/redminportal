<?php namespace Redooor\Redminportal\App\Http\Controllers;

use Redooor\Redminportal\App\Models\Category;
use Redooor\Redminportal\App\Models\Media;
use Redooor\Redminportal\App\Models\Image;
use Redooor\Redminportal\App\Models\Translation;
use Redooor\Redminportal\App\Helpers\RImage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class CategoryController extends Controller
{
    public function getIndex()
    {
        $categories = Category::where('category_id', 0)
            ->orWhere('category_id', null)
            ->orderBy('order', 'desc')
            ->orderBy('name')
            ->get();

        return \View::make('redminportal::categories/view')->with('categories', $categories);
    }

    public function getCreate()
    {
        $categories = Category::where('active', true)
            ->where('category_id', 0)
            ->orWhere('category_id', null)
            ->orderBy('name')
            ->get();

        return \View::make('redminportal::categories/create')->with('categories', $categories);
    }

    public function getDetail($sid)
    {
        $category = Category::find($sid);

        if ($category == null) {
            return view('redminportal::pages.404');
        }

        return \View::make('redminportal::categories/detail')
            ->with('category', $category)
            ->with('imagine', new RImage);
    }

    public function getEdit($sid)
    {
        // Find the category using the user id
        $category = Category::find($sid);

        if ($category == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add(
                'editError',
                "The category cannot be found because it does not exist or may have been deleted."
            );
            return redirect('/admin/categories')->withErrors($errors);
        }
        
        $translated = array();
        foreach ($category->translations as $translation) {
            $translated[$translation->lang] = json_decode($translation->content);
        }

        $categories = Category::where('active', true)
            ->where('category_id', 0)
            ->orWhere('category_id', null)
            ->orderBy('name')
            ->get();

        return \View::make('redminportal::categories/edit')
            ->with('category', $category)
            ->with('translated', $translated)
            ->with('imagine', new RImage)
            ->with('categories', $categories);
    }

    public function postStore()
    {
        $sid = \Input::get('id');
        
        $return_path = (isset($sid) ? 'admin/categories/edit/' . $sid : 'admin/categories/create');

        /*
         * Validate
         */
        $rules = array(
            'image'                 => 'mimes:jpg,jpeg,png,gif|max:500',
            'name'                  => 'required',
            'short_description'     => 'required',
            'order'                 => 'required|min:0',
        );

        $validation = \Validator::make(\Input::all(), $rules);
        
        if ($validation->fails()) {
            return redirect($return_path)->withErrors($validation)->withInput();
        }
        
        $name               = \Input::get('name');
        $short_description  = \Input::get('short_description');
        $long_description   = \Input::get('long_description');
        $image              = \Input::file('image');
        $active             = (\Input::get('active') == '' ? false : true);
        $order              = \Input::get('order');
        $parent_id          = \Input::get('parent_id');

        $category = (isset($sid) ? Category::find($sid) : new Category);

        if ($category == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add(
                'editError',
                "The category cannot be found because it does not exist or may have been deleted."
            );
            return redirect('/admin/categories')->withErrors($errors);
        }

        // Check if there's an existing category with the same name under the same parent
        if (isset($sid)) {
            $checkSameName = Category::where('name', $name)
                ->where('category_id', (($parent_id == 0) ? null : $parent_id))
                ->whereNotIn('id', [$sid])
                ->count();
        } else {
            $checkSameName = Category::where('name', $name)
                ->where('category_id', (($parent_id == 0) ? null : $parent_id))
                ->count();
        }

        if ($checkSameName > 0) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add(
                'nameError',
                "The category cannot be added because there's an existing category with the same name."
            );

            return redirect($return_path)->withErrors($errors)->withInput();
        }

        $category->name = $name;
        $category->short_description = $short_description;
        $category->long_description = $long_description;
        $category->active = $active;
        $category->order = $order;

        // Check if parent_id is equal to this->id. If 0, save as null
        $category->category_id = ($sid == $parent_id) ? null : (($parent_id == 0) ? null : $parent_id);

        $category->save();
        
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
            $translated_model = $category->translations->where('lang', $lang)->first();
            if ($translated_model == null) {
                $translated_model = new Translation;
            }
            
            $translated_model->lang = $lang;
            $translated_model->content = json_encode($translated_content);
            
            $category->translations()->save($translated_model);
        }

        if (\Input::hasFile('image')) {
            //Upload the file
            $helper_image = new RImage;
            $filename = $helper_image->upload($image, 'categories/' . $category->id, true);

            if ($filename) {
                // create photo
                $newimage = new Image;
                $newimage->path = $filename;

                // save photo to the loaded model
                $category->images()->save($newimage);
            }
        }
        
        return redirect('admin/categories');
    }

    public function getDelete($sid)
    {
        // Find the category using the user id
        $category = Category::find($sid);

        if ($category == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add(
                'deleteError',
                "The category cannot be found because it does not exist or may have been deleted."
            );
            return redirect('/admin/categories')->withErrors($errors);
        }

        // Find if there's any child
        $children = Category::where('category_id', $sid)->count();

        if ($children > 0) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add(
                'deleteError',
                "The category '" . $category->name .
                "' cannot be deleted because it has " . $children . " children categories."
            );
            return redirect('/admin/categories')->withErrors($errors);
        }

        // Check in use by media
        $medias = Media::where('category_id', $sid)->get();
        if (count($medias) > 0) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add(
                'deleteError',
                "The category '" . $category->name . "' cannot be deleted because it is in used."
            );
            return redirect('/admin/categories')->withErrors($errors);
        }
        
        // Delete the category
        $category->delete();

        return redirect('admin/categories');
    }
    
    public function getImgremove($sid)
    {
        $image = Image::find($sid);

        if ($image == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add('deleteError', "The image cannot be deleted at this time.");
            return redirect('/admin/categories')->withErrors($errors);
        }

        $model_id = $image->imageable_id;

        $image->delete();

        return redirect('admin/categories/edit/' . $model_id);
    }
}
