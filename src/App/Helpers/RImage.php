<?php namespace Redooor\Redminportal\App\Helpers;

use Redooor\Redminportal\App\Classes\Imagine;

/**
 * Deprecated.
 *
 * Moved to Redooor\Redminportal\App\Classes\Imagine
 * Leaving it here for backward compatibility
 * To be removed in future release
 **/
class RImage extends Imagine
{
    /**
     * Initialize the image service
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }
}
