<?php namespace Redooor\Redminportal\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Redooor\Redminportal\App\Classes\ImageService;
use Redooor\Redminportal\App\Helpers\RImage;

/* Columns
 *
 * id           (increment)
 * path         (string, 320)
 * sort_order   (unsigned integer, default 0)
 * is_primary   (boolean, default false)
 * imageable_id (integer)
 * imageable_type (string, 255)
 * created_at   (dateTime)
 * updated_at   (dateTime)
 *
 */

class Image extends Model
{
    protected $table = 'images';
    
    public function imageable()
    {
        return $this->morphTo();
    }
    
    public function delete()
    {
        return (new ImageService)->remove($this);
    }

    /**
     * Delete the database record while keeping the Eloquent model events
     *
     * @return bool|null
     */
    public function deleteRecord()
    {
        return parent::delete();
    }
    
    /*
     * php delete function that deals with directories recursively
     */
    public function deleteFiles($target)
    {
        if (is_dir($target)) {
            $files = glob($target . '*', GLOB_MARK); //GLOB_MARK adds a slash to directories returned

            foreach ($files as $file) {
                $this->deleteFiles($file);
            }

            if (is_dir($target)) {
                rmdir($target);
            }

        } elseif (is_file($target)) {
            unlink($target);
        }
    }
}
