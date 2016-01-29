<?php namespace Redooor\Redminportal\App\Http\Controllers;

use Exception;
use Validator;
use Redooor\Redminportal\App\Http\Traits\SorterController;
use Redooor\Redminportal\App\Http\Traits\DeleterController;
use Redooor\Redminportal\App\Http\Traits\SearcherController;
use Redooor\Redminportal\App\Http\Traits\RevisionableController;
use Redooor\Redminportal\App\Models\User;
use Redooor\Redminportal\App\Models\Order;
use Redooor\Redminportal\App\Models\Product;
use Redooor\Redminportal\App\Models\Bundle;
use Redooor\Redminportal\App\Models\Coupon;
use Redooor\Redminportal\App\Models\Pricelist;

class OrderController extends Controller
{
    use SorterController, DeleterController, SearcherController, RevisionableController;
    
    public function __construct(Order $model)
    {
        $this->model = $model;
        $this->sortBy = 'created_at';
        $this->orderBy = 'desc';
        $this->perpage = config('redminportal::pagination.size');
        $this->pageView = 'redminportal::orders.view';
        $this->pageRoute = 'admin/orders';
        
        // For sorting
        $this->query = $this->model
            ->LeftJoin('users', 'orders.user_id', '=', 'users.id')
            ->select('users.email', 'users.first_name', 'users.last_name', 'orders.*');
        
        // For searching
        $this->searchable_fields = [
            'all' => 'Search all',
            'email' => 'Email',
            'first_name' => 'First name',
            'last_name' => 'Last name',
            'transaction_id' => 'Transaction Id',
            'payment_status' => 'Payment status'
        ];
        
        // Default data
        $this->data = [
            'sortBy' => $this->sortBy,
            'orderBy' => $this->orderBy,
            'searchable_fields' => $this->searchable_fields,
            'payment_statuses' => config('redminportal::payment_statuses')
        ];
    }
    
    public function getIndex()
    {
        $models = Order::orderBy($this->sortBy, $this->orderBy)->paginate($this->perpage);

        $data = array_merge($this->data, [
            'models' => $models
        ]);
        
        return view('redminportal::orders.view', $data);
    }
    
    public function getCreate()
    {
        $products = Product::where('active', true)->orderBy('name')->lists('name', 'id');
        $bundles = Bundle::where('active', true)->orderBy('name')->lists('name', 'id');
        $coupons = Coupon::orderBy('code')->lists('code', 'id');
        
        $pricelists = array();

        $pricelists_get = Pricelist::join('modules', 'modules.id', '=', 'pricelists.module_id')
            ->join('memberships', 'memberships.id', '=', 'pricelists.membership_id')
            ->where('modules.active', true)
            ->orderBy('modules.name')
            ->orderBy('memberships.rank', 'desc')
            ->select('pricelists.*')
            ->get();

        foreach ($pricelists_get as $pricelist) {
            $pricelists[$pricelist->id] = $pricelist->name;
        }

        $payment_statuses = config('redminportal::payment_statuses');

        return view('redminportal::orders/create')
            ->with('products', $products)
            ->with('bundles', $bundles)
            ->with('coupons', $coupons)
            ->with('pricelists', $pricelists)
            ->with('payment_statuses', $payment_statuses);
    }
    
    public function getEdit($sid = null)
    {
        $sid = null;
        $errors = new \Illuminate\Support\MessageBag;
        $errors->add(
            'editError',
            "The edit function has been disabled for all orders."
        );
        return redirect('/admin/orders')->withErrors($errors);
    }

