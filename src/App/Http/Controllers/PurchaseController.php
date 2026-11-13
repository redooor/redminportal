<?php namespace Redooor\Redminportal\App\Http\Controllers;

use Redooor\Redminportal\App\Http\Traits\DeleterController;
use Redooor\Redminportal\App\Models\User;
use Redooor\Redminportal\App\Models\UserPricelist;
use Redooor\Redminportal\App\Models\Pricelist;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;

class PurchaseController extends Controller
{
    protected $model;
    protected $perpage;
    
    use DeleterController {
        getDelete as protected deletePurchase;
    }
    
    public function __construct(UserPricelist $model)
    {
        $this->model = $model;
        $this->perpage = config('redminportal::pagination.size');
    }

    /**
     * Delete a purchase only when the user may also delete orders.
     */
    public function getDelete($sid)
    {
        $user = Auth::guard('redminguard')->user();

        if ($user == null || ! $user->hasAccess(Request::create('admin/orders/delete/' . $sid, 'POST'))) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add('deleteError', trans('redminportal::messages.purchase_error_delete_no_orders_permission'));
            return redirect()->back()->withErrors($errors);
        }

        return $this->deletePurchase($sid);
    }
    
    public function getIndex()
    {
        $purchases = UserPricelist::orderBy('created_at', 'desc')->paginate($this->perpage);
        
        return view('redminportal::purchases/view')->with('purchases', $purchases);
    }
    
    public function getCreate()
    {
        $pricelists_select = array();

        $pricelists_all = Pricelist::join('modules', 'modules.id', '=', 'pricelists.module_id')
            ->join('memberships', 'memberships.id', '=', 'pricelists.membership_id')
            ->orderBy('modules.name')
            ->orderBy('memberships.rank', 'desc')
            ->select('pricelists.*')
            ->get();

        foreach ($pricelists_all as $pricelist) {
            $pricelists_select[$pricelist->id] =
            $pricelist->module->name . " (" . $pricelist->membership->name . ")";
        }

        $payment_statuses = array(
            'Completed'     => 'Completed',
            'Pending'       => 'Pending',
            'In Progress'   => 'In Progress',
            'Canceled'      => 'Canceled',
            'Refunded'      => 'Refunded'
        );

        return view('redminportal::purchases/create')
            ->with('pricelists_select', $pricelists_select)
            ->with('payment_statuses', $payment_statuses);
    }
    
    public function getEdit($sid = null)
    {
        $sid = null;
        $errors = new \Illuminate\Support\MessageBag;
        $errors->add(
            'editError',
            "The edit function has been disabled for all purchases."
        );
        return redirect('/admin/purchases')->withErrors($errors);
    }

    public function postStore()
    {
        // Purchases are rows of the orders table, so adding one also needs permission to add an order
        $user = Auth::guard('redminguard')->user();

        if ($user == null || ! $user->hasAccess(Request::create('admin/orders/store', 'POST'))) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add('createError', trans('redminportal::messages.purchase_error_create_no_orders_permission'));
            return redirect()->back()->withErrors($errors)->withInput();
        }

        $sid = Request::input('id');

        $rules = array(
            'pricelist_id'      => 'required|integer',
            'transaction_id'    => 'required',
            'payment_status'    => 'required',
            'paid'              => 'numeric',
            'email'             => 'required|email'
        );

        $validation = Validator::make(Request::all(), $rules);

        $redirect_url = (isset($sid)) ? 'admin/purchases/edit/' . $sid : 'admin/purchases/create';
        
        if ($validation->fails()) {
            return redirect($redirect_url)->withErrors($validation)->withInput();
        }
        
        $pricelist_id   = Request::input('pricelist_id');
        $transaction_id = Request::input('transaction_id');
        $payment_status = Request::input('payment_status');
        $paid           = Request::input('paid');
        $email          = Request::input('email');

        $pricelist = Pricelist::find($pricelist_id);

        // No such pricelist
        if ($pricelist == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add('pricelistError', "The Module/Membership may have been deleted. Please try again.");
            return redirect($redirect_url)->withErrors($errors)->withInput();
        }
        
        $user = User::where('email', $email)->first();
        
        if ($user == null) {
            // No such user
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add('userError', "The user may have been deleted. Please try again.");
            return redirect($redirect_url)->withErrors($errors)->withInput();
        }

        // Check if user_pricelist already exist
        $existing = UserPricelist::join('order_pricelist', 'orders.id', '=', 'order_pricelist.id')
            ->where('orders.user_id', $user->id)
            ->where('order_pricelist.pricelist_id', $pricelist->id)
            ->count();
        if ($existing > 0) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add(
                'userpricelistError',
                $email . " has already purchased " .
                $pricelist->module->name . " (" .
                $pricelist->membership->name . ")."
            );
            return redirect($redirect_url)->withErrors($errors)->withInput();
        }

        $new_purchase = new UserPricelist;
        $new_purchase->user_id = $user->id;
        $new_purchase->pricelist_id = $pricelist->id;
        $new_purchase->paid = $paid;
        $new_purchase->transaction_id = $transaction_id;
        $new_purchase->payment_status = $payment_status;
        $new_purchase->save();
        
        return redirect('admin/purchases');
    }
}
