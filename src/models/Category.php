<?php namespace Redooor\Redminportal;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

class Category extends Model {

    public function category()
    {
        return $this->belongsTo('Redooor\Redminportal\Category');
    }

    public function products()
    {
        return $this->hasMany('Redooor\Redminportal\Product');
    }

    public function portfolios()
    {
        return $this->hasMany('Redooor\Redminportal\Portfolio');
    }

    public function images()
    {
        return $this->morphMany('Redooor\Redminportal\Image', 'imageable');
    }

    public function deleteAllImages()
    {
        $folder = 'assets/img/categories/';

        foreach ($this->images as $image)
        {
            // Delete physical file
            $filepath = $folder . $image->path;

            if( File::exists($filepath) ) {
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

    public static function printCategory($id)
    {
        $category = Category::find($id);

        if ($category != null) {
            echo "<ul>";
            echo "<li><a href='" . $category->id . "'>" . $category->name . "</a>";
            foreach(Category::where('category_id', $id)->where('active', true)->get() as $cat) {
                Category::printCategory($cat->id);
            }
            echo "</li>";
            echo "</ul>";
        }
    }

}
