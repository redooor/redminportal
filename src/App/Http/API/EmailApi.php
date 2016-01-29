<?php namespace Redooor\Redminportal\App\Http\API;

use Redooor\Redminportal\App\Models\User;

class EmailApi extends Api
{
    public function getIndex()
    {
        $list = array();
        return response()->json($list);
    }
    
    public function getAll()
    {
        $list = User::lists('email');
        return response()->json($list);
    }
}
