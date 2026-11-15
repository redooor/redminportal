<?php namespace Redooor\Redminportal\App\Http\Concerns;

/*
 * Internal helper for removing gallery images. Not an extension API.
 *
 * The controller keeps its own public getImgremove() action and passes its fixed model class and path, so a host
 * subclass that overrides the gallery model class or path methods does not change image removal.
 */
trait RemovesGalleryImages
{
    /**
     * Remove an image owned by the given model class
     *
     * @param integer $sid Image id
     * @param string $modelClass Model class that owns the image
     * @param string $path Admin path of the module, e.g. admin/pages
     * @return \Illuminate\Http\RedirectResponse
     */
    private function removeOwnedGalleryImage($sid, string $modelClass, string $path)
    {
        $image = $this->findOwnedImage($sid, $modelClass);

        if ($image == null) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add('deleteError', "The image cannot be deleted at this time.");
            return redirect('/' . $path)->withErrors($errors);
        }

        $image_owner_id = $image->imageable_id;

        $image->delete();

        return redirect($path . '/edit/' . $image_owner_id);
    }
}
