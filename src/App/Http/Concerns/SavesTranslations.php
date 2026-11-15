<?php namespace Redooor\Redminportal\App\Http\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use Redooor\Redminportal\App\Models\Translation;

/*
 * Internal helper for saving translated content. Not an extension API.
 */
trait SavesTranslations
{
    /**
     * Save translations from request input for the given fields
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param array $fields
     * @param callable|null $transform
     * @return void
     */
    private function saveTranslationsFromInput(Model $model, array $fields, ?callable $transform = null): void
    {
        $translations = Config::get('redminportal::translation');

        foreach ($translations as $translation) {
            $lang = $translation['lang'];
            if ($lang == 'en') {
                continue;
            }

            $translated_content = array();
            foreach ($fields as $field) {
                $value = Request::input($lang . '_' . $field);

                if ($transform != null) {
                    $value = $transform($field, $value);
                }

                $translated_content[$field] = $value;
            }

            // Check if lang exist
            $translated_model = $model->translations->where('lang', $lang)->first();
            if ($translated_model == null) {
                $translated_model = new Translation;
            }

            $translated_model->lang = $lang;
            $translated_model->content = json_encode($translated_content);

            $model->translations()->save($translated_model);
        }
    }
}
