<?php
/*
    Language Selector Tab template for reuse
    --------------------------------
    Usage Example:
    --------------------------------
    @include('redminportal::partials.lang-selector-form', [
        'selector_name' => '-variant',
        'id_prefix' => 'variant-',
        'translatable' => $product,
        'translated' => $translated
    ])
*/
$lang_id_prefix = isset($id_prefix) ? $id_prefix : '';
?>
<ul class="nav nav-tabs" id="lang-selector{{ isset($selector_name) ? $selector_name : '' }}">
   @foreach(\Config::get('redminportal::translation') as $translation)
   <li class="nav-item"><a class="nav-link @if($loop->first) active @endif" href="#lang-{{ $translation['lang'] }}{{ isset($selector_name) ? $selector_name : '' }}" data-bs-toggle="tab" data-bs-target="#lang-{{ $translation['lang'] }}{{ isset($selector_name) ? $selector_name : '' }}">{{ $translation['name'] }}</a></li>
   @endforeach
</ul>
<div class="tab-content">
    <div class="tab-pane fade show active" id="lang-en{{ isset($selector_name) ? $selector_name : '' }}">
        <div class="mb-3">
            <label for="{{ $lang_id_prefix . 'name' }}">{{ Lang::get('redminportal::forms.title') }}</label>
            <input type="text" name="name" id="{{ $lang_id_prefix . 'name' }}" value="{{ old('name', (isset($translatable) ? $translatable->name : null)) }}" class="form-control">
        </div>

        <div class="mb-3">
            <label for="{{ $lang_id_prefix . 'short_description' }}">{{ Lang::get('redminportal::forms.summary') }}</label>
            <input type="text" name="short_description" id="{{ $lang_id_prefix . 'short_description' }}" value="{{ old('short_description', (isset($translatable) ? $translatable->short_description : null)) }}" class="form-control">
        </div>

        <div class="mb-3">
            <label for="{{ $lang_id_prefix . 'long_description' }}">{{ Lang::get('redminportal::forms.description') }}</label>
            <textarea name="long_description" id="{{ $lang_id_prefix . 'long_description' }}" class="form-control" style="height:200px">{{ old('long_description', (isset($translatable) ? $translatable->long_description : null)) }}</textarea>
        </div>
    </div>
    @foreach(\Config::get('redminportal::translation') as $translation)
        @if($translation['lang'] != 'en')
        <div class="tab-pane fade" id="lang-{{ $translation['lang'] }}{{ isset($selector_name) ? $selector_name : '' }}">
            <div class="mb-3">
                <label for="{{ $lang_id_prefix . $translation['lang'] . '_name' }}">{{ Lang::get('redminportal::forms.title') }}</label>
                @if (isset($translated))
                <input type="text" name="{{ $translation['lang'] . '_name' }}" id="{{ $lang_id_prefix . $translation['lang'] . '_name' }}" value="{{ old($translation['lang'] . '_name', (array_key_exists($translation['lang'], $translated) ? $translated[$translation['lang']]->name : '')) }}" class="form-control">
                @else
                <input type="text" name="{{ $translation['lang'] . '_name' }}" id="{{ $lang_id_prefix . $translation['lang'] . '_name' }}" value="{{ old($translation['lang'] . '_name') }}" class="form-control">
                @endif
            </div>
            
            <div class="mb-3">
                <label for="{{ $lang_id_prefix . $translation['lang'] . '_short_description' }}">{{ Lang::get('redminportal::forms.summary') }}</label>
                @if (isset($translated))
                <input type="text" name="{{ $translation['lang'] . '_short_description' }}" id="{{ $lang_id_prefix . $translation['lang'] . '_short_description' }}" value="{{ old($translation['lang'] . '_short_description', (array_key_exists($translation['lang'], $translated) ? $translated[$translation['lang']]->short_description : '')) }}" class="form-control">
                @else
                <input type="text" name="{{ $translation['lang'] . '_short_description' }}" id="{{ $lang_id_prefix . $translation['lang'] . '_short_description' }}" value="{{ old($translation['lang'] . '_short_description') }}" class="form-control">
                @endif
            </div>

            <div class="mb-3">
                <label for="{{ $lang_id_prefix . $translation['lang'] . '_long_description' }}">{{ Lang::get('redminportal::forms.description') }}</label>
                @if (isset($translated))
                <textarea name="{{ $translation['lang'] . '_long_description' }}" id="{{ $lang_id_prefix . $translation['lang'] . '_long_description' }}" class="form-control" style="height:200px">{{ old($translation['lang'] . '_long_description', (array_key_exists($translation['lang'], $translated) ? $translated[$translation['lang']]->long_description : '')) }}</textarea>
                @else
                <textarea name="{{ $translation['lang'] . '_long_description' }}" id="{{ $lang_id_prefix . $translation['lang'] . '_long_description' }}" class="form-control" style="height:200px">{{ old($translation['lang'] . '_long_description') }}</textarea>
                @endif
            </div>
        </div>
        @endif
    @endforeach
</div>
