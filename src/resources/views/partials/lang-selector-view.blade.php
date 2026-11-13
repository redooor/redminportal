<?php
/*
    Language Selector Tab template for reuse
    --------------------------------
    Usage Example:
    --------------------------------
    @include('redminportal::partials.lang-selector-view', [
        'selector_name' => '-variant',
        'translatable' => $product,
        'translated' => $translated
    ])
*/
?>
<ul class="nav nav-tabs" id="lang-selector{{ isset($selector_name) ? $selector_name : '' }}">
   @foreach(\Config::get('redminportal::translation') as $translation)
   <li class="nav-item"><a class="nav-link @if($loop->first) active @endif" href="#lang-{{ $translation['lang'] }}{{ isset($selector_name) ? $selector_name : '' }}" data-bs-toggle="tab" data-bs-target="#lang-{{ $translation['lang'] }}{{ isset($selector_name) ? $selector_name : '' }}">{{ $translation['name'] }}</a></li>
   @endforeach
</ul>
<div class="tab-content">
    <div class="tab-pane fade show active" id="lang-en{{ isset($selector_name) ? $selector_name : '' }}">
        <div class="mb-3">
            {!! Form::label('name', Lang::get('redminportal::forms.title')) !!}
            <div>{{ $translatable->name ?? '' }}</div>
        </div>

        <div class="mb-3">
            {!! Form::label('short_description', Lang::get('redminportal::forms.summary')) !!}
            <div>{!! \Redooor\Redminportal\App\Helpers\RHelper::cleanHtml($translatable->short_description ?? null) !!}</div>
        </div>

        <div class="mb-3">
            {!! Form::label('long_description', Lang::get('redminportal::forms.description')) !!}
            <div>{!! \Redooor\Redminportal\App\Helpers\RHelper::cleanHtml($translatable->long_description ?? null) !!}</div>
        </div>
    </div>
    @foreach(\Config::get('redminportal::translation') as $translation)
        @if($translation['lang'] != 'en')
        <div class="tab-pane fade" id="lang-{{ $translation['lang'] }}{{ isset($selector_name) ? $selector_name : '' }}">
            <div class="mb-3">
                {!! Form::label($translation['lang'] . '_name', Lang::get('redminportal::forms.title')) !!}
                <div>{{ array_key_exists($translation['lang'], $translated) ? $translated[$translation['lang']]->name : '' }}</div>
            </div>

            <div class="mb-3">
                {!! Form::label($translation['lang'] . '_short_description', Lang::get('redminportal::forms.summary')) !!}
                <div>{!! \Redooor\Redminportal\App\Helpers\RHelper::cleanHtml(array_key_exists($translation['lang'], $translated) ? $translated[$translation['lang']]->short_description : null) !!}</div>
            </div>

            <div class="mb-3">
                {!! Form::label($translation['lang'] . '_long_description', Lang::get('redminportal::forms.description')) !!}
                <div>{!! \Redooor\Redminportal\App\Helpers\RHelper::cleanHtml(array_key_exists($translation['lang'], $translated) ? $translated[$translation['lang']]->long_description : null) !!}</div>
            </div>
        </div>
        @endif
    @endforeach
</div>
