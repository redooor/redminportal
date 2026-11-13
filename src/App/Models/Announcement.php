<?php namespace Redooor\Redminportal\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Redooor\Redminportal\App\Helpers\RHelper;
use Redooor\Redminportal\App\Models\Traits\HasImages;

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
    use HasImages;

    protected $table = 'announcements';
    
    
    public static function validate($input)
    {
        $rules = array(
            'title' => 'required|regex:/^[a-z,0-9 ._\(\)-?]+$/i',
            'image'             => 'mimes:jpg,jpeg,png,gif|max:500',
            'images'            => 'array',
            'images.*'          => 'mimes:jpg,jpeg,png,gif|max:500',
            'content'           => 'required'
        );

        return Validator::make($input, $rules);
    }
    
    public function delete()
    {
        // Delete all images
        foreach ($this->images as $image) {
            $image->delete();
        }
        
        // Delete assets images folder
        $upload_dir = Config::get('redminportal::image.upload_dir');
        $deleteFolder = new Image;
        $url_path = RHelper::joinPaths($upload_dir, $this->table, $this->id);
        $deleteFolder->deleteFiles($url_path);
        
        return parent::delete();
    }
}
