<?php namespace Redooor\Redminportal;

class PricelistController extends BaseController {

    public function getIndex()
    {
        $pricelists = Pricelist::paginate(20);

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

        return \View::make('redminportal::pricelists/discount')
            ->with('pricelists', $pricelists)
            ->with('pricelists_select', $pricelists_select);
    }

    public function postDiscount()
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
                return \Redirect::to('admin/pricelists')->withErrors($errors)->withInput();
            }

            $newDiscount = new Discount;
            $newDiscount->code = $code;
            $newDiscount->expiry_date = date_create_from_format('d/m/Y H:i:s', $expiry_date . ' 00:00:00');
            $newDiscount->percent = $percent;

            $pricelist->discounts()->save($newDiscount);

        }//if it validate
        else {
            return \Redirect::to('admin/pricelists')->withErrors($validation)->withInput();
        }

        return \Redirect::to('admin/pricelists');
    }

    public function getDeletediscount($id)
    {
        $discount = Discount::find($id);

        // No such id
        if ($discount == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add('deleteError', "The discount may have been deleted.");
            return \Redirect::to('admin/pricelists')->withErrors($errors)->withInput();
        }

        $discount->delete();

        return \Redirect::to('admin/pricelists');
    }
}
