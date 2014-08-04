@extends('redminportal::layouts.master')

@section('content')
    <div class="row">
        <div class="col-md-12 text-center">
            {{ HTML::image( URL::to('packages/redooor/redminportal/assets/img/redminportal_logo.png'), 'RedminPortal', array('class' => 'img-responsive') )}}
        </div>
    </div>
    <div class='row'>
        <div class='col-md-9'>
            <h1>RedminPortal <small>by Redooor</small></h1>
            <h2><u>Change log</u></h2>
            <h3>Version 0.1.0</h3>
            <ol>
                <li>Supports Laravel 4.1</li>
                <li>Added PHPUnit testing and fixed Model test errors.</li>
                <li>Menu view can be controlled via config/menu.php file.</li>
                <li>UI improvement.</li>
                <li>Added Purchase history page.</li>
                <li>Media supports huge file upload (via a plugin).</li>
                <li>Able to activate user from admin portal.</li>
                <li>Added ability to insert picture in Announcement page.</li>
                <li>Added downloadable CSV reports for Purchases and Mailinglist.</li>
                <li>Many bug fixes.
            </ol>
            <h4>Added the following new Models</h4>
            <ol>
                <li>Price list for Media and Membership</li>
                <li>Discount table</li>
                <li>Media</li>
                <li>Membership</li>
                <li>Module</li>
                <li>Mailinglist</li>
            </ol>
            <h3>Version 0.0.3</h3>
            <ol>
                <li>Bump up version to 1</li>
                <li>Upgraded Bootstrap to 3.1.1</li>
                <li>Upgraded jQuery to 1.11.1</li>
                <li>Changed Composer to get stable versions</li>
                <li>Branch Version 2 from here</li>
            </ol>
            <h3>Version 0.0.2</h3>
            <ol>
                <li>Added HTML WYSIWYG editor for Portfolio Create page Description field</li>
                <li>Added HTML WYSIWYG editor for Product Create and Edit page Description field</li>
                <li>Fixed image issue for Announcements/News page</li>
                <li>Fixed private Announcements/News still showing issue</li>
                <li>Refractoring: clean up routes, move login logic out of User Controller into Login Controller</li>
                <li>Upgraded Bootstrap to 3.0.3</li>
            </ol>
            <h3>Version 0.0.1</h3>
            <ol>
            	<li>Fixed image issue for Announcements/News page</li>
            	<li>Fixed private Announcements/News still showing issue</li>
            	<li>Added HTML WYSIWYG editor for Portfolio Description field</li>
            	<li>Added function to delete image from Annoncements/News</li>
            </ol>
            <h3>Version 0.0.0</h3>
            <ol>
            	<li>Separated Redmin Portal code to its own package</li>
            	<li>Coined the term &quot;Redmin Portal&quot;</li>
                <li>Using Bootstrap v3</li>
                <li>Better user interface such as using tabs to show different languages</li>
                <li>Added pagnation for products, categories and promotions pages</li>
                <li>Added date picker and file uploader javascripts</li>
                <li>Added WYSIWYG editor for promotion description</li>
                <li>Verify password for user creation</li>
                <li>Able to add and edit users, groups, categories, products and promotions</li>
                <li>Translation capability for Simplified Chinese</li>
            </ol>
        </div>
        <div class='col-md-3'>
            <h3>Contribution</h3>
            Source code at <a href="https://github.com/redooor/redminportal">Github</a>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            Maintained by the core team with the help of our contributors.<br>
            RedminPortal is open-sourced software licensed under the <a href="http://opensource.org/licenses/MIT">MIT license</a>.
        </div>
    </div>
@stop
