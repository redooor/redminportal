<?php namespace Redooor\Redminportal\App\Http\Controllers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Routing\Controller as BaseController;
use Redooor\Redminportal\App\Models\Image;

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

    /**
     * Find an image only when it belongs to the given model class.
     *
     * @param integer $sid Image id
     * @param string $modelClass
     * @return Image|null
     */
    protected function findOwnedImage($sid, $modelClass)
    {
        $image = Image::find($sid);

        if ($image == null) {
            return null;
        }

        $morphAlias = Relation::getMorphAlias($modelClass);

        if ($image->imageable_type !== $modelClass && $image->imageable_type !== $morphAlias) {
            return null;
        }

        return $image;
    }
}
