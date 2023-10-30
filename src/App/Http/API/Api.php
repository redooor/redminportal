<?php namespace Redooor\Redminportal\App\Http\API;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Api extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
