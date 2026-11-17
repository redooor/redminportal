<?php namespace Redooor\Redminportal\App\Http\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;
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
        // A missing tags key means the form does not manage tags, so keep them.
        if (! Request::has('tags')) {
            return;
        }

        // Delete old tags. An empty value means the tags were cleared.
        $model->tags()->detach();

        // Save tags
        if (! empty($tags)) {
            foreach (explode(',', $tags) as $tagName) {
                Tag::addTag($model, $tagName);
            }
        }
    }
}
