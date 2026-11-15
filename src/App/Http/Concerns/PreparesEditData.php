<?php namespace Redooor\Redminportal\App\Http\Concerns;

use Illuminate\Database\Eloquent\Model;

/*
 * Internal helper for preparing edit data. Not an extension API.
 */
trait PreparesEditData
{
    /**
     * Decoded translation content keyed by language
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return array
     */
    private function translatedContentFor(Model $model): array
    {
        $translated = array();
        foreach ($model->translations as $translation) {
            $translated[$translation->lang] = json_decode($translation->content);
        }

        return $translated;
    }

    /**
     * Tag names joined by comma in the model's tag order
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return string
     */
    private function tagStringFor(Model $model): string
    {
        $tagString = "";
        foreach ($model->tags as $tag) {
            if (! empty($tagString)) {
                $tagString .= ",";
            }

            $tagString .= $tag->name;
        }

        return $tagString;
    }
}
