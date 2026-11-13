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

        (new ImageService)->move($image, $direction);

        return redirect($this->galleryPath() . '/edit/' . $model->getKey());
    }

    private function imageGalleryError()
    {
        $errors = new MessageBag;
        $errors->add('deleteError', trans('redminportal::messages.error_image_not_found'));

        return redirect($this->galleryPath())->withErrors($errors);
    }
}
