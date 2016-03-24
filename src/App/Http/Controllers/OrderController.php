<?php namespace Redooor\Redminportal\App\Http\Controllers;

use Input;
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
        $redirect_url = 'admin/orders/create';
        
        $validation = $this->validateStoreInputs(Input::all());
        
        if ($validation->fails()) {
            return redirect($redirect_url)->withErrors($validation)->withInput();
        }
        
        $transaction_id = Input::get('transaction_id');
        $payment_status = Input::get('payment_status');
        $paid           = Input::get('paid');
        $email          = Input::get('email');
        
        // Check user first
        $user = User::where('email', $email)->first();
        
        if ($user == null) {
            // No such user
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add('userError', "The user may have been deleted. Please try again.");
            return redirect($redirect_url)->withErrors($errors)->withInput();
        }
        
        $apply_to_models = array();
        
        // Save products to order
        $selected_products = Input::get('selected_products');
        $apply_to_models = array_merge($apply_to_models, $this->addModelToArray($selected_products, new Product));
        
        // Save bundles to order
        $selected_bundles = Input::get('selected_bundles');
        $apply_to_models = array_merge($apply_to_models, $this->addModelToArray($selected_bundles, new Bundle));
        
        // Save pricelists to order
        $pricelists = Input::get('pricelist_id');
        $apply_to_models = array_merge($apply_to_models, $this->addModelToArraySimpleMode($pricelists, new Pricelist));
        
        // No product/bundle to add
        if (count($apply_to_models) == 0) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add('productError', "The items selected may have been deleted. Please try again.");
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
        $coupons = Input::get('coupon_id');
        $errors = $this->addCouponToOrder($coupons, $new_order);
        
        if ($errors) {
            return redirect($this->pageRoute)->withErrors($errors);
        }
        
        return redirect($this->pageRoute);
    }
    
    /**
     * Add model to array, content is JSON string
     *
     * @param array $models
     * @param object $model Model
     *
     * @return array
     **/
    private function addModelToArray($models, $model)
    {
        $apply_to_models = array();
        
        if (count($models) > 0) {
            foreach ($models as $item_json) {
                $item = json_decode($item_json);
                $object = $model->find($item->id);
                if ($object != null) {
                    for ($count = 1; $count <= $item->quantity; $count++) {
                        $apply_to_models[] = $object;
                    }
                }
            }
        }
        
        return $apply_to_models;
    }
    
    /**
     * Add model to array, simple mode, content is NOT JSON string
     *
     * @param array $models
     * @param object $model Model
     *
     * @return array
     **/
    private function addModelToArraySimpleMode($models, $model)
    {
        $apply_to_models = array();
        
        if (count($models) > 0) {
            foreach ($models as $item) {
                $object = $model->find($item);
                if ($object != null) {
                    $apply_to_models[] = $object;
                }
            }
        }
        
        return $apply_to_models;
    }
    
    /**
     * Add coupons to order
     *
     * @param array $coupons
     * @param object $new_order Order
     *
     * @return array
     **/
    private function addCouponToOrder($coupons, $new_order)
    {
        $errors = new \Illuminate\Support\MessageBag;
        
        if (count($coupons) > 0) {
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
        }
        
        return $errors;
    }
    
    /**
     * Validates inputs before storing data
     *
     * @param array $inputs
     *
     * @return object Validator
     **/
    private function validateStoreInputs($inputs)
    {
        $rules = array(
            'selected_products' => 'required_without_all:selected_bundles,pricelist_id',
            'selected_bundles'  => 'required_without_all:selected_products,pricelist_id',
            'pricelist_id'      => 'required_without_all:selected_products,selected_bundles',
            'transaction_id'    => 'required',
            'payment_status'    => 'required',
            'paid'              => 'numeric',
            'email'             => 'required|email'
        );
        
        $messages = [
            'selected_products.required_without_all' =>
                trans('redminportal::messages.order_error_selected_products_required_without_all'),
            'selected_bundles.required_without_all' =>
                trans('redminportal::messages.order_error_selected_bundles_required_without_all'),
            'pricelist_id.required_without_all' =>
                trans('redminportal::messages.order_error_pricelist_id_required_without_all')
        ];

        return Validator::make($inputs, $rules, $messages);
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
