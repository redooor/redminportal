<?php namespace Redooor\Redminportal\App\Http\Controllers;

use Input;
use Redooor\Redminportal\App\Http\Traits\SorterController;
use Redooor\Redminportal\App\Http\Traits\DeleterController;
use Redooor\Redminportal\App\Models\Category;
use Redooor\Redminportal\App\Models\Bundle;
use Redooor\Redminportal\App\Models\Product;
use Redooor\Redminportal\App\Models\Pricelist;
use Redooor\Redminportal\App\Models\Translation;
use Redooor\Redminportal\App\Models\Tag;
use Redooor\Redminportal\App\Models\Image;
use Redooor\Redminportal\App\Helpers\RImage;

class BundleController extends Controller
{
    use SorterController, DeleterController;
    
    public function __construct(Bundle $model)
    {
        $this->model = $model;
        $this->sortBy = 'name';
        $this->orderBy = 'asc';
        $this->perpage = config('redminportal::pagination.size');
        $this->pageView = 'redminportal::bundles.view';
        $this->pageRoute = 'admin/bundles';
        
        // For sorting
        $this->query = $this->model
            ->LeftJoin('categories', 'bundles.category_id', '=', 'categories.id')
            ->select('bundles.*', 'categories.name as category_name');
    }
    
    public function getIndex()
    {
        $models = Bundle::orderBy($this->sortBy, $this->orderBy)->paginate($this->perpage);
        
        $data = [
            'models' => $models,
            'sortBy' => $this->sortBy,
            'orderBy' => $this->orderBy
        ];

        return view('redminportal::bundles/view', $data);
    }
    
    public function getCreate()
    {
        $categories = Category::where('active', true)
            ->where('category_id', 0)
            ->orWhere('category_id', null)
            ->orderBy('name')
            ->get();
        
        $products = Product::where('active', true)->lists('name', 'id');
        
        $membermodules = array();

        $pricelists = Pricelist::join('modules', 'modules.id', '=', 'pricelists.module_id')
            ->join('memberships', 'memberships.id', '=', 'pricelists.membership_id')
            ->where('pricelists.active', true)
            ->orderBy('modules.name')
            ->orderBy('memberships.rank', 'desc')
            ->select('pricelists.*')
            ->get();

        foreach ($pricelists as $pricelist) {
            $membermodules[$pricelist->id] =
            $pricelist->module->name . " (" . $pricelist->membership->name . ")";
        }
        
        $data = array(
            'categories' => $categories,
            'products' => $products,
            'membermodules' => $membermodules
        );
        
        return view('redminportal::bundles/create', $data);
    }
    
    public function getEdit($sid)
    {
        $bundle = Bundle::find($sid);
        if ($bundle == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add(
                'editError',
                "The bundle cannot be found because it does not exist or may have been deleted."
            );
            return redirect('/admin/bundles')->withErrors($errors);
        }
        
        $categories = Category::where('active', true)
            ->where('category_id', 0)
            ->orWhere('category_id', null)
            ->orderBy('name')
            ->get();
        
        $products = Product::where('active', true)->lists('name', 'id');
        
        $membermodules = array();

        $pricelists = Pricelist::join('modules', 'modules.id', '=', 'pricelists.module_id')
            ->join('memberships', 'memberships.id', '=', 'pricelists.membership_id')
            ->where('pricelists.active', true)
            ->orderBy('modules.name')
            ->orderBy('memberships.rank', 'desc')
            ->select('pricelists.*')
            ->get();

        foreach ($pricelists as $pricelist) {
            $membermodules[$pricelist->id] =
            $pricelist->module->name . " (" . $pricelist->membership->name . ")";
        }
        
        $product_id = array();
        foreach ($bundle->products as $product) {
            $product_id[$product->id] = $product->id;
        }
        
        $pricelist_id = array();
        foreach ($bundle->pricelists as $pricelist) {
            $pricelist_id[$pricelist->id] = $pricelist->id;
        }
        
        $tagString = "";
        foreach ($bundle->tags as $tag) {
            if (! empty($tagString)) {
                $tagString .= ",";
            }

            $tagString .= $tag->name;
        }
        
        $translated = array();
        foreach ($bundle->translations as $translation) {
            $translated[$translation->lang] = json_decode($translation->content);
        }
        
        $data = array(
            'categories' => $categories,
            'products' => $products,
            'membermodules' => $membermodules,
            'bundle' => $bundle,
            'product_id' => $product_id,
            'pricelist_id' => $pricelist_id,
            'translated' => $translated,
            'categories'=> $categories,
            'tagString' => $tagString,
            'imagine' => new RImage
        );
        
        return view('redminportal::bundles/edit', $data);
    }
    
