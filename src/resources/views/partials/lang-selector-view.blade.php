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
   <li><a href="#lang-{{ $translation['lang'] }}{{ isset($selector_name) ? $selector_name : '' }}">{{ $translation['name'] }}</a></li>
   @endforeach
</ul>
<div class="tab-content">
    <div class="tab-pane active" id="lang-en{{ isset($selector_name) ? $selector_name : '' }}">
        <div class="form-group">
            {!! Form::label('name', Lang::get('redminportal::forms.title')) !!}
            <div>{!! $translatable->name or '' !!}</div>
        </div>

        <div class="form-group">
            {!! Form::label('short_description', Lang::get('redminportal::forms.summary')) !!}
            <div>{!! $translatable->short_description or '' !!}</div>
        </div>

        <div class="form-group">
            {!! Form::label('long_description', Lang::get('redminportal::forms.description')) !!}
            <div>{!! $translatable->long_description or '' !!}</div>
        </div>
    </div>
    @foreach(\Config::get('redminportal::translation') as $translation)
        @if($translation['lang'] != 'en')
        <div class="tab-pane" id="lang-{{ $translation['lang'] }}{{ isset($selector_name) ? $selector_name : '' }}">
            <div class="form-group">
                {!! Form::label($translation['lang'] . '_name', Lang::get('redminportal::forms.title')) !!}
                <div>{!! (array_key_exists($translation['lang'], $translated) ? $translated[$translation['lang']]->name : '') !!}</div>
            </div>

            <div class="form-group">
                {!! Form::label($translation['lang'] . '_short_description', Lang::get('redminportal::forms.summary')) !!}
                <div>{!! (array_key_exists($translation['lang'], $translated) ? $translated[$translation['lang']]->short_description : '') !!}</div>
            </div>

            <div class="form-group">
                {!! Form::label($translation['lang'] . '_long_description', Lang::get('redminportal::forms.description')) !!}
                <div>{!! (array_key_exists($translation['lang'], $translated) ? $translated[$translation['lang']]->long_description : '') !!}</div>
            </div>
        </div>
        @endif
    @endforeach
</div>
<script>
    window.onload = function() {
        $("#lang-selector{{ isset($selector_name) ? $selector_name : '' }} li").first().addClass('active');
        $(document).on('click', "#lang-selector{{ isset($selector_name) ? $selector_name : '' }} a", function (e) {
            e.preventDefault();
            $(this).tab('show');
        });
    };
</script>
