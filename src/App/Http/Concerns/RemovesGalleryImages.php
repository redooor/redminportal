<?php namespace Redooor\Redminportal\App\Http\Concerns;

/*
 * Internal helper for removing gallery images. Not an extension API.
 *
 * Requires ImageGalleryController or equivalent: galleryModelClass() and galleryPath().
 */
trait RemovesGalleryImages
{
    /**
     * Remove an image owned by this controller's gallery model
     *
     * @param integer $sid Image id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function getImgremove($sid)
    {
        $image = $this->findOwnedImage($sid, $this->galleryModelClass());

        if ($image == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add('deleteError', "The image cannot be deleted at this time.");
            return redirect('/' . $this->galleryPath())->withErrors($errors);
        }

        $image_owner_id = $image->imageable_id;

        $image->delete();

        return redirect($this->galleryPath() . '/edit/' . $image_owner_id);
    }
}
