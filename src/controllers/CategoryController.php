<?php namespace Redooor\Redminportal;

class CategoryController extends BaseController
{
    protected $model;

    public function __construct(Category $category)
    {
        $this->model = $category;
    }

    public function getIndex()
    {
        $categories = Category::where('category_id', 0)->orWhere('category_id', null)->orderBy('order', 'desc')->orderBy('name')->get();

        return \View::make('redminportal::categories/view')->with('categories', $categories);
    }

    public function getCreate()
    {
        $categories = Category::where('active', true)->where('category_id', 0)->orWhere('category_id', null)->orderBy('name')->get();

        return \View::make('redminportal::categories/create')->with('categories', $categories);
    }

    public function getDetail($sid)
    {
        $category = Category::find($sid);

        if ($category == null) {
            return \View::make('redminportal::pages/404');
        }

        return \View::make('redminportal::categories/detail')
            ->with('category', $category)
            ->with('imageUrl', 'assets/img/categories/');
    }

    public function getEdit($sid)
    {
        // Find the category using the user id
        $category = Category::find($sid);

        if ($category == null) {
            return \View::make('redminportal::pages/404');
        }

        if (empty($category->options)) {
            $category_cn = (object) array(
                'name'                  => $category->name,
                'short_description'     => $category->short_description,
                'long_description'      => $category->long_description
            );
        } else {
            $category_cn = json_decode($category->options);
        }

        $categories = Category::where('active', true)->where('category_id', 0)->orWhere('category_id', null)->orderBy('name')->get();

        return \View::make('redminportal::categories/edit')
            ->with('category', $category)
            ->with('category_cn', $category_cn)
            ->with('imageUrl', 'assets/img/categories/')
            ->with('categories', $categories);
    }

    public function postStore()
    {
        $sid = \Input::get('id');

        /*
         * Validate
         */
        $rules = array(
            'image'                 => 'mimes:jpg,jpeg,png,gif|max:500',
            'name'                  => 'required|unique:categories,name' . (isset($sid) ? ',' . $sid : ''),
            'short_description'     => 'required',
            'order'                 => 'required|min:0',
        );

        $validation = \Validator::make(\Input::all(), $rules);

        if ($validation->passes()) {
            $name               = \Input::get('name');
            $short_description  = \Input::get('short_description');
            $long_description   = \Input::get('long_description');
            $image              = \Input::file('image');
            $active             = (\Input::get('active') == '' ? false : true);
            $order              = \Input::get('order');
            $parent_id          = \Input::get('parent_id');

            $cn_name               = \Input::get('cn_name');
            $cn_short_description  = \Input::get('cn_short_description');
            $cn_long_description   = \Input::get('cn_long_description');

            $options = array(
                'name'                  => $cn_name,
                'short_description'     => $cn_short_description,
                'long_description'      => $cn_long_description
            );
            
            $category = (isset($sid) ? Category::find($sid) : new Category);
            
            if ($category == null) {
                $errors = new \Illuminate\Support\MessageBag;
                $errors->add(
                    'editError',
                    "The category cannot be found because it does not exist or may have been deleted."
                );
                return \Redirect::to('/admin/categories')->withErrors($errors);
            }

            $category->name = $name;
            $category->short_description = $short_description;
            $category->long_description = $long_description;
            $category->active = $active;
            $category->order = $order;
            $category->options = json_encode($options);

            // Check if parent_id is equal to this->id. If 0, save as null
            $category->category_id = ($sid == $parent_id) ? null : (($parent_id == 0) ? null : $parent_id);

            $category->save();

            if (\Input::hasFile('image')) {
                // Delete all existing images for edit
                if (isset($sid)) {
                    $category->deleteAllImages();
                }

                //set the name of the file
                $originalFilename = $image->getClientOriginalName();
                $filename = str_replace(' ', '', $name) . \Str::random(20) .'.'. \File::extension($originalFilename);

                //Upload the file
                $isSuccess = $image->move('assets/img/categories', $filename);

                if ($isSuccess) {
                    // create photo
                    $newimage = new Image;
                    $newimage->path = $filename;

                    // save photo to the loaded model
                    $category->images()->save($newimage);
                }
            }
            //if it validate
        } else {
            if (isset($sid)) {
                return \Redirect::to('admin/categories/edit/' . $sid)->withErrors($validation)->withInput();
            } else {
                return \Redirect::to('admin/categories/create')->withErrors($validation)->withInput();
            }
        }

        return \Redirect::to('admin/categories');
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
            return \Redirect::to('/admin/categories')->withErrors($errors);
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
            return \Redirect::to('/admin/categories')->withErrors($errors);
        }

        // Check in use by media
        $medias = Media::where('category_id', $sid)->get();
        if (count($medias) > 0) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add(
                'deleteError',
                "The category '" . $category->name . "' cannot be deleted because it is in used."
            );
            return \Redirect::to('/admin/categories')->withErrors($errors);
        }

        // Delete all images first
        $category->deleteAllImages();

        // Delete the category
        $category->delete();

        return \Redirect::to('admin/categories');
    }
}
