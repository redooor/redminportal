<?php namespace Redooor\Redminportal\App\Models;

use Illuminate\Database\Eloquent\Model;

/* Columns
 *
 * id           (increment)
 * title        (string, 255)
 * content      (text)
 * private      (boolean, default true)
 * created_at   (dateTime)
 * updated_at   (dateTime)
 *
 */

class Announcement extends Model
{
    public function images()
    {
        return $this->morphMany('Redooor\Redminportal\App\Models\Image', 'imageable');
    }

    public function deleteAllImages()
    {
        foreach ($this->images as $image) {
            // Delete physical file, including all different sizes
            $parts = pathinfo($image->path);

            $this->deleteFiles($parts['dirname']);

            // Delete image model
            $image->delete();
        }
    }

    public function deleteImageFolder($dir)
    {
        $this->deleteFiles($dir);
    }

    /*
     * php delete function that deals with directories recursively
     */
    private function deleteFiles($target)
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

    public static function validate($input)
    {
        $rules = array(
            'title' => 'required|regex:/^[a-z,0-9 ._\(\)-?]+$/i',
            'image'             => 'mimes:jpg,jpeg,png,gif|max:500',
            'content'           => 'required'
        );

        return \Validator::make($input, $rules);
    }
    
    public function delete()
    {
        $this->deleteAllImages();
        return parent::delete();
    }
}
