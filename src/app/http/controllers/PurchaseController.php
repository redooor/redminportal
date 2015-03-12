<?php namespace Redooor\Redminportal;

class PurchaseController extends BaseController {

    public function getIndex()
    {
        $purchases = UserPricelist::orderBy('created_at', 'desc')->paginate(20);

        return \View::make('redminportal::purchases/view')->with('purchases', $purchases);
    }
    
    public function getEmails()
    {
        $emails = \DB::table('users')->lists('email');

        return \Response::json($emails);
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

        return \View::make('redminportal::purchases/create')
            ->with('pricelists_select', $pricelists_select)
            ->with('payment_statuses', $payment_statuses);
    }
    
    public function getEdit($id = null) {
        return \View::make('redminportal::pages/404');
    }

    public function postStore()
    {
        $id = \Input::get('id');

        $rules = array(
            'pricelist_id'      => 'required|integer',
            'transaction_id'    => 'required',
            'payment_status'    => 'required',
            'paid'              => 'numeric',
            'email'             => 'required|email'
        );

        $validation = \Validator::make(\Input::all(), $rules);

        $redirect_url = (isset($id)) ? 'admin/purchases/edit/' . $id : 'admin/purchases/create';

        if( $validation->passes() )
        {
            $pricelist_id   = \Input::get('pricelist_id');
            $transaction_id = \Input::get('transaction_id');
            $payment_status = \Input::get('payment_status');
            $paid           = \Input::get('paid');
            $email          = \Input::get('email');

            $pricelist = Pricelist::find($pricelist_id);

            // No such pricelist
            if ($pricelist == null) {
                $errors = new \Illuminate\Support\MessageBag;
                $errors->add('pricelistError', "The Module/Membership may have been deleted. Please try again.");
                return \Redirect::to($redirect_url)->withErrors($errors)->withInput();
            }
            
            try {
                $user = \Sentry::findUserByLogin($email);
            } catch (\Exception $e) {
                // No such user
                $errors = new \Illuminate\Support\MessageBag;
                $errors->add('userError', "The user may have been deleted. Please try again.");
                return \Redirect::to($redirect_url)->withErrors($errors)->withInput();
            }
            
            // Check if user_pricelist already exist
            $existing = UserPricelist::where('user_id', $user->id)->where('pricelist_id', $pricelist->id)->get();
            if (count($existing) > 0) {
                $errors = new \Illuminate\Support\MessageBag;
                $errors->add('userpricelistError', $email . " has already purchased " . $pricelist->module->name . " (" . $pricelist->membership->name . ").");
                return \Redirect::to($redirect_url)->withErrors($errors)->withInput();
            }

            $new_purchase = new UserPricelist;
            $new_purchase->user_id = $user->id;
            $new_purchase->pricelist_id = $pricelist->id;
            $new_purchase->paid = $paid;
            $new_purchase->transaction_id = $transaction_id;
            $new_purchase->payment_status = $payment_status;
            $new_purchase->save();

        }//if it validate
        else {
            return \Redirect::to($redirect_url)->withErrors($validation)->withInput();
        }

        return \Redirect::to('admin/purchases');
    }
    
    public function getDelete($id)
    {
        $purchase = UserPricelist::find($id);
        
        if ($purchase == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add('userError', "The purchase record may have already been deleted.");
            return \Redirect::to('admin/purchases')->withErrors($errors);
        }
        
        $purchase->delete();
        
        return \Redirect::to('admin/purchases');
    }

}
