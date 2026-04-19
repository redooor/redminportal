<?php namespace Redooor\Redminportal\App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;

abstract class Controller extends BaseController
{
    protected $model;
    protected $perpage;
    protected $sortBy;
    protected $orderBy;
    protected $pageView;
    protected $pageRoute;
    protected $data; // For storing shared data across views
    protected $query;
    protected $guard;
}
