@extends('redminportal::layouts.master')

@section('navbar-breadcrumb')
    <li class="nav-item"><a class="nav-link" href="{{ URL::to('admin/promotions') }}">{{ Lang::get('redminportal::menus.promotions') }}</a></li>
    <li class="nav-item"><span class="nav-link active">{{ Lang::get('redminportal::forms.edit') }}</span></li>
@stop

@section('content')
    @include('redminportal::partials.confirm-delete')
    
    @include('redminportal::partials.errors')

    <form method="POST" action="{{ action('\Redooor\Redminportal\App\Http\Controllers\PromotionController@postStore') }}" enctype="multipart/form-data" role="form">
    @csrf
        <input type="hidden" name="id" value="{{ old('id', $promotion->id) }}">

    	<div class='row'>
    	    <div class="col-lg-3 order-lg-last">
                <div class="card card-body bg-body-tertiary">
                    <div class='form-actions'>
                        <a href="{{ URL::to('admin/promotions') }}" class="btn btn-link btn-sm">{{ Lang::get('redminportal::buttons.cancel') }}</a>
                        <input type="submit" value="{{ Lang::get('redminportal::buttons.save') }}" class="btn btn-primary btn-sm float-end">
                    </div>
                </div>
                <div class='card card-body bg-body-tertiary p-2'>
                    <div class="mb-3">
                        <div class="form-check">
                            <label class="form-check-label" for="active-checker">
                                <input type="checkbox" name="active" value="1" id="active-checker" class="form-check-input" @checked(session()->hasOldInput() ? old('active') : $promotion->active)> {{ Lang::get('redminportal::forms.active') }}
                            </label>
                        </div>
                    </div>
                </div>
                <div class="card card-body bg-body-tertiary p-2">
                    <div class="mb-3">
                        <label for="start_date">{{ Lang::get('redminportal::forms.start_date') }}</label>
                        <div class="input-group" id='start-date'>
                            <input type="text" name="start_date" id="start_date" value="{{ old('start_date', $start_date->format('d/m/Y')) }}" class="form-control datepicker" readonly>
                            <span class="input-group-text"><i class="fa-solid fa-calendar"></i></span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="end_date">{{ Lang::get('redminportal::forms.end_date') }}</label>
                        <div class="input-group" id='end-date'>
                            <input type="text" name="end_date" id="end_date" value="{{ old('end_date', $end_date->format('d/m/Y')) }}" class="form-control datepicker" readonly>
                            <span class="input-group-text"><i class="fa-solid fa-calendar"></i></span>
                        </div>
                    </div>
                </div>
                @include('redminportal::partials.image-input')
            </div>
    	    <div class="col-lg-9 order-lg-first">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">{{ Lang::get('redminportal::forms.edit_promotion') }}</h4>
                    </div>
                    <div class="card-body">
                        <ul class="nav nav-tabs" id="lang-selector">
                           @foreach(\Config::get('redminportal::translation') as $translation)
                           <li class="nav-item"><a class="nav-link @if ($loop->first)active @endif" href="#lang-{{ $translation['lang'] }}" data-bs-toggle="tab" data-bs-target="#lang-{{ $translation['lang'] }}">{{ $translation['name'] }}</a></li>
                           @endforeach
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="lang-en">
                                <div class="mb-3">
                                    <label for="name">{{ Lang::get('redminportal::forms.title') }}</label>
                                    <input type="text" name="name" id="name" value="{{ old('name', $promotion->name) }}" class="form-control">
                                </div>

                                <div class="mb-3">
                                    <label for="short_description">{{ Lang::get('redminportal::forms.summary') }}</label>
                                    <input type="text" name="short_description" id="short_description" value="{{ old('short_description', $promotion->short_description) }}" class="form-control">
                                </div>

                                <div class="mb-3">
                                    <label for="long_description">{{ Lang::get('redminportal::forms.description') }}</label>
                                    <textarea name="long_description" id="long_description" class="form-control" style="height:200px">{{ old('long_description', $promotion->long_description) }}</textarea>
                                </div>
                            </div>
                            @foreach(\Config::get('redminportal::translation') as $translation)
                                @if($translation['lang'] != 'en')
                                <div class="tab-pane fade" id="lang-{{ $translation['lang'] }}">
                                    <div class="mb-3">
                                        <label for="{{ $translation['lang'] . '_name' }}">{{ Lang::get('redminportal::forms.title') }}</label>
                                        @if ($translated)
                                        <input type="text" name="{{ $translation['lang'] . '_name' }}" id="{{ $translation['lang'] . '_name' }}" value="{{ old($translation['lang'] . '_name', (array_key_exists($translation['lang'], $translated) ? $translated[$translation['lang']]->name : '')) }}" class="form-control">
                                        @else
                                        <input type="text" name="{{ $translation['lang'] . '_name' }}" id="{{ $translation['lang'] . '_name' }}" value="{{ old($translation['lang'] . '_name', null) }}" class="form-control">
                                        @endif
                                    </div>

                                    <div class="mb-3">
                                        <label for="{{ $translation['lang'] . '_short_description' }}">{{ Lang::get('redminportal::forms.summary') }}</label>
                                        @if ($translated)
                                        <input type="text" name="{{ $translation['lang'] . '_short_description' }}" id="{{ $translation['lang'] . '_short_description' }}" value="{{ old($translation['lang'] . '_short_description', (array_key_exists($translation['lang'], $translated) ? $translated[$translation['lang']]->short_description : '')) }}" class="form-control">
                                        @else
                                        <input type="text" name="{{ $translation['lang'] . '_short_description' }}" id="{{ $translation['lang'] . '_short_description' }}" value="{{ old($translation['lang'] . '_short_description', null) }}" class="form-control">
                                        @endif
                                    </div>

                                    <div class="mb-3">
                                        <label for="{{ $translation['lang'] . '_long_description' }}">{{ Lang::get('redminportal::forms.description') }}</label>
                                        @if ($translated)
                                        <textarea name="{{ $translation['lang'] . '_long_description' }}" id="{{ $translation['lang'] . '_long_description' }}" class="form-control" style="height:200px">{{ old($translation['lang'] . '_long_description', (array_key_exists($translation['lang'], $translated) ? $translated[$translation['lang']]->long_description : '')) }}</textarea>
                                        @else
                                        <textarea name="{{ $translation['lang'] . '_long_description' }}" id="{{ $translation['lang'] . '_long_description' }}" class="form-control" style="height:200px">{{ old($translation['lang'] . '_long_description', null) }}</textarea>
                                        @endif
                                    </div>
                                </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
                @include('redminportal::partials.image-gallery', [
                    'images' => $promotion->images,
                    'imagine' => $imagine,
                    'base' => 'admin/promotions',
                    'alt' => $promotion->name,
                ])
            </div>
    	</div>
    </form>
@stop

@section('footer')
    <script src="{{ URL::to('vendor/redooor/redminportal/js/redmin-fileinput.min.js') }}"></script>
    <script>
        !function ($) {
            $(function(){
                $( ".datepicker" ).datepicker({ dateFormat: "dd/mm/yy" });

                $( "#end-date .input-group-text" ).click( function() {
                    $( "#end_date" ).datepicker( "show" );
                });

                $( "#start-date .input-group-text" ).click( function() {
                    $( "#start_date" ).datepicker( "show" );
                });

            })
        }(window.jQuery);
    </script>
    @include('redminportal::plugins/editor', ['editorImages' => $promotion->images])
@stop
