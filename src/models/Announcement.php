<?php namespace Redooor\Redminportal;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

class Announcement extends Model {

    public function images()
    {
        return $this->morphMany('Redooor\Redminportal\Image', 'imageable');
    }

    public function deleteAllImages()
    {
        foreach ($this->images as $image)
        {
            // Delete physical file, including all different sizes
            $parts = pathinfo($image->path);

            $this->delete_files($parts['dirname']);

            // Delete image model
            $image->delete();
        }
    }

    public function deleteImageFolder($dir)
    {
        $this->delete_files($dir);
    }

    /*
     * php delete function that deals with directories recursively
     */
    private function delete_files($target)
    {
        if(is_dir($target)){
            $files = glob( $target . '*', GLOB_MARK ); //GLOB_MARK adds a slash to directories returned

            foreach( $files as $file )
            {
                $this->delete_files( $file );
            }

            if (is_dir($target)) {
                rmdir( $target );
            }

        } elseif(is_file($target)) {
            unlink( $target );
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
}
