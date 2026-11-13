{{--
    Image file picker for one or more images
    --------------------------------
    Usage Example:
    --------------------------------
    @include('redminportal::partials.image-input')
    --------------------------------
--}}
<div class="redmin-fileinput" data-redmin-fileinput>
    <div class="redmin-fileinput-preview img-thumbnail"></div>
    <div>
        <label class="btn btn-secondary">
            <span class="redmin-fileinput-new">{{ Lang::get('redminportal::forms.select_images') }}</span><span class="redmin-fileinput-exists">{{ Lang::get('redminportal::forms.change_images') }}</span>
            <input name="images[]" type="file" multiple accept="image/*" class="visually-hidden">
        </label>
        <button type="button" class="btn btn-danger redmin-fileinput-exists" data-redmin-fileinput-clear>{{ Lang::get('redminportal::forms.remove_images') }}</button>
    </div>
</div>
