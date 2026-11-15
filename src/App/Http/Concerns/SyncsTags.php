<?php namespace Redooor\Redminportal\App\Http\Concerns;

use Illuminate\Database\Eloquent\Model;
use Redooor\Redminportal\App\Models\Tag;

/*
 * Internal helper for syncing tags. Not an extension API.
 */
trait SyncsTags
{
    /**
     * Replace the model's tags with the comma separated input
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param mixed $tags
     * @return void
     */
    private function syncTagsFromInput(Model $model, $tags): void
    {
        if (! empty($tags)) {
            // Delete old tags
            $model->tags()->detach();

            // Save tags
            foreach (explode(',', $tags) as $tagName) {
                Tag::addTag($model, $tagName);
            }
        }
    }
}
