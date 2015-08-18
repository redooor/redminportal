<?php namespace Redooor\Redminportal\App\Http\Controllers;

use Redooor\Redminportal\App\Models\Category;
use Redooor\Redminportal\App\Models\Coupon;
use Redooor\Redminportal\App\Models\Product;
use Redooor\Redminportal\App\Models\Pricelist;

class CouponController extends Controller
{
    public function getIndex()
    {
        $sortBy = 'start_date';
        $orderBy = 'desc';
        
        $coupons = Coupon::orderBy($sortBy, $orderBy)->paginate(20);

        return view('redminportal::coupons/view')
            ->with('coupons', $coupons)
            ->with('sortBy', $sortBy)
            ->with('orderBy', $orderBy);
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
            'membermodules' => $membermodules
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
        
        $data = array(
            'categories' => $categories,
            'products' => $products,
            'membermodules' => $membermodules,
            'coupon' => $coupon,
            'product_id' => $product_id,
            'category_id' => $category_id,
            'pricelist_id' => $pricelist_id
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
        $newCoupon->start_date              = $start_date;
        $newCoupon->end_date                = $end_date;
        $newCoupon->max_spent              = $max_spent;
        $newCoupon->min_spent              = $min_spent;
        $newCoupon->usage_limit_per_coupon = $limit_per_coupon;
        $newCoupon->usage_limit_per_user   = $limit_per_user;
        $newCoupon->multiple_coupons       = $multiple_coupons;
        $newCoupon->exclude_sale_item      = $exclude_sale_item;
        $newCoupon->save();

        // Remove all existing relationships first
        if (isset($sid)) {
            $coupon->categories()->detach();
            $coupon->pricelists()->detach();
            $coupon->products()->detach();
        }

        foreach ($apply_to_models as $apply_to_model) {
            $apply_to_model->coupons()->save($newCoupon);
        }

        return redirect('admin/coupons');
    }

    public function getDelete($sid)
    {
        $coupon = Coupon::find($sid);

        // No such id
        if ($coupon == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add('deleteError', "The coupon may have been deleted.");
            return redirect('admin/coupons')->withErrors($errors)->withInput();
        }

        $coupon->delete();

        return redirect('admin/coupons');
    }
    
    public function getSort($sortBy = 'create_at', $orderBy = 'desc')
    {
        $inputs = array(
            'sortBy' => $sortBy,
            'orderBy' => $orderBy
        );
        
        $rules = array(
            'sortBy'  => 'required|regex:/^[a-zA-Z0-9 _-]*$/',
            'orderBy' => 'required|regex:/^[a-zA-Z0-9 _-]*$/'
        );
        
        $validation = \Validator::make($inputs, $rules);

        if ($validation->fails()) {
            return redirect('admin/coupons')->withErrors($validation);
        }
        
        if ($orderBy != 'asc' && $orderBy != 'desc') {
            $orderBy = 'asc';
        }
        
        $coupons = Coupon::orderBy($sortBy, $orderBy)->paginate(20);

        return view('redminportal::coupons/view')
            ->with('coupons', $coupons)
            ->with('sortBy', $sortBy)
            ->with('orderBy', $orderBy);
    }
}
