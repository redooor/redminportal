@extends('redminportal::layouts.master')

@section('content')
    <div class='row'>
        <div class='col-md-8'>
            <div class="panel panel-default">
                <div class="panel-body">
                    <h1>RedminPortal <small>by Redooor</small></h1>
                    <h2><u>Change log</u></h2>
                    <h3>Version 0.3.3 and 0.2.3</h3>
                    <p>Code clean up, new features and UI improvements.</p>
                    <p>This update introduces some new tables. Refer to UPGRADE.md for the upgrading instructions.</p>
                    <h4>New features:</h4>
                    <ol>
                        <li>Added product variations. You can now add variations to each product. (issue #125)</li>
                        <li>Added reusable partial templates for Modal Window and Language selector tab. (issue #130)</li>
                        <li>Added shipping properties to products: Weight, Length, Width, Height, units. (issue #126)</li>
                    </ol> 
                    <h3>Version 0.3.2</h3>
                    <p>Code clean up, new features and UI improvements.</p>
                    <p>This update is generally v0.3.* compatible but there's a change with UserPricelist. Refer to UPGRADE.md for the upgrading instructions.</p>
                    <h4>Enhancement:</h4>
                    <ol>
                        <li>Move Purchases into Orders (issue #95).</li>
                        <li>Download Orders report as Excel format xlsx (issue #114).</li>
                        <li>Search user by email or name (issue #68).</li>
                        <li>Users pagination increased to 50 per page (issue #121).</li>
                        <li>User creation to check unique email and prompt error (issue #120).</li>
                        <li>Improve UI: move breadcrumb to unused menu bar area (issue #103).</li>
                    </ol>
                    <h4>Bug fixes:</h4>
                    <ol>
                        <li>(legacy support) Purchase export to excel not working. Purchase has deprecated. Use Orders instead. (issue #115)</li>
                    </ol>
                    <h3>Version 0.3.1</h3>
                    <p>New Bundle module and improvement to UI.</p>
                    <h4>New feature:</h4>
                    <ol>
                        <li>Bundle module allow bundling physical and digital products on a single price (issue #94).</li>
                    </ol>
                    <h4>Enhancement:</h4>
                    <ol>
                        <li>Added migrations to upgrade database from v0.1 to v0.2/v0.3 (issue #89).</li>
                        <li>Sidebar is now off-canvas (issue #91).</li>
                    </ol>
                    <h4>Bug fixes:</h4>
                    <ol>
                        <li>Resolves TokenMismatchException issue on login (issue #88).</li>
                        <li>User config file will override the default package config file (issue #90).</li>
                    </ol>
                    <hr>
                    <h3>Version 0.3.0</h3>
                    <p>Focus on supporting Laravel 5.1 (issue #87).</p>
                    <h4>Important:</h4>
                    <p>If you're upgrading from v0.2.0, please refer to the Upgrade Guide in README.md.</p>
                    <hr>
                    <h3>Version 0.2.*</h3>
                    <p>Focus on supporting Laravel 5.0 and making sure all models and controllers work.</p>
                    <p>Version 0.2.0 is <strong>SOMEWHAT</strong> backward compatible.</p>
                    <p>If you're upgrading from v0.1.*, please refer to the <a href="https://github.com/redooor/redminportal/blob/v0.2/UPGRADE.md">branch v0.2 Upgrade Guide</a>.</p>
                    <p>Refer to <a href="https://github.com/redooor/redminportal/blob/v0.2/CHANGELOG.md">branch v0.2 CHANGELOG.md</a></p>

                    <h3>Version 0.1.*</h3>
                    <p>Supports Laravel 4.2.</p>
                    <p>Refer to <a href="https://github.com/redooor/redminportal/blob/v0.1/README.md#change-log">branch v0.1 README.md Changelog</a></p>
                </div>
            </div>
        </div>
        <div class='col-md-4'>
            <div class="well well-sm shadow-depth-1">
                <img src="{{ URL::to('vendor/redooor/redminportal/img/redminportal_logo.png') }}" title="ReminPortal" class="img-responsive pull-right">
                <h3>Contribution</h3>
                <p>Source code at <a href="https://github.com/redooor/redminportal">Github</a></p>
                <h3>Maintenance</h3>
                <p>Maintained by the core team with the help of our contributors.</p>
                <h3>License</h3>
                <p>RedminPortal is open-sourced software licensed under the <a href="http://opensource.org/licenses/MIT">MIT license</a>.</p>
            </div>
        </div>
    </div>
@stop
