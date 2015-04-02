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

            $image->deleteFiles($parts['dirname']);

            // Delete image model
            $image->delete();
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
