<?php namespace Redooor\Redminportal\App\Models;

use Illuminate\Database\Eloquent\Model;

/* Columns
 *
 * id           (increment)
 * path         (string, 320)
 * tagable_id   (integer)
 * tagable_type (string, 255)
 * created_at   (dateTime)
 * updated_at   (dateTime)
 *
 */

class Tag extends Model
{
    protected $table = 'tags';
    
    public function products()
    {
        return $this->morphedByMany('Redooor\Redminportal\App\Models\Product', 'taggable');
    }
    
    public function medias()
    {
        return $this->morphedByMany('Redooor\Redminportal\App\Models\Media', 'taggable');
    }
    
    public function modules()
    {
        return $this->morphedByMany('Redooor\Redminportal\App\Models\Module', 'taggable');
    }
    
    public function pages()
    {
        return $this->morphedByMany('Redooor\Redminportal\App\Models\Page', 'taggable');
    }
    
    public function posts()
    {
        return $this->morphedByMany('Redooor\Redminportal\App\Models\Post', 'taggable');
    }
    
    public static function addTag($model, $name)
    {
        if ($model == null || empty($name)) {
            return false;
        }
        
        $checkTag = Tag::where('name', $name)->first();
        if ($checkTag) {
            $model->tags()->attach($checkTag);
        } else {
            $newTag = new Tag;
            $name_trimmed = trim($name); // Trim space from beginning and end
            $newTag->name = strtolower($name_trimmed);
            $model->tags()->save($newTag);
        }
        
        return true;
    }
}
