<?php namespace Redooor\Redminportal\App\Classes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
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

        $uploaded = array();
        $imagine = new Imagine;

        // Files are written before the transaction so the lock is held only for the rows
        foreach ($files as $file) {
            if (! ($file instanceof UploadedFile)) {
                continue;
            }

            $path = $imagine->upload($file, $folder . '/' . $model->getKey(), $createDimensions);

            if (! $path) {
                continue;
            }

            $uploaded[] = $path;
        }

        if (empty($uploaded)) {
            return $images;
        }

        try {
            DB::transaction(function () use ($model, $uploaded, &$images) {
                $this->lockOwner($model);

                $hadImages = $model->images()->exists();
                $sortOrder = ((int) $model->images()->max('sort_order')) + 1;
                $primarySet = false;

                foreach ($uploaded as $path) {
                    $image = new Image;
                    $image->path = $path;
                    $image->sort_order = $sortOrder++;
                    $image->is_primary = (! $hadImages && ! $primarySet);

                    if ($image->is_primary) {
                        $primarySet = true;
                    }

                    if (! $model->images()->save($image)) {
                        throw new \RuntimeException('Unable to save the image');
                    }

                    $images->push($image);
                }
            });
        } catch (\Throwable $e) {
            foreach ($uploaded as $path) {
                $this->deleteUploadedFiles($path);
            }

            throw $e;
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
     * @return bool
     */
    public function remove(Image $image): bool
    {
        return DB::transaction(function () use ($image) {
            $model = null;

            try {
                $model = $image->imageable;
            } catch (\Throwable $e) {
                $model = null;
            }

            if ($model) {
                $this->lockOwner($model);
            }

            // Read the primary flag again under the lock, another request may have changed it
            $current = Image::find($image->getKey());
            $wasPrimary = $current ? (bool) $current->is_primary : (bool) $image->is_primary;

            if (! $image->deleteRecord()) {
                return false;
            }

            // Delete the files only once the whole transaction (including any outer one) has committed
            DB::afterCommit(function () use ($image) {
                $this->deleteImageFiles($image);
            });

            if ($wasPrimary && $model) {
                $next = $model->images()->first();

                if ($next) {
                    $next->is_primary = true;
                    $next->save();
                }
            }

            return true;
        });
    }

    /**
     * Make the given image the only primary image of its model
     *
     * @param  \Redooor\Redminportal\App\Models\Image $image
     * @return void
     */
    public function makePrimary(Image $image): void
    {
        DB::transaction(function () use ($image) {
            $model = $image->imageable;

            if (! $model) {
                return;
            }

            $this->lockOwner($model);

            // The image may have been removed since it was loaded
            if (! $model->images()->where('id', $image->getKey())->exists()) {
                return;
            }

            $model->images()
                ->where('id', '!=', $image->getKey())
                ->update(['is_primary' => false]);

            Image::whereKey($image->getKey())->update(['is_primary' => true]);

            $image->setAttribute('is_primary', true);
            $image->syncOriginalAttribute('is_primary');
        });
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
        DB::transaction(function () use ($model, $imageIds) {
            $this->lockOwner($model);

            $images = $model->images()->get()->keyBy('id');
            $orderedIds = array();

            foreach (array_unique(array_map('intval', $imageIds)) as $imageId) {
                if (isset($images[$imageId])) {
                    $orderedIds[] = $imageId;
                }
            }

            $sortOrder = 1;
            $remaining = $images->except($orderedIds);

            foreach ($orderedIds as $imageId) {
                $image = $images[$imageId];
                $image->sort_order = $sortOrder++;
                $image->save();
            }

            foreach ($remaining as $image) {
                $image->sort_order = $sortOrder++;
                $image->save();
            }
        });
    }

    /**
     * Lock the owner row for the duration of the transaction
     *
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    private function lockOwner(Model $model)
    {
        return $model->newQuery()->whereKey($model->getKey())->lockForUpdate()->first();
    }

    /**
     * Delete the original and dimension files of an uploaded image that was not saved
     *
     * @param  string $path
     * @return void
     */
    private function deleteUploadedFiles(string $path): void
    {
        $image = new Image;
        $image->path = $path;

        $this->deleteImageFiles($image);
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
