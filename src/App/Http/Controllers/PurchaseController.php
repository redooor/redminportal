<?php namespace Redooor\Redminportal\App\Http\Controllers;

use Redooor\Redminportal\App\Http\Traits\DeleterController;
use Redooor\Redminportal\App\Models\User;
use Redooor\Redminportal\App\Models\UserPricelist;
use Redooor\Redminportal\App\Models\Pricelist;
use Illuminate\Support\Facades\Input;

class PurchaseController extends Controller
{
    protected $model;
    protected $perpage;
    
    use DeleterController;
    
    public function __construct(UserPricelist $model)
    {
        $this->model = $model;
        $this->perpage = config('redminportal::pagination.size');
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
        $sid = Input::get('id');

        $rules = array(
            'pricelist_id'      => 'required|integer',
            'transaction_id'    => 'required',
            'payment_status'    => 'required',
            'paid'              => 'numeric',
            'email'             => 'required|email'
        );

        $validation = \Validator::make(Input::all(), $rules);

        $redirect_url = (isset($sid)) ? 'admin/purchases/edit/' . $sid : 'admin/purchases/create';
        
        if ($validation->fails()) {
            return redirect($redirect_url)->withErrors($validation)->withInput();
        }
        
        $pricelist_id   = Input::get('pricelist_id');
        $transaction_id = Input::get('transaction_id');
        $payment_status = Input::get('payment_status');
        $paid           = Input::get('paid');
        $email          = Input::get('email');

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
