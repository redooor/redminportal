<?php namespace Redooor\Redminportal\App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
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
    
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

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

        $morphClass = (new $modelClass)->getMorphClass();

        if ($image->imageable_type !== $modelClass && $image->imageable_type !== $morphClass) {
            return null;
        }

        return $image;
    }
}
