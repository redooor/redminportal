<?php namespace Redooor\Redminportal\App\Http\API;

use Redooor\Redminportal\App\Models\Tag;

class TagApi extends Api
{
    public function getIndex()
    {
        $list = Tag::orderBy('id')->pluck('name', 'id');
        return response()->json($list);
    }
    
    public function getName()
    {
        $list = Tag::orderBy('name')->pluck('name');
        return response()->json($list);
    }
}
