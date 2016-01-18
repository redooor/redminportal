<?php namespace Redooor\Redminportal\App\Http\API;

use Illuminate\Foundation\Bus\DispatchesCommands;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;

abstract class Api extends BaseController
{
    use DispatchesCommands, ValidatesRequests;
}
