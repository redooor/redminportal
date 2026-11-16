<?php namespace Redooor\Redminportal\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Redooor\Redminportal\App\Classes\ImagePath;
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
     * Delete a file or folder, resolving relative targets through the image configuration.
     * Targets outside the configured upload path are ignored.
     */
    public function deleteFiles($target)
    {
        $resolved = ImagePath::resolve($target);

        if ($resolved === null) {
            return;
        }

        // Absolute targets and stored paths under the upload directory must stay
        // inside the configured upload path. Other relative paths use the
        // public_path() fallback for legacy stored paths.
        $confined = ImagePath::isAbsolute($target) || ImagePath::isUnderUploadDir($target);

        if ($confined && ! ImagePath::isInsideUploadPath($resolved)) {
            return;
        }

        $this->deleteResolvedFiles($resolved);
    }

    /*
     * php delete function that deals with directories recursively
     */
    private function deleteResolvedFiles($target)
    {
        $target = rtrim($target, '/');

        if (is_link($target)) {
            @unlink($target);

            return;
        }

        if (is_dir($target)) {
            $files = glob($target . '/*', GLOB_MARK); //GLOB_MARK adds a slash to directories returned

            if ($files === false) {
                return;
            }

            foreach ($files as $file) {
                $this->deleteResolvedFiles($file);
            }

            @rmdir($target);

        } elseif (is_file($target)) {
            @unlink($target);
        }
    }
}
