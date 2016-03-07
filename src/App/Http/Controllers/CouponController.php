<?php namespace Redooor\Redminportal\App\Http\Controllers;

use Redooor\Redminportal\App\Http\Traits\SorterController;
use Redooor\Redminportal\App\Http\Traits\DeleterController;
use Redooor\Redminportal\App\Models\Category;
use Redooor\Redminportal\App\Models\Coupon;
use Redooor\Redminportal\App\Models\Product;
use Redooor\Redminportal\App\Models\Pricelist;
use Redooor\Redminportal\App\Models\Bundle;

class CouponController extends Controller
{
    use SorterController, DeleterController;
    
    public function __construct(Coupon $model)
    {
        $this->model = $model;
        $this->sortBy = 'start_date';
        $this->orderBy = 'desc';
        $this->perpage = config('redminportal::pagination.size');
        $this->pageView = 'redminportal::coupons.view';
        $this->pageRoute = 'admin/coupons';
        
        // For sorting
        $this->query = $this->model;
    }
    
    public function getIndex()
    {
        $models = Coupon::orderBy($this->sortBy, $this->orderBy)->paginate($this->perpage);

        $data = [
            'models' => $models,
            'sortBy' => $this->sortBy,
            'orderBy' => $this->orderBy
        ];
        
        return view('redminportal::coupons/view', $data);
    }
    
    private function getCategories()
    {
        $category_models = Category::where('active', true)
            ->where('category_id', 0)
            ->orWhere('category_id', null)
            ->orderBy('name')
            ->get();
        
        $categories = $this->recursivePrint($category_models);
        natsort($categories);
        
        return $categories;
    }
    
    public function getCreate()
    {
        $categories = $this->getCategories();
        
        $products = Product::where('active', true)->lists('name', 'id');
        $bundles = Bundle::where('active', true)->lists('name', 'id');
        
        $membermodules = array();

        $pricelists = Pricelist::join('modules', 'modules.id', '=', 'pricelists.module_id')
            ->join('memberships', 'memberships.id', '=', 'pricelists.membership_id')
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
            'membermodules' => $membermodules,
            'bundles' => $bundles
        );
        
