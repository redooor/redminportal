<?php namespace Redooor\Redminportal\App\Models;

use Illuminate\Database\Eloquent\Model;
use Redooor\Redminportal\App\Helpers\RHelper;

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
        return $this->morphToMany('Redooor\Redminportal\App\Models\Tag', 'taggable');
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
    
    public function delete()
    {
        // Remove all relationships
        $this->memberships()->detach();
        $this->medias()->detach();
        $this->pricelists()->delete();
        
        // Delete all media links
        foreach (ModuleMediaMembership::where('module_id', $this->id)->get() as $mmm) {
            $mmm->delete();
        }
        
        // Remove all relationships
        $this->tags()->detach();
        
        // Delete all images
        foreach ($this->images as $image) {
            $image->delete();
        }
        
        // Delete asset images folder
        $upload_dir = \Config::get('redminportal::image.upload_dir');
        $deleteFolder = new Image;
        $url_path = RHelper::joinPaths($upload_dir, $this->table, $this->id);
        $deleteFolder->deleteFiles($url_path);
        
        return parent::delete();
    }
}
