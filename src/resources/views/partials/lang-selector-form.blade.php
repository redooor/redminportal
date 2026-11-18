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
            {!! Form::label($lang_id_prefix . 'name', Lang::get('redminportal::forms.title')) !!}
            {!! Form::text('name', old('name', (isset($translatable) ? $translatable->name : null)), array('class' => 'form-control') + ($lang_id_prefix === '' ? array() : array('id' => $lang_id_prefix . 'name'))) !!}
        </div>

        <div class="mb-3">
            {!! Form::label($lang_id_prefix . 'short_description', Lang::get('redminportal::forms.summary')) !!}
            {!! Form::text('short_description', old('short_description', (isset($translatable) ? $translatable->short_description : null)), array('class' => 'form-control') + ($lang_id_prefix === '' ? array() : array('id' => $lang_id_prefix . 'short_description'))) !!}
        </div>

        <div class="mb-3">
            {!! Form::label($lang_id_prefix . 'long_description', Lang::get('redminportal::forms.description')) !!}
            {!! Form::textarea('long_description', old('long_description', (isset($translatable) ? $translatable->long_description : null)), array('class' => 'form-control', 'style' => 'height:200px') + ($lang_id_prefix === '' ? array() : array('id' => $lang_id_prefix . 'long_description'))) !!}
        </div>
    </div>
    @foreach(\Config::get('redminportal::translation') as $translation)
        @if($translation['lang'] != 'en')
        <div class="tab-pane fade" id="lang-{{ $translation['lang'] }}{{ isset($selector_name) ? $selector_name : '' }}">
            <div class="mb-3">
                {!! Form::label($lang_id_prefix . $translation['lang'] . '_name', Lang::get('redminportal::forms.title')) !!}
                @if (isset($translated))
                {!! Form::text($translation['lang'] . '_name', old($translation['lang'] . '_name', (array_key_exists($translation['lang'], $translated) ? $translated[$translation['lang']]->name : '')), array('class' => 'form-control') + ($lang_id_prefix === '' ? array() : array('id' => $lang_id_prefix . $translation['lang'] . '_name'))) !!}
                @else
                {!! Form::text($translation['lang'] . '_name', old($translation['lang'] . '_name'), array('class' => 'form-control') + ($lang_id_prefix === '' ? array() : array('id' => $lang_id_prefix . $translation['lang'] . '_name'))) !!}
                @endif
            </div>
            
            <div class="mb-3">
                {!! Form::label($lang_id_prefix . $translation['lang'] . '_short_description', Lang::get('redminportal::forms.summary')) !!}
                @if (isset($translated))
                {!! Form::text($translation['lang'] . '_short_description', old($translation['lang'] . '_short_description', (array_key_exists($translation['lang'], $translated) ? $translated[$translation['lang']]->short_description : '')), array('class' => 'form-control') + ($lang_id_prefix === '' ? array() : array('id' => $lang_id_prefix . $translation['lang'] . '_short_description'))) !!}
                @else
                {!! Form::text($translation['lang'] . '_short_description', old($translation['lang'] . '_short_description'), array('class' => 'form-control') + ($lang_id_prefix === '' ? array() : array('id' => $lang_id_prefix . $translation['lang'] . '_short_description'))) !!}
                @endif
            </div>

            <div class="mb-3">
                {!! Form::label($lang_id_prefix . $translation['lang'] . '_long_description', Lang::get('redminportal::forms.description')) !!}
                @if (isset($translated))
                {!! Form::textarea($translation['lang'] . '_long_description', old($translation['lang'] . '_long_description', (array_key_exists($translation['lang'], $translated) ? $translated[$translation['lang']]->long_description : '')), array('class' => 'form-control', 'style' => 'height:200px') + ($lang_id_prefix === '' ? array() : array('id' => $lang_id_prefix . $translation['lang'] . '_long_description'))) !!}
                @else
                {!! Form::textarea($translation['lang'] . '_long_description', old($translation['lang'] . '_long_description'), array('class' => 'form-control', 'style' => 'height:200px') + ($lang_id_prefix === '' ? array() : array('id' => $lang_id_prefix . $translation['lang'] . '_long_description'))) !!}
                @endif
            </div>
        </div>
        @endif
    @endforeach
</div>
