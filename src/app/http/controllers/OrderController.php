<?php namespace Redooor\Redminportal\App\Http\Controllers;

use Redooor\Redminportal\App\Models\User;
use Redooor\Redminportal\App\Models\Order;
use Redooor\Redminportal\App\Models\Product;
use Redooor\Redminportal\App\Models\Bundle;
use Redooor\Redminportal\App\Models\Coupon;

class OrderController extends Controller
{
    public function getIndex()
    {
        $orders = Order::orderBy('created_at', 'desc')->paginate(20);

        return view('redminportal::orders/view')->with('orders', $orders);
    }
    
    public function getEmails()
    {
        $emails = User::lists('email');

        return \Response::json($emails);
    }

    public function getCreate()
    {
        $products = Product::orderBy('name')->lists('name', 'id');
        $bundles = Bundle::orderBy('name')->lists('name', 'id');
        $coupons = Coupon::orderBy('code')->lists('code', 'id');

        $payment_statuses = array(
            'Completed'     => 'Completed',
            'Pending'       => 'Pending',
            'In Progress'   => 'In Progress',
            'Canceled'      => 'Canceled',
            'Refunded'      => 'Refunded'
        );

        return view('redminportal::orders/create')
            ->with('products', $products)
            ->with('bundles', $bundles)
            ->with('coupons', $coupons)
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
            'product_id'        => 'required_without:bundle_id',
            'bundle_id'         => 'required_without:product_id',
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
        // Save coupons to order
        $coupons = \Input::get('coupon_id');
        if (count($coupons) > 0) {
            foreach ($coupons as $item) {
                $model = Coupon::find($item);
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
        
        // Save the products
        foreach ($apply_to_models as $apply_to_model) {
            $apply_to_model->orders()->save($new_order);
        }
        
        return redirect('admin/orders');
    }
    
    public function getDelete($sid)
    {
        $order = Order::find($sid);
        
        if ($order == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add('userError', "The order record may have already been deleted.");
            return redirect('admin/orders')->withErrors($errors);
        }
        
        $order->delete();
        
        return redirect('admin/orders');
    }
}
