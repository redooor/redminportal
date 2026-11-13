<?php namespace Redooor\Redminportal\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Redooor\Redminportal\App\Helpers\RHelper;
use Redooor\Redminportal\App\Models\Traits\HasImages;

/* Columns
 *
 * id               (increment)
 * name             (string, 255)
 * short_description (string, 255)
 * long_description (text, nullable)
 * active           (boolean, default true)
 * options          (text, nullable)
 * category_id      (integer, unsigned)
 * created_at       (dateTime)
 * updated_at       (dateTime)
 *
 */

class Portfolio extends Model
{
    use HasImages;

    protected $table = 'portfolios';
    
    public function category()
    {
        return $this->belongsTo('Redooor\Redminportal\App\Models\Category');
    }
    
    
    public function translations()
    {
        return $this->morphMany('Redooor\Redminportal\App\Models\Translation', 'translatable');
    }
    
    public function delete()
    {
        // Delete all images
        foreach ($this->images as $image) {
            $image->delete();
        }
        
        // Delete all translations
        $this->translations()->delete();
        
        // Delete asset images folder
        $upload_dir = Config::get('redminportal::image.upload_dir');
        $deleteFolder = new Image;
        $url_path = RHelper::joinPaths($upload_dir, $this->table, $this->id);
        $deleteFolder->deleteFiles($url_path);
        
        return parent::delete();
    }
}
