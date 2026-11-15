<?php namespace Redooor\Redminportal\App\Http\Controllers;

use Redooor\Redminportal\App\Models\Category;
use Redooor\Redminportal\App\Classes\ImageService;
use Redooor\Redminportal\App\Http\Concerns\PreparesEditData;
use Redooor\Redminportal\App\Http\Concerns\RedirectsNotFound;
use Redooor\Redminportal\App\Http\Concerns\RemovesGalleryImages;
use Redooor\Redminportal\App\Http\Concerns\SavesTranslations;
use Redooor\Redminportal\App\Http\Concerns\ValidatesInput;
use Redooor\Redminportal\App\Http\Validation\CategoryRules;
use Redooor\Redminportal\App\Http\Traits\ImageGalleryController;
use Redooor\Redminportal\App\Helpers\RImage;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Lang;

class CategoryController extends Controller
{
    use ImageGalleryController, PreparesEditData, RedirectsNotFound, RemovesGalleryImages, SavesTranslations, ValidatesInput;

    public function getIndex()
    {
        $categories = Category::where('category_id', 0)
            ->orWhere('category_id', null)
            ->orderBy('order', 'desc')
            ->orderBy('name')
            ->get();

        return view('redminportal::categories/view')->with('categories', $categories);
    }

    public function getCreate()
    {
        $categories = Category::where('active', true)
            ->where('category_id', 0)
            ->orWhere('category_id', null)
            ->orderBy('name')
            ->get();

        return view('redminportal::categories/create')->with('categories', $categories);
    }

    public function getDetail($sid)
    {
        $category = Category::find($sid);

        if ($category == null) {
            return view('redminportal::pages.404');
        }

        return view('redminportal::categories/detail')
            ->with('category', $category)
            ->with('imagine', new RImage);
    }

    public function getEdit($sid)
    {
        // Find the category using the user id
        $category = Category::find($sid);

        if ($category == null) {
            return $this->redirectNotFoundWithMessage(
                '/admin/categories',
                Lang::get('redminportal::messages.category_error_category_not_found')
            );
        }
        
        $translated = $this->translatedContentFor($category);

        $categories = Category::where('active', true)
            ->where('category_id', 0)
            ->orWhere('category_id', null)
            ->orderBy('name')
            ->get();

        return view('redminportal::categories/edit')
            ->with('category', $category)
            ->with('translated', $translated)
            ->with('imagine', new RImage)
            ->with('categories', $categories);
    }

    public function postStore()
    {
        $sid = Request::input('id');
        
        $return_path = (isset($sid) ? 'admin/categories/edit/' . $sid : 'admin/categories/create');

        /*
         * Validate
         */
        $rules = new CategoryRules;
        $failure = $this->validateInputOrRedirect($rules->rules($sid), $rules->messages(), $return_path);
        if ($failure) {
            return $failure;
        }
        
        $name               = Request::input('name');
        $short_description  = Request::input('short_description');
        $long_description   = Request::input('long_description');
        $image              = Request::file('image');
        $active             = (Request::input('active') == '' ? false : true);
        $order              = Request::input('order');
        $parent_id          = Request::input('parent_id');

        $category = (isset($sid) ? Category::find($sid) : new Category);

        if ($category == null) {
            return $this->redirectNotFoundWithMessage(
                '/admin/categories',
                Lang::get('redminportal::messages.category_error_category_not_found')
            );
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
                Lang::get('redminportal::messages.category_error_name_already_exists')
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
        $this->saveTranslationsFromInput($category, array('name', 'short_description', 'long_description'));

        $imageFiles = ImageService::filesFrom();

        if (! empty($imageFiles)) {
            (new ImageService)->attach($category, $imageFiles, 'categories');
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
                Lang::get('redminportal::messages.category_error_category_not_found')
            );
            return redirect()->back()->withErrors($errors);
        }

        // Find if there's any child
        $children = Category::where('category_id', $sid)->count();

        if ($children > 0) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add(
                'deleteError',
                Lang::get(
                    'redminportal::messages.category_error_cannot_delete_has_child',
                    ['name' => $category->name, 'children' => $children]
                )
            );
            return redirect()->back()->withErrors($errors);
        }
        
        // Delete the category
        $category->delete();

        return redirect()->back();
    }
    
    public function getImgremove($sid)
    {
        return $this->removeOwnedGalleryImage($sid, Category::class, 'admin/categories');
    }

    protected function galleryModelClass(): string
    {
        return Category::class;
    }

    protected function galleryPath(): string
    {
        return 'admin/categories';
    }
}
