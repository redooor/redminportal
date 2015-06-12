<?php namespace Redooor\Redminportal\App\Http\Controllers;

use Redooor\Redminportal\App\Models\Product;
use Redooor\Redminportal\App\Models\Category;
use Redooor\Redminportal\App\Models\Image;
use Redooor\Redminportal\App\Models\Translation;
use Redooor\Redminportal\App\Models\Tag;
use Redooor\Redminportal\App\Helpers\RImage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;

class ProductController extends Controller
{
    protected $model;

    public function __construct(Product $product)
    {
        $this->model = $product;
    }

    public function getIndex()
    {
        $products = Product::orderBy('category_id')
            ->orderBy('name')
            ->paginate(20);

        return view('redminportal::products/view')->with('products', $products);
    }

    public function getCreate()
    {
        $categories = Category::where('active', true)
            ->where('category_id', 0)
            ->orWhere('category_id', null)
            ->orderBy('name')
            ->get();

        return view('redminportal::products/create')->with('categories', $categories);
    }

    public function getEdit($sid)
    {
        // Find the product using the user id
        $product = Product::find($sid);

        // No such id
        if ($product == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add(
                'editError',
                "The product cannot be found because it does not exist or may have been deleted."
            );
            return redirect('/admin/products')->withErrors($errors);
        }

        $categories = Category::where('active', true)
            ->where('category_id', 0)
            ->orWhere('category_id', null)
            ->orderBy('name')
            ->get();

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

        return view('redminportal::products/edit')
            ->with('product', $product)
            ->with('translated', $translated)
            ->with('categories', $categories)
            ->with('tagString', $tagString)
            ->with('imagine', new RImage);
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
                return redirect('/admin/products')->withErrors($errors);
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
                $translated_model = $product->translations->where('lang', $lang)->first();
                if ($translated_model == null) {
                    $translated_model = new Translation;
                }

                $translated_model->lang = $lang;
                $translated_model->content = json_encode($translated_content);

                $product->translations()->save($translated_model);
            }

            if (! empty($tags)) {
                // Delete old tags
                $product->tags()->detach();

                // Save tags
                foreach (explode(',', $tags) as $tagName) {
                    Tag::addTag($product, $tagName);
                }
            }

            if (Input::hasFile('image')) {
                //Upload the file
                $helper_image = new RImage;
                $filename = $helper_image->upload($image, 'products/' . $product->id, true);

                if ($filename) {
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
                return redirect('admin/products/edit/' . $sid)->withErrors($validation)->withInput();
            } else {
                return redirect('admin/products/create')->withErrors($validation)->withInput();
            }
        }

        return redirect('admin/products');
    }

    public function getDelete($sid)
    {
        // Find the product using the user id
        $product = Product::find($sid);

        if ($product == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add('deleteError', "We are having problem deleting this entry. Please try again.");
            return redirect('admin/products')->withErrors($errors);
        }
        
        // Check if there's any order related to this product
        if (count($product->orders) > 0) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add('deleteError', "You cannot delete this product because it has been ordered. Please delete the order first.");
            return redirect('admin/products')->withErrors($errors);
        }
        
        // Delete the product
        $product->delete();

        return redirect('admin/products');
    }
    
    public function getImgremove($sid)
    {
        $image = Image::find($sid);

        if ($image == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add('deleteError', "The image cannot be deleted at this time.");
            return redirect('/admin/products')->withErrors($errors);
        }

        $model_id = $image->imageable_id;

        $image->delete();

        return redirect('admin/products/edit/' . $model_id);
    }
}
