<?php namespace Redooor\Redminportal;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;
use Cartalyst\Sentry\Facades\Laravel\Sentry;

class ProductController extends BaseController
{
    protected $model;

    public function __construct(Product $product)
    {
        $this->model = $product;
    }

    public function getIndex()
    {
        $products = Product::orderBy('category_id')->orderBy('name')->paginate(20);

        return View::make('redminportal::products/view')->with('products', $products);
    }

    public function getCreate()
    {
        $categories = Category::where('active', true)->where('category_id', 0)->orWhere('category_id', null)->orderBy('name')->get();

        return View::make('redminportal::products/create')->with('categories', $categories);
    }

    public function getEdit($sid)
    {
        // Find the product using the user id
        $product = Product::find($sid);

        // No such id
        if ($product == null) {
            return \View::make('redminportal::pages/404');
        }

        $categories = Category::where('active', true)->where('category_id', 0)->orWhere('category_id', null)->orderBy('name')->get();

        $tagString = "";
        foreach ($product->tags as $tag) {
            if (! empty($tagString)) {
                $tagString .= ",";
            }

            $tagString .= $tag->name;
        }

        $translated = array();
        foreach ($product->translations as $translation) {
            $translated[$translation->lang] = json_decode($translation->content);
        }

        return View::make('redminportal::products/edit')
            ->with('product', $product)
            ->with('translated', $translated)
            ->with('imageUrl', 'assets/img/products/')
            ->with('categories', $categories)
            ->with('tagString', $tagString);
    }

    public function postStore()
    {
        $sid = Input::get('id');

        /*
         * Validate
         */
        $rules = array(
            'image'             => 'mimes:jpg,jpeg,png,gif|max:500',
            'name'              => 'required|unique:products,name' . (isset($sid) ? ',' . $sid : ''),
            'short_description' => 'required',
            'price'             => 'numeric',
            'sku'               => 'required|alpha_dash|unique:products,sku' . (isset($sid) ? ',' . $sid : ''),
            'category_id'       => 'required',
            'tags'              => 'regex:/^[a-z,0-9 -]+$/i',
        );

        $validation = Validator::make(Input::all(), $rules);

        if ($validation->passes()) {
            $name               = Input::get('name');
            $sku                = Input::get('sku');
            $price              = Input::get('price');
            $short_description  = Input::get('short_description');
            $long_description   = Input::get('long_description');
            $image              = Input::file('image');
            $featured           = (Input::get('featured') == '' ? false : true);
            $active             = (Input::get('active') == '' ? false : true);
            $category_id        = Input::get('category_id');
            $tags               = Input::get('tags');

            $product = (isset($sid) ? Product::find($sid) : new Product);
            
            if ($product == null) {
                $errors = new \Illuminate\Support\MessageBag;
                $errors->add(
                    'editError',
                    "The product cannot be found because it does not exist or may have been deleted."
                );
                return \Redirect::to('/admin/products')->withErrors($errors);
            }
            
            $product->name = $name;
            $product->sku = $sku;
            $product->price = (isset($price) ? $price : 0);
            $product->short_description = $short_description;
            $product->long_description = $long_description;
            $product->featured = $featured;
            $product->active = $active;
            $product->category_id = $category_id;

            $product->save();
            
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
                $translated_model = $product->translations()->where('lang', $lang)->first();
                if ($translated_model == null) {
                    $translated_model = new Translation;
                }

                $translated_model->lang = $lang;
                $translated_model->content = json_encode($translated_content);

                $product->translations()->save($translated_model);
            }

            if (! empty($tags)) {
                // Delete old tags
                $product->deleteAllTags();

                // Save tags
                foreach (explode(',', $tags) as $tagName) {
                    $newTag = new Tag;
                    $newTag->name = strtolower($tagName);
                    $product->tags()->save($newTag);
                }
            }

            if (Input::hasFile('image')) {
                // Delete all existing images for edit
                if (isset($sid)) {
                    $product->deleteAllImages();
                }

                //set the name of the file
                $originalFilename = $image->getClientOriginalName();
                $filename = $sku . Str::random(20) .'.'. File::extension($originalFilename);

                //Upload the file
                $isSuccess = $image->move('assets/img/products', $filename);

                if ($isSuccess) {
                    // create photo
                    $newimage = new Image;
                    $newimage->path = $filename;

                    // save photo to the loaded model
                    $product->images()->save($newimage);
                }
            }
        //if it validate
        } else {
            if (isset($sid)) {
                return Redirect::to('admin/products/edit/' . $sid)->withErrors($validation)->withInput();
            } else {
                return Redirect::to('admin/products/create')->withErrors($validation)->withInput();
            }
        }

        return Redirect::to('admin/products');
    }

    public function getDelete($sid)
    {
        // Find the product using the user id
        $product = Product::find($sid);

        if ($product == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add('deleteError', "We are having problem deleting this entry. Please try again.");
            return \Redirect::to('admin/products')->withErrors($errors);
        }

        // Delete all images first
        $product->deleteAllImages();

        // Delete all tags
        $product->deleteAllTags();

        // Delete the product
        $product->delete();

        return Redirect::to('admin/products');
    }
}
