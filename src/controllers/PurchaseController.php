<?php namespace Redooor\Redminportal;

class PurchaseController extends BaseController {

    protected $model;

    public function __construct(UserPricelist $module)
    {
        $this->model = $module;
    }

    public function getIndex()
    {
        $purchases = UserPricelist::orderBy('created_at', 'desc')->paginate(20);

        return \View::make('redminportal::purchases/view')->with('purchases', $purchases);
    }

}
