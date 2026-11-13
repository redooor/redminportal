<?php namespace Redooor\Redminportal\App\Classes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Redooor\Redminportal\App\Models\Image;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/*
 * Attach uploaded images to a model's polymorphic image relation.
 */
class ImageService
{
    /**
     * Upload files and attach them to the given model
     *
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @param  \Symfony\Component\HttpFoundation\File\UploadedFile|array|null $files
     * @param  string $folder
     * @param  bool $createDimensions
     * @return \Illuminate\Support\Collection
     */
    public function attach(Model $model, $files, string $folder, bool $createDimensions = true): Collection
    {
        $images = new Collection;

        if ($files === null) {
            return $images;
        }

        if (! is_array($files)) {
            $files = array($files);
        }

        foreach ($files as $file) {
            if (! ($file instanceof UploadedFile)) {
                continue;
            }

            $path = (new Imagine)->upload($file, $folder . '/' . $model->getKey(), $createDimensions);

            if (! $path) {
                continue;
            }

            $image = new Image;
            $image->path = $path;
            $model->images()->save($image);

            $images->push($image);
        }

        return $images;
    }
}
