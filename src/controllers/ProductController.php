<?php namespace Redooor\Redminportal;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;
use Cartalyst\Sentry\Facades\Laravel\Sentry;

class ProductController extends BaseController {

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
        $categories = array();

        foreach (Category::all() as $category) {
            $categories[$category->id] = $category->name;
        }

        return View::make('redminportal::products/create')->with('categories', $categories);
    }

    public function getEdit($id)
    {
        // Find the product using the user id
        $product = Product::find($id);

        // No such id
        if ($product == null) {
            return \View::make('redminportal::pages/404');
        }

        $categories = array();

        foreach (Category::all() as $category) {
            $categories[$category->id] = $category->name;
        }

        $tagString = "";
        foreach ($product->tags as $tag)
        {
            if(! empty($tagString))
            {
                $tagString .= ",";
            }

            $tagString .= $tag->name;
        }

		if(empty($product->options))
        {
            $product_cn = (object) array(
                'name'                  => $product->name,
                'short_description'     => $product->short_description,
                'long_description'      => $product->long_description
            );
        }
        else
        {
            $product_cn = json_decode($product->options);
        }

        return View::make('redminportal::products/edit')
            ->with('product', $product)
			->with('product_cn', $product_cn)
            ->with('imageUrl', 'assets/img/products/')
            ->with('categories', $categories)
            ->with('tagString', $tagString);
    }

    public function postStore()
    {
        $id = Input::get('id');

        /*
         * Validate
         */
        $rules = array(
            'image'             => 'mimes:jpg,jpeg,png,gif|max:500',
            'name'              => 'required|unique:products,name' . (isset($id) ? ',' . $id : ''),
            'short_description' => 'required',
            'price'             => 'numeric',
            'sku'               => 'required|alpha_dash|unique:products,sku' . (isset($id) ? ',' . $id : ''),
            'category_id'       => 'required',
            'tags'              => 'regex:/^[a-z,0-9 -]+$/i',
        );

        $validation = Validator::make(Input::all(), $rules);

        if( $validation->passes() )
        {
            $name               = Input::get('name');
            $sku                = Input::get('sku');
            $price              = Input::get('price');
            $short_description  = Input::get('short_description');
            $long_description   = Input::get('long_description');
            $image              = Input::file('image');
            $featured           = (Input::get('featured') == '' ? FALSE : TRUE);
            $active             = (Input::get('active') == '' ? FALSE : TRUE);
            $category_id        = Input::get('category_id');
            $tags               = Input::get('tags');

			$cn_name               = Input::get('cn_name');
            $cn_short_description  = Input::get('cn_short_description');
            $cn_long_description   = Input::get('cn_long_description');

			$options = array(
                'name'                  => $cn_name,
                'short_description'     => $cn_short_description,
                'long_description'      => $cn_long_description
            );

            $product = (isset($id) ? Product::find($id) : new Product);
            $product->name = $name;
            $product->sku = $sku;
            $product->price = (isset($price) ? $price : 0);
            $product->short_description = $short_description;
            $product->long_description = $long_description;
            $product->featured = $featured;
            $product->active = $active;
            $product->category_id = $category_id;
            $product->options = json_encode($options);

            $product->save();

            if(! empty($tags))
            {
                // Delete old tags
                $product->deleteAllTags();

                // Save tags
                foreach(explode(',', $tags) as $tagName)
                {
                    $newTag = new Tag;
                    $newTag->name = strtolower($tagName);
                    $product->tags()->save($newTag);
                }
            }

            if(Input::hasFile('image'))
            {
                // Delete all existing images for edit
                if(isset($id)) $product->deleteAllImages();

                //set the name of the file
                $originalFilename = $image->getClientOriginalName();
                $filename = $sku . Str::random(20) .'.'. File::extension($originalFilename);

                //Upload the file
                $isSuccess = $image->move('assets/img/products', $filename);

                if( $isSuccess )
                {
                    // create photo
                    $newimage = new Image;
                    $newimage->path = $filename;

                    // save photo to the loaded model
                    $product->images()->save($newimage);
                }
            }

        }//if it validate
        else {
            if(isset($id))
            {
                return Redirect::to('admin/products/edit/' . $id)->withErrors($validation)->withInput();
            }
            else
            {
                return Redirect::to('admin/products/create')->withErrors($validation)->withInput();
            }
        }

        return Redirect::to('admin/products');
    }

    public function getDelete($id)
    {
        // Find the product using the user id
        $product = Product::find($id);

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
