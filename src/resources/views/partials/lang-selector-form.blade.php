<?php
/*
    Language Selector Tab template for reuse
    --------------------------------
    Usage Example:
    --------------------------------
    @include('redminportal::partials.lang-selector-form', [
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
            {!! Form::text('name', (isset($translatable) ? $translatable->name : null), array('class' => 'form-control')) !!}
        </div>

        <div class="form-group">
            {!! Form::label('short_description', Lang::get('redminportal::forms.summary')) !!}
            {!! Form::text('short_description', (isset($translatable) ? $translatable->short_description : null), array('class' => 'form-control')) !!}
        </div>

        <div class="form-group">
            {!! Form::label('long_description', Lang::get('redminportal::forms.description')) !!}
            {!! Form::textarea('long_description', (isset($translatable) ? $translatable->long_description : null), array('class' => 'form-control', 'style' => 'height:200px')) !!}
        </div>
    </div>
    @foreach(\Config::get('redminportal::translation') as $translation)
        @if($translation['lang'] != 'en')
        <div class="tab-pane" id="lang-{{ $translation['lang'] }}{{ isset($selector_name) ? $selector_name : '' }}">
            <div class="form-group">
                {!! Form::label($translation['lang'] . '_name', Lang::get('redminportal::forms.title')) !!}
                @if (isset($translated))
                {!! Form::text($translation['lang'] . '_name', (array_key_exists($translation['lang'], $translated) ? $translated[$translation['lang']]->name : ''), array('class' => 'form-control')) !!}
                @else
                {!! Form::text($translation['lang'] . '_name', null, array('class' => 'form-control')) !!}
                @endif
            </div>
            
            <div class="form-group">
                {!! Form::label($translation['lang'] . '_short_description', Lang::get('redminportal::forms.summary')) !!}
                @if (isset($translated))
                {!! Form::text($translation['lang'] . '_short_description', (array_key_exists($translation['lang'], $translated) ? $translated[$translation['lang']]->short_description : ''), array('class' => 'form-control')) !!}
                @else
                {!! Form::text($translation['lang'] . '_short_description', null, array('class' => 'form-control')) !!}
                @endif
            </div>

            <div class="form-group">
                {!! Form::label($translation['lang'] . '_long_description', Lang::get('redminportal::forms.description')) !!}
                @if (isset($translated))
                {!! Form::textarea($translation['lang'] . '_long_description', (array_key_exists($translation['lang'], $translated) ? $translated[$translation['lang']]->long_description : ''), array('class' => 'form-control', 'style' => 'height:200px')) !!}
                @else
                {!! Form::textarea($translation['lang'] . '_long_description', null, array('class' => 'form-control', 'style' => 'height:200px')) !!}
                @endif
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
