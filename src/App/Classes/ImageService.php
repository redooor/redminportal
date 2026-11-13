<?php namespace Redooor\Redminportal\App\Classes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request as RequestFacade;
use Redooor\Redminportal\App\Helpers\RImage;
use Redooor\Redminportal\App\Models\Image;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/*
 * Attach, order and remove images on a model's polymorphic image relation.
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

        $hadImages = $model->images()->exists();
        $sortOrder = ((int) $model->images()->max('sort_order')) + 1;
        $primarySet = false;

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
            $image->sort_order = $sortOrder++;
            $image->is_primary = (! $hadImages && ! $primarySet);

            if ($image->is_primary) {
                $primarySet = true;
            }

            $model->images()->save($image);

            $images->push($image);
        }

        return $images;
    }

    /**
     * Collect uploaded files from the image and images[] request fields
     *
     * @param  \Illuminate\Http\Request|null $request
     * @return array
     */
    public static function filesFrom(\Illuminate\Http\Request|null $request = null): array
    {
        $files = array();

        foreach (array('image', 'images') as $field) {
            $uploaded = ($request === null ? RequestFacade::file($field) : $request->file($field));

            if ($uploaded instanceof UploadedFile) {
                $files[] = $uploaded;
                continue;
            }

            if (is_array($uploaded)) {
                foreach ($uploaded as $file) {
                    if ($file instanceof UploadedFile) {
                        $files[] = $file;
                    }
                }
            }
        }

        return $files;
    }

    /**
     * Remove an image, its files and promote the next image when it was primary
     *
     * @param  \Redooor\Redminportal\App\Models\Image $image
     * @return void
     */
    public function remove(Image $image): void
    {
        $wasPrimary = (bool) $image->is_primary;
        $model = null;

        if ($wasPrimary) {
            try {
                $model = $image->imageable;
            } catch (\Throwable $e) {
                $model = null;
            }
        }

        $this->deleteImageFiles($image);

        Image::whereKey($image->getKey())->delete();
        $image->exists = false;

        if ($wasPrimary && $model) {
            $next = $model->images()->first();

            if ($next) {
                $next->is_primary = true;
                $next->save();
            }
        }
    }

    /**
     * Make the given image the only primary image of its model
     *
     * @param  \Redooor\Redminportal\App\Models\Image $image
     * @return void
     */
    public function makePrimary(Image $image): void
    {
        $model = $image->imageable;

        if (! $model) {
            return;
        }

        $model->images()
            ->where('id', '!=', $image->getKey())
            ->update(['is_primary' => false]);

        $image->is_primary = true;
        $image->save();
    }

    /**
     * Set the image order from the given ids that belong to the model
     *
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @param  array $imageIds
     * @return void
     */
    public function reorder(Model $model, array $imageIds): void
    {
        $images = $model->images()->whereIn('id', $imageIds)->get()->keyBy('id');
        $sortOrder = 1;

        foreach ($imageIds as $imageId) {
            if (! isset($images[$imageId])) {
                continue;
            }

            $image = $images[$imageId];
            $image->sort_order = $sortOrder++;
            $image->save();
        }
    }

    /**
     * Delete the original image file and all of its dimension files
     *
     * @param  \Redooor\Redminportal\App\Models\Image $image
     * @return void
     */
    private function deleteImageFiles(Image $image): void
    {
        $filename = $image->path;
        $image->deleteFiles($filename);

        $imagine = new RImage;
        $defaultDimensions = Config::get('redminportal::image.dimensions');

        foreach (array_keys($defaultDimensions) as $key) {
            $filename_variant = $imagine->getUrl($filename, $key);
            $image->deleteFiles($filename_variant);
        }
    }
}