        return view('redminportal::coupons/create', $data);
    }
    
    public function getEdit($sid)
    {
        $coupon = Coupon::find($sid);
        if ($coupon == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add(
                'editError',
                "The coupon cannot be found because it does not exist or may have been deleted."
            );
            return redirect('/admin/coupons')->withErrors($errors);
        }
        
        $categories = $this->getCategories();
        
        $products = Product::where('active', true)->lists('name', 'id');
        $bundles = Bundle::where('active', true)->lists('name', 'id');
        
        $membermodules = array();

        $pricelists = Pricelist::join('modules', 'modules.id', '=', 'pricelists.module_id')
            ->join('memberships', 'memberships.id', '=', 'pricelists.membership_id')
            ->orderBy('modules.name')
            ->orderBy('memberships.rank', 'desc')
            ->select('pricelists.*')
            ->get();

        foreach ($pricelists as $pricelist) {
            $membermodules[$pricelist->id] =
            $pricelist->module->name . " (" . $pricelist->membership->name . ")";
        }
        
        $product_id = array();
        foreach ($coupon->products as $product) {
            $product_id[$product->id] = $product->id;
        }
        
        $category_id = array();
        foreach ($coupon->categories as $category) {
            $category_id[$category->id] = $category->id;
        }
        
        $pricelist_id = array();
        foreach ($coupon->pricelists as $pricelist) {
            $pricelist_id[$pricelist->id] = $pricelist->id;
        }
        
        $bundle_id = array();
        foreach ($coupon->bundles as $bundle) {
            $bundle_id[$bundle->id] = $bundle->id;
        }
        
        $data = array(
            'categories' => $categories,
            'products' => $products,
            'membermodules' => $membermodules,
            'bundles' => $bundles,
            'coupon' => $coupon,
            'product_id' => $product_id,
            'category_id' => $category_id,
            'pricelist_id' => $pricelist_id,
            'bundle_id' => $bundle_id
        );
        
        return view('redminportal::coupons/edit', $data);
    }
    
    private function recursivePrint($cats, $parent = null)
    {
        $categories = array();
        $subcategories = array();
        
        foreach ($cats as $cat) {
            $categories[$cat->id] = ($parent == null ? '' : $parent->name . ' / ') . $cat->name;
            if (count($cat->categories) > 0) {
                $subcategories = $this->recursivePrint($cat->categories, $cat);
                $categories = $categories + $subcategories; // Merge array while preserving keys
            }
        }
        
        return $categories;
    }
    
    public function postStore()
    {
        $sid = \Input::get('id');
        
        if (isset($sid)) {
            $url = 'admin/coupons/edit/' . $sid;
        } else {
            $url = 'admin/coupons/create';
        }
        
        $rules = array(
            'code'          => 'required',
            'amount'        => 'required',
            'start_date'    => 'required',
            'end_date'      => 'required',
            'amount'        => 'numeric',
            'min_spent'     => 'numeric',
            'max_spent'     => 'numeric'
        );
        
        $validation = \Validator::make(\Input::all(), $rules);

        if ($validation->fails()) {
            return redirect($url)->withErrors($validation)->withInput();
        }
        
        // If id is set, check that it exists
        if (isset($sid)) {
            $coupon = Coupon::find($sid);
            if ($coupon == null) {
                $errors = new \Illuminate\Support\MessageBag;
                $errors->add('couponError', "The coupon does not exist or may have been deleted.");
                return redirect('admin/coupons')->withErrors($errors);
            }
        }
        $code                   = \Input::get('code');
        $description            = \Input::get('description');
        $amount                 = \Input::get('amount');
        $is_percent             = (\Input::get('is_percent') == '' ? false : true);
        $start_date             = \DateTime::createFromFormat('d/m/Y h:i A', \Input::get('start_date'));
        $end_date               = \DateTime::createFromFormat('d/m/Y h:i A', \Input::get('end_date'));
        $max_spent              = \Input::get('max_spent');
        $min_spent              = \Input::get('min_spent');
        $limit_per_coupon       = \Input::get('usage_limit_per_coupon');
        $limit_per_user         = \Input::get('usage_limit_per_user');
        $multiple_coupons       = (\Input::get('multiple_coupons') == '' ? false : true);
        $exclude_sale_item      = (\Input::get('exclude_sale_item') == '' ? false : true);
        $automatically_apply    = (\Input::get('automatically_apply') == '' ? false : true);

        // Check that end date is after start date
        if ($end_date <= $start_date) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add('dateRangeError', "Please enter an End date later than Start date.");
            return redirect($url)->withErrors($errors)->withInput();
        }

        // Check if max spent is less than min spent, only if both not null
        if ($max_spent and $min_spent) {
            if ((float)$max_spent < (float)$min_spent) {
                $errors = new \Illuminate\Support\MessageBag;
                $errors->add('spentRangeError', "Max spent cannot be less than Min spent.");
                return redirect($url)->withErrors($errors)->withInput();
            }
        }

        $apply_to_models = array();

        $categories = \Input::get('category_id');
        if (count($categories) > 0) {
            foreach ($categories as $item) {
                $model = Category::find($item);
                if ($model != null) {
                    $apply_to_models[] = $model;
                }
            }
        }

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
        
        $bundles = \Input::get('bundle_id');
        if (count($bundles) > 0) {
            foreach ($bundles as $item) {
                $model = Bundle::find($item);
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
                "You have not selected any Category, Product or Membership/Module under Restricted To."
            );
            return redirect($url)->withErrors($errors)->withInput();
        }

        $newCoupon = (isset($sid) ? $coupon : new Coupon);
        $newCoupon->code                   = $code;
        $newCoupon->description            = $description;
        $newCoupon->amount                 = $amount;
        $newCoupon->is_percent             = $is_percent;
        $newCoupon->start_date             = $start_date;
        $newCoupon->end_date               = $end_date;
        $newCoupon->max_spent              = ($max_spent == 0) ? null : $max_spent;
        $newCoupon->min_spent              = ($min_spent == 0) ? null : $min_spent;
        $newCoupon->usage_limit_per_coupon = ($limit_per_coupon == 0) ? null : $limit_per_coupon;
        $newCoupon->usage_limit_per_user   = ($limit_per_user == 0) ? null : $limit_per_user;
        $newCoupon->multiple_coupons       = $multiple_coupons;
        $newCoupon->exclude_sale_item      = $exclude_sale_item;
        $newCoupon->automatically_apply    = $automatically_apply;
        $newCoupon->save();

        // Remove all existing relationships first
        if (isset($sid)) {
            $coupon->categories()->detach();
            $coupon->pricelists()->detach();
            $coupon->products()->detach();
            $coupon->bundles()->detach();
        }

        foreach ($apply_to_models as $apply_to_model) {
            $apply_to_model->coupons()->save($newCoupon);
        }

        return redirect('admin/coupons');
    }
}