    public function postStore()
    {
        $sid = \Input::get('id');

        $rules = array(
            'product_id'        => 'required_without_all:bundle_id,pricelist_id',
            'bundle_id'         => 'required_without_all:product_id,pricelist_id',
            'pricelist_id'      => 'required_without_all:product_id,bundle_id',
            'transaction_id'    => 'required',
            'payment_status'    => 'required',
            'paid'              => 'numeric',
            'email'             => 'required|email'
        );

        $validation = \Validator::make(\Input::all(), $rules);

        $redirect_url = (isset($sid)) ? 'admin/orders/edit/' . $sid : 'admin/orders/create';
        
        if ($validation->fails()) {
            return redirect($redirect_url)->withErrors($validation)->withInput();
        }
        
        $transaction_id = \Input::get('transaction_id');
        $payment_status = \Input::get('payment_status');
        $paid           = \Input::get('paid');
        $email          = \Input::get('email');
        
        $apply_to_models = array();
        
        // Save products to order
        $products = \Input::get('product_id');
        if (count($products) > 0) {
            foreach ($products as $item) {
                $model = Product::find($item);
                if ($model != null) {
                    $apply_to_models[] = $model;
                }
            }
        }
        // Save bundles to order
        $bundles = \Input::get('bundle_id');
        if (count($bundles) > 0) {
            foreach ($bundles as $item) {
                $model = Bundle::find($item);
                if ($model != null) {
                    $apply_to_models[] = $model;
                }
            }
        }
        // Save pricelists to order
        $pricelists = \Input::get('pricelist_id');
        if (count($pricelists) > 0) {
            foreach ($pricelists as $item) {
                $model = Pricelist::find($item);
                if ($model != null) {
                    $apply_to_models[] = $model;
                }
            }
        }

        // No product/bundle to add
        if (count($apply_to_models) == 0) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add('productError', "The items selected may have been deleted. Please try again.");
            return redirect($redirect_url)->withErrors($errors)->withInput();
        }
        
        $user = User::where('email', $email)->first();
        
        if ($user == null) {
            // No such user
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add('userError', "The user may have been deleted. Please try again.");
            return redirect($redirect_url)->withErrors($errors)->withInput();
        }
        
        $new_order = new Order;
        $new_order->user_id = $user->id;
        $new_order->paid = $paid;
        $new_order->transaction_id = $transaction_id;
        $new_order->payment_status = $payment_status;
        $new_order->save();
        
        // Save the products/bundles
        foreach ($apply_to_models as $apply_to_model) {
            $apply_to_model->orders()->save($new_order);
        }
        
        // Save coupons to order
        $coupons = \Input::get('coupon_id');
        if (count($coupons) > 0) {
            $errors = new \Illuminate\Support\MessageBag;
            foreach ($coupons as $item) {
                $model = Coupon::find($item);
                if ($model != null) {
                    try {
                        $new_order->addCoupon($model);
                    } catch (Exception $exp) {
                        $errors->add(
                            'couponError',
                            "Coupon " . $model->code . " cannot be added because: " . $exp->getMessage()
                        );
                        
                    }
                }
            }
            
            // Set coupon discount
            $new_order->setDiscounts();
            
            if (count($errors) > 0) {
                return redirect('admin/orders')->withErrors($errors);
            }
        }
        
        return redirect('admin/orders');
    }
    
    public function getUpdate($field = null, $sid = null, $status = null)
    {
        $field_pattern = '/^[a-zA-Z0-9_\-]+$/';
        $text_pattern = '/^[a-zA-Z0-9 _\-]+$/';
        
        $rules = [
            'field' => 'required|in:status|regex:' . $field_pattern,
            'sid' => 'required|numeric',
            'status' => 'required|regex:' . $text_pattern
        ];
        
        $inputs = [
            'field' => $field,
            'sid' => $sid,
            'status' => $status
        ];
        
        $messages = [
            'field.in'   => trans('redminportal::messages.order_error_update_unsupported_field'),
            'field.required'   => trans('redminportal::messages.order_error_update_missing_field'),
            'field.regex'      => trans('redminportal::messages.error_remove_special_characters'),
            'status.required'   => trans('redminportal::messages.order_error_update_missing_status'),
            'status.regex'      => trans('redminportal::messages.error_remove_special_characters')
        ];

        $validation = Validator::make($inputs, $rules, $messages);
        
        if ($validation->fails()) {
            return redirect($this->pageRoute)->withErrors($validation);
        }
        
        // Only supports status for now
        if ($field == 'status') {
            $order = Order::find($sid);
            $order->payment_status = $status;
            $order->save();
        }
        
        return redirect()->back();
    }
}
