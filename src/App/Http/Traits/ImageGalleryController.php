<?php namespace Redooor\Redminportal\App\Http\Traits;

use Illuminate\Support\MessageBag;
use Redooor\Redminportal\App\Classes\ImageService;

/*
 * Add primary image and image ordering capability to controller
 */
trait ImageGalleryController
{
    /**
     * Model class that owns the images handled by this controller
     *
     * @return string
     */
    abstract protected function galleryModelClass(): string;

    /**
     * Admin path of the module, e.g. admin/posts
     *
     * @return string
     */
    abstract protected function galleryPath(): string;

    public function getImgprimary($sid)
    {
        $image = $this->findOwnedImage($sid, $this->galleryModelClass());

        if ($image == null) {
            return $this->imageGalleryError();
        }

        (new ImageService)->makePrimary($image);

        return redirect($this->galleryPath() . '/edit/' . $image->imageable_id);
    }

    public function getImgmove($sid, $direction)
    {
        if (! in_array($direction, array('earlier', 'later'), true)) {
            abort(404);
        }

        $image = $this->findOwnedImage($sid, $this->galleryModelClass());

        if ($image == null) {
            return $this->imageGalleryError();
        }

        $model = $image->imageable;

        if ($model == null) {
            return $this->imageGalleryError();
        }

        // The relation is already primary-first and then in sort order
        $ids = array_map('intval', $model->images()->pluck('id')->all());
        $index = array_search((int) $image->getKey(), $ids, true);

        // The primary image always sorts first, so moving it has no visible effect
        if ($index === false || $image->is_primary) {
            return redirect($this->galleryPath() . '/edit/' . $model->getKey());
        }

        $neighbour = $direction === 'earlier' ? $index - 1 : $index + 1;

        // Moving at either end is a no-op
        if (isset($ids[$neighbour])) {
            $ids[$index] = $ids[$neighbour];
            $ids[$neighbour] = (int) $image->getKey();

            (new ImageService)->reorder($model, $ids);
        }

        return redirect($this->galleryPath() . '/edit/' . $model->getKey());
    }

    private function imageGalleryError()
    {
        $errors = new MessageBag;
        $errors->add('deleteError', trans('redminportal::messages.error_image_not_found'));

        return redirect($this->galleryPath())->withErrors($errors);
    }
}
