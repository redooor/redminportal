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
        if ($model == null || $name === null) {
            return false;
        }

        $normalised = strtolower(trim($name));

        if (empty($normalised)) {
            return false;
        }

        $checkTag = Tag::where('name', $normalised)->first();
        if ($checkTag) {
            $model->tags()->attach($checkTag);
        } else {
            $newTag = new Tag;
            $newTag->name = $normalised;
            $model->tags()->save($newTag);
        }

        return true;
    }
}
