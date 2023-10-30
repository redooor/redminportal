<?php namespace Redooor\Redminportal\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Redooor\Redminportal\App\Models\Image;
use Redooor\Redminportal\App\Helpers\RHelper;

/* Columns
 *
 * id               (increment)
 * name             (string, 255)
 * short_description (string, 255)
 * long_description (text, nullable)
 * active           (boolean, default true)
 * options          (text, nullable)
 * order            (integer, default 0)
 * category_id      (integer, unsigned, nullable)
 * created_at       (dateTime)
 * updated_at       (dateTime)
 *
 */

class Category extends Model
{
    protected $table = 'categories';
    
    public function parentCategory()
    {
        return $this->belongsTo('Redooor\Redminportal\App\Models\Category', 'category_id');
    }

    public function categories()
    {
        return $this->hasMany('Redooor\Redminportal\App\Models\Category');
    }

    public function products()
    {
        return $this->hasMany('Redooor\Redminportal\App\Models\Product');
    }

    public function portfolios()
    {
        return $this->hasMany('Redooor\Redminportal\App\Models\Portfolio');
    }
    
    public function medias()
    {
        return $this->hasMany('Redooor\Redminportal\App\Models\Media');
    }
    
    public function modules()
    {
        return $this->hasMany('Redooor\Redminportal\App\Models\Module');
    }
    
    public function pages()
    {
        return $this->hasMany('Redooor\Redminportal\App\Models\Page');
    }
    
    public function posts()
    {
        return $this->hasMany('Redooor\Redminportal\App\Models\Post');
    }
    
    public function bundles()
    {
        return $this->hasMany('Redooor\Redminportal\App\Models\Bundle');
    }

    public function images()
    {
        return $this->morphMany('Redooor\Redminportal\App\Models\Image', 'imageable');
    }
    
    public function translations()
    {
        return $this->morphMany('Redooor\Redminportal\App\Models\Translation', 'translatable');
    }
    
    public function coupons()
    {
        return $this->belongsToMany('Redooor\Redminportal\App\Models\Coupon', 'coupon_category');
    }

    public static function getAllActiveOrdered()
    {
        return Category::where('active', '=', '1')->orderBy('order', 'desc')->orderBy('name')->get();
    }

    public function printCategory($showActive = false)
    {
        $html = "<a href='" . $this->id .
            "'><span class='glyphicon glyphicon-menu-right'></span> " .
            $this->name . "</a>";
        if ($this->categories->count() > 0) {
            $html .= "<ul>";
            if ($showActive) {
                $categories = $this->where('category_id', $this->id)
                    ->orderBy('order', 'desc')
                    ->orderBy('name')
                    ->get();
            } else {
                $categories = $this->where('category_id', $this->id)
                    ->where('active', true)
                    ->orderBy('order', 'desc')
                    ->orderBy('name')
                    ->get();
            }
            foreach ($categories as $cat) {
                $html .= "<li>";
                $html .= $cat->printCategory();
                $html .= "</li>";
            }
            $html .= "</ul>";
        }

        return $html;
    }

    public function delete()
    {
        // Delete main category will delete all sub categories
        $this->categories()->delete();
        
        // Delete all images
        foreach ($this->images as $image) {
            $image->delete();
        }
        
        // Remove all relationships
        $this->bundles()->delete();
        $this->coupons()->detach();
        $this->medias()->delete();
        $this->modules()->delete();
        $this->pages()->delete();
        $this->portfolios()->delete();
        $this->posts()->delete();
        $this->products()->delete();
        $this->translations()->delete();
        
        // Delete category's images folder
        $upload_dir = Config::get('redminportal::image.upload_dir');
        $deleteFolder = new Image;
        $url_path = RHelper::joinPaths($upload_dir, $this->table, $this->id);
        $deleteFolder->deleteFiles($url_path);

        return parent::delete();
    }
}
