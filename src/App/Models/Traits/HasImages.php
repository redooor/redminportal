<?php namespace Redooor\Redminportal\App\Models\Traits;

trait HasImages
{
    /**
     * Images ordered with the primary image first, then by display order and id
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function images()
    {
        return $this->morphMany(\Redooor\Redminportal\App\Models\Image::class, 'imageable')
            ->orderByDesc('is_primary')
            ->orderBy('sort_order')
            ->orderBy('id');
    }
}
