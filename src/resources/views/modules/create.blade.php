@extends('redminportal::layouts.master')

@section('navbar-breadcrumb')
    <li class="nav-item"><a class="nav-link" href="{{ URL::to('admin/modules') }}">{{ Lang::get('redminportal::menus.modules') }}</a></li>
    <li class="nav-item"><span class="nav-link active">{{ Lang::get('redminportal::forms.create') }}</span></li>
@stop

@section('content')
    
    @include('redminportal::partials.errors')

    <form method="POST" action="{{ action('\Redooor\Redminportal\App\Http\Controllers\ModuleController@postStore') }}" enctype="multipart/form-data" role="form">
    @csrf

    	<div class='row'>
            <div class="col-lg-3 order-lg-last">
                <div class="card card-body bg-body-tertiary">
                    <div class='form-actions'>
                        <a href="{{ URL::to('admin/modules') }}" class="btn btn-link btn-sm">{{ Lang::get('redminportal::buttons.cancel') }}</a>
                        <input type="submit" value="{{ Lang::get('redminportal::buttons.create') }}" class="btn btn-primary btn-sm float-end">
                    </div>
                </div>
                <div class='card card-body bg-body-tertiary p-2'>
                    <div class="mb-3">
                        <div class="form-check">
                            <label class="form-check-label" for="featured-checker">
                                <input class="form-check-input" id="featured-checker" checked="checked" name="featured" type="checkbox" value="1"> {{ Lang::get('redminportal::forms.featured') }}
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <label class="form-check-label" for="active-checker">
                                <input class="form-check-input" id="active-checker" checked="checked" name="active" type="checkbox" value="1"> {{ Lang::get('redminportal::forms.active') }}
                            </label>
                        </div>
                    </div>
                </div>
                {{-- Load Select Category partial form --}}
                @include('redminportal::partials.form-select-category', [
                    'select_category_selected_name' => 'category_id',
                    'select_category_categories' => $categories,
                    'select_category_required_field' => true
                ])
		        <div>
                    @include('redminportal::partials.image-input')
                </div>
	        </div>

	        <div class="col-lg-9 order-lg-first">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">{{ Lang::get('redminportal::forms.create_module') }}</h4>
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
                                    <input type="text" name="name" id="name" value="{{ old('name', null) }}" class="form-control">
                                </div>

                                <div class="mb-3">
                                    <label for="short_description">{{ Lang::get('redminportal::forms.summary') }}</label>
                                    <input type="text" name="short_description" id="short_description" value="{{ old('short_description', null) }}" class="form-control">
                                </div>

                                <div class="mb-3">
                                    <label for="long_description">{{ Lang::get('redminportal::forms.description') }}</label>
                                    <textarea name="long_description" id="long_description" class="form-control" style="height:200px">{{ old('long_description', null) }}</textarea>
                                </div>
                            </div>
                            @foreach(\Config::get('redminportal::translation') as $translation)
                                @if($translation['lang'] != 'en')
                                <div class="tab-pane fade" id="lang-{{ $translation['lang'] }}">
                                    <div class="mb-3">
                                        <label for="{{ $translation['lang'] . '_name' }}">{{ Lang::get('redminportal::forms.title') }}</label>
                                        <input type="text" name="{{ $translation['lang'] . '_name' }}" id="{{ $translation['lang'] . '_name' }}" value="{{ old($translation['lang'] . '_name', null) }}" class="form-control">
                                    </div>

                                    <div class="mb-3">
                                        <label for="{{ $translation['lang'] . '_short_description' }}">{{ Lang::get('redminportal::forms.summary') }}</label>
                                        <input type="text" name="{{ $translation['lang'] . '_short_description' }}" id="{{ $translation['lang'] . '_short_description' }}" value="{{ old($translation['lang'] . '_short_description', null) }}" class="form-control">
                                    </div>

                                    <div class="mb-3">
                                        <label for="{{ $translation['lang'] . '_long_description' }}">{{ Lang::get('redminportal::forms.description') }}</label>
                                        <textarea name="{{ $translation['lang'] . '_long_description' }}" id="{{ $translation['lang'] . '_long_description' }}" class="form-control" style="height:200px">{{ old($translation['lang'] . '_long_description', null) }}</textarea>
                                    </div>
                                </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">{{ Lang::get('redminportal::forms.module_properties') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="sku">{{ Lang::get('redminportal::forms.sku') }}</label>
                            <input type="text" name="sku" id="sku" value="{{ old('sku', null) }}" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="tags">{{ Lang::get('redminportal::forms.tags_separated_by_comma') }}</label>
                            <input type="text" name="tags" id="tags" value="{{ old('tags', null) }}" class="form-control">
                        </div>
                    </div>
                </div>
                @if (isset($memberships))
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">{{ Lang::get('redminportal::forms.price_list') }}</h4>
                    </div>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>{{ Lang::get('redminportal::forms.membership') }}</th>
                                <th>{{ Lang::get('redminportal::forms.price') }}</th>
                                <th>{{ Lang::get('redminportal::forms.enabled') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($memberships as $membership)
                            <tr>
                                <td>{{ $membership->name }}</td>
                                <td><input type="text" name="{{ 'price_' . $membership->id }}" value="{{ old('price_' . $membership->id, null) }}" class="form-control"></td>
                                <td>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <label class="form-check-label" for="{{ 'price_active_' . $membership->id }}">
                                                <input type="checkbox" name="{{ 'price_active_' . $membership->id }}" value="true" id="{{ 'price_active_' . $membership->id }}" class="form-check-input" @checked(session()->hasOldInput() ? old('price_active_' . $membership->id) : false)>
                                            </label>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">{{ Lang::get('redminportal::forms.medias') }}</h4>
                    </div>
                    <div id="media-wrapper">
                        <div class="card card-body bg-body-tertiary">{{ Lang::get('redminportal::messages.select_category_to_load_media') }}</div>
                    </div>
                </div>
	        </div>
        </div>
    </form>
@stop

@section('footer')
    @parent
    <script src="{{ URL::to('vendor/redooor/redminportal/js/redmin-fileinput.min.js') }}"></script>
    <script>
        !function ($) {
            $(function(){
                // Load medias base on selected category
                function loadMedia() {
                    $selected_val = $('#category_id').val();
                    $('#media-wrapper').empty().load('medias/' + $selected_val);
                }
                loadMedia(); // Initiate media list on load
                $(document).on('click', '.redmin-hierarchy li', function() {
                    loadMedia(); // Load media list on category change
                });
            })
        }(window.jQuery);
    </script>
    @include('redminportal::plugins/editor')
    @include('redminportal::plugins/tagsinput')
@stop
