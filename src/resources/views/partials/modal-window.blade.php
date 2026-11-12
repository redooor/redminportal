{{--
    Modal Window template for reuse
    --------------------------------
    Usage Example:
    --------------------------------
    @include('redminportal::partials.modal-window', [
        'modal_id' => 'modal-unique-id',
        'modal_title' => 'Some title',
        'modal_body' => 'Some body',
        'modal_footer' => 'Some footer',
        'modal_size' => 'modal-lg',
        'modal_progress' => 'modal-progress-unique-id'
    ])
--}}
<div id="{{ $modal_id }}" class="modal fade" tabindex="-1">
    <div class="modal-dialog {{ $modal_size ?? '' }}">
        @if (isset($modal_progress))
        <div class="modal-progress">
            <div class="progress">
                <div id="{{ $modal_progress }}" class="progress-bar bg-info progress-bar-striped" 
                     role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
                    <span class="visually-hidden">Loading</span>
                </div>
            </div>
        </div>
        @endif
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ $modal_title }}</h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ Lang::get('redminportal::buttons.close') }}"></button>
            </div>
            <div class="modal-body">
                {!! $modal_body !!}
            </div>
            @if (isset($modal_footer))
            <div class="modal-footer">
                {!! $modal_footer !!}
            </div>
            @endif
        </div>{{-- modal-content --}}
    </div>{{-- modal-dialog --}}
</div>{{-- modal --}}
