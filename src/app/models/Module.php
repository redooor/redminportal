<?php namespace Redooor\Redminportal\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

/* Columns
 *
 * id               (increment)
 * name             (string, 255)
 * sku              (string, 255, unique, nullable)
 * short_description (string, 255)
 * long_description (text, nullable)
 * featured         (boolean, default false)
 * active           (boolean, default true)
 * options          (text, nullable)
 * category_id      (integer, unsigned)
 * created_at       (dateTime)
 * updated_at       (dateTime)
 *
 */

class Module extends Model
{
    protected $table = 'modules';
    
    public function category()
    {
        return $this->belongsTo('Redooor\Redminportal\App\Models\Category');
    }

    public function pricelists()
    {
        return $this->hasMany('Redooor\Redminportal\App\Models\Pricelist');
    }

    public function images()
    {
        return $this->morphMany('Redooor\Redminportal\App\Models\Image', 'imageable');
    }

    public function tags()
    {
        return $this->morphMany('Redooor\Redminportal\App\Models\Tag', 'tagable');
    }
    
    public function memberships()
    {
        return $this->belongsToMany(
            'Redooor\Redminportal\App\Models\Membership',
            'module_media_memberships',
            'module_id',
            'membership_id'
        );
    }
    
    public function medias()
    {
        return $this->belongsToMany(
            'Redooor\Redminportal\App\Models\Media',
            'module_media_memberships',
            'module_id',
            'media_id'
        );
    }

    public function deleteAllImages()
    {
        $folder = 'assets/img/modules/';

        foreach ($this->images as $image) {
            // Delete physical file
            $filepath = $folder . $image->path;

            if (File::exists($filepath)) {
                File::delete($filepath);
            }

            // Delete image model
            $image->delete();
        }
    }

    public function deleteAllTags()
    {
        $this->tags()->delete();
    }
    
    public function delete()
    {
        $this->tags()->delete();
        $this->deleteAllImages();
        $this->memberships()->detach();
        $this->medias()->detach();
        $this->pricelists()->delete();
        
        return parent::delete();
    }
}
