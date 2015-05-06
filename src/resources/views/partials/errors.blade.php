@if (isset($errors))
    @if($errors->has())
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