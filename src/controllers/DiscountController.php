<?php namespace Redooor\Redminportal;

class DiscountController extends BaseController {

    /* 
     * Note to contributors: 
     * This controller will be removed from v0.2.0. 
     * Please use Coupon controller instead. 
     */
    
    public function getIndex()
    {
        $discounts = Discount::paginate(20);

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

        return \View::make('redminportal::discounts/view')
            ->with('discounts', $discounts)
            ->with('pricelists_select', $pricelists_select);
    }
    
    public function getCreate()
    {
        return \View::make('redminportal::pages/404');
    }
    
    public function getEdit($id = null)
    {
        return \View::make('redminportal::pages/404');
    }

    public function postStore()
    {
        $id = \Input::get('id');
        
        $rules = array(
            'pricelist_id'      => 'required',
            'code'              => 'required',
            'expiry_date'       => 'required',
            'percent'           => 'required|numeric|min:1|max:100'
        );

        $validation = \Validator::make(\Input::all(), $rules);

        if( $validation->passes() )
        {
            $pricelist_id     = \Input::get('pricelist_id');
            $code             = \Input::get('code');
            $expiry_date      = \Input::get('expiry_date');
            $percent          = \Input::get('percent');

            $pricelist = Pricelist::find($pricelist_id);

            // No such id
            if ($pricelist == null) {
                $errors = new \Illuminate\Support\MessageBag;
                $errors->add('deleteError', "The pricelist for discount may have been deleted. Please try again.");
                return \Redirect::to('admin/discounts')->withErrors($errors)->withInput();
            }

            $newDiscount = new Discount;
            $newDiscount->code = $code;
            $newDiscount->expiry_date = date_create_from_format('d/m/Y H:i:s', $expiry_date . ' 00:00:00');
            $newDiscount->percent = $percent;

            $pricelist->discounts()->save($newDiscount);

        }//if it validate
        else {
            return \Redirect::to('admin/discounts')->withErrors($validation)->withInput();
        }

        return \Redirect::to('admin/discounts');
    }

    public function getDelete($id)
    {
        $discount = Discount::find($id);

        // No such id
        if ($discount == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add('deleteError', "The discount may have been deleted.");
            return \Redirect::to('admin/discounts')->withErrors($errors)->withInput();
        }

        $discount->delete();

        return \Redirect::to('admin/discounts');
    }
    
}
