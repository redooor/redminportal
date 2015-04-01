<?php namespace Redooor\Redminportal\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

/* Columns
 *
 * id               (increment)
 * name             (string, 255)
 * short_description (string, 255)
 * long_description (text, nullable)
 * active           (boolean, default true)
 * options          (text, nullable)
 * order            (integer, default 0)
 * category_id      (integer, unsigned)
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

    public function images()
    {
        return $this->morphMany('Redooor\Redminportal\App\Models\Image', 'imageable');
    }
    
    public function coupons()
    {
        return $this->belongsToMany('Redooor\Redminportal\App\Models\Coupon', 'coupon_category');
    }

    public function deleteAllImages()
    {
        $folder = 'assets/img/categories/';

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

    public static function getAllActiveOrdered()
    {
        return Category::where('active', '=', '1')->orderBy('order', 'desc')->orderBy('name')->get();
    }

    public function printCategory($showActive = false)
    {
        $html = "<a href='" . $this->id .
            "'><span class='glyphicon glyphicon-chevron-right'></span> " .
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
        // Remove all relationships
        $this->coupons()->detach();
        
        // Delete main category will delete all sub categories
        $this->categories()->delete();

        // Delete all images
        $this->deleteAllImages();

        return parent::delete();
    }
}
