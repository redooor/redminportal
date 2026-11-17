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
            <div class="form-label">{{ Lang::get('redminportal::forms.title') }}</div>
            <div>{{ $translatable->name ?? '' }}</div>
        </div>

        <div class="mb-3">
            <div class="form-label">{{ Lang::get('redminportal::forms.summary') }}</div>
            <div>{!! \Redooor\Redminportal\App\Helpers\RHelper::cleanHtml($translatable->short_description ?? null) !!}</div>
        </div>

        <div class="mb-3">
            <div class="form-label">{{ Lang::get('redminportal::forms.description') }}</div>
            <div>{!! \Redooor\Redminportal\App\Helpers\RHelper::cleanHtml($translatable->long_description ?? null) !!}</div>
        </div>
    </div>
    @foreach(\Config::get('redminportal::translation') as $translation)
        @if($translation['lang'] != 'en')
        <div class="tab-pane fade" id="lang-{{ $translation['lang'] }}{{ isset($selector_name) ? $selector_name : '' }}">
            <div class="mb-3">
                <div class="form-label">{{ Lang::get('redminportal::forms.title') }}</div>
                <div>{{ array_key_exists($translation['lang'], $translated) ? $translated[$translation['lang']]->name : '' }}</div>
            </div>

            <div class="mb-3">
                <div class="form-label">{{ Lang::get('redminportal::forms.summary') }}</div>
                <div>{!! \Redooor\Redminportal\App\Helpers\RHelper::cleanHtml(array_key_exists($translation['lang'], $translated) ? $translated[$translation['lang']]->short_description : null) !!}</div>
            </div>

            <div class="mb-3">
                <div class="form-label">{{ Lang::get('redminportal::forms.description') }}</div>
                <div>{!! \Redooor\Redminportal\App\Helpers\RHelper::cleanHtml(array_key_exists($translation['lang'], $translated) ? $translated[$translation['lang']]->long_description : null) !!}</div>
            </div>
        </div>
        @endif
    @endforeach
</div>
