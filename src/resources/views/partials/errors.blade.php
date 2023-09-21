@if (isset($errors))
    @if(count($errors) > 0)
    <div class="row">
        <div class="col-md-12">
            <div class='alert alert-danger'>
                We encountered the following errors:
                <ul>
                    @foreach($errors->all() as $message)
                    <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif
@endif