    public function postStore()
    {
        $sid = \Input::get('id');
        
        if (isset($sid)) {
            $url = 'admin/bundles/edit/' . $sid;
        } else {
            $url = 'admin/bundles/create';
        }
        
        $rules = array(
            'image'             => 'mimes:jpg,jpeg,png,gif|max:500',
            'name'              => 'required|unique:products,name' . (isset($sid) ? ',' . $sid : ''),
            'short_description' => 'required',
            'price'             => 'numeric',
            'sku'               => 'required|alpha_dash|unique:bundles,sku' . (isset($sid) ? ',' . $sid : ''),
            'category_id'       => 'required',
            'tags'              => 'regex:/^[a-z,0-9 -]+$/i',
        );
        
        $validation = \Validator::make(\Input::all(), $rules);

        if ($validation->fails()) {
            return redirect($url)->withErrors($validation)->withInput();
        }
        
        // If id is set, check that it exists
        if (isset($sid)) {
            $bundle = Bundle::find($sid);
            if ($bundle == null) {
                $errors = new \Illuminate\Support\MessageBag;
                $errors->add('bundleError', "The bundle does not exist or may have been deleted.");
                return redirect('admin/bundles')->withErrors($errors);
            }
        }
        
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

        $apply_to_models = array();

        $products = \Input::get('product_id');
        if (count($products) > 0) {
            foreach ($products as $item) {
                $model = Product::find($item);
                if ($model != null) {
                    $apply_to_models[] = $model;
                }
            }
        }

        $pricelists = \Input::get('pricelist_id');
        if (count($pricelists) > 0) {
            foreach ($pricelists as $item) {
                $model = Pricelist::find($item);
                if ($model != null) {
                    $apply_to_models[] = $model;
                }
            }
        }

        // In the worst scenario, all select items have been deleted
        if (count($apply_to_models) == 0) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add(
                'applyToError',
                "You have not selected any Product or Membership/Module under Bundled Items."
            );
            return redirect($url)->withErrors($errors)->withInput();
        }

        $newBundle = (isset($sid) ? $bundle : new Bundle);
        $newBundle->name = $name;
        $newBundle->sku = $sku;
        $newBundle->price = (isset($price) ? $price : 0);
        $newBundle->short_description = $short_description;
        $newBundle->long_description = $long_description;
        $newBundle->featured = $featured;
        $newBundle->active = $active;
        $newBundle->category_id = ($category_id > 0) ? $category_id : null;
        $newBundle->save();

        // Remove all existing relationships first
        if (isset($sid)) {
            $newBundle->pricelists()->detach();
            $newBundle->products()->detach();
        }

        foreach ($apply_to_models as $apply_to_model) {
            $apply_to_model->bundles()->save($newBundle);
        }
        
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
            $translated_model = $newBundle->translations->where('lang', $lang)->first();
            if ($translated_model == null) {
                $translated_model = new Translation;
            }
            
            $translated_model->lang = $lang;
            $translated_model->content = json_encode($translated_content);

            $newBundle->translations()->save($translated_model);
        }
        
        if (! empty($tags)) {
            // Delete old tags
            $newBundle->tags()->detach();

            // Save tags
            foreach (explode(',', $tags) as $tagName) {
                Tag::addTag($newBundle, $tagName);
            }
        }

        if (Input::hasFile('image')) {
            //Upload the file
            $helper_image = new RImage;
            $filename = $helper_image->upload($image, 'bundles/' . $newBundle->id, true);

            if ($filename) {
                // create photo
                $newimage = new Image;
                $newimage->path = $filename;

                // save photo to the loaded model
                $newBundle->images()->save($newimage);
            }
        }

        return redirect('admin/bundles');
    }
}
