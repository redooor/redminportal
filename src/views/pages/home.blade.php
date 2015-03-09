@extends('redminportal::layouts.master')

@section('content')
    <div class='row'>
        <div class='col-md-8'>
            <h1>RedminPortal <small>by Redooor</small></h1>
            <h2><u>Change log</u></h2>
            <h3>Under Development, Version 0.2.0 (latest master)</h3>
            <p>Focus on supporting Laravel 5.0.</p>
            <h3>Version 0.1.5</h3>
            <p>Focus on fixing bugs and cleaning up code. Improve assets management via Grunt and Bower. Add Coupon module.</p>
            <h4>Important:</h4>
            <p>If you're upgrading from &lt;= v0.1.4, please refer to the Upgrade Guide in README.md.</p>
            <h4>Bug fixes:</h4>
            <ol>
                <li>PurchaseControllerTest is producing an error (issue #48).</li>
                <li>PHPUnit test run out of memory issue, increase from 256MB to 400MB (issue #49).</li>
                <li>orchestra/testbench is using developer branch (issue #50).</li>
                <li>PricelistController has been changed to DiscountController (issue #55).</li>
                <li>admin/discounts does not show correct display count (issue #33).</li>
                <li>Unable to delete discount from UI due to fix for issue #55 (issue #57).</li>
                <li>Sorting capability for Users page (issue #70).</li>
                <li>Sorting capability for Group page (issue #71).</li>
                <li>Add relationship in classes User and Group (issue #72).</li>
                <li>More coming... (developing)</li>
            </ol>
            <h4>New feature:</h4>
            <p>Discount module is restricted to membership/module (pricelist) and does not allow usage limit.</p>
            <p>Coupon module was created to replace Discount module and to provide greater flexibility.</p>
            <ol>
                <li>New Coupon module will replace Discount module (issue #62).</li>
                <li>Ability to add, edit and delete Coupon module (issue #62).</li>
                <li>Coupon module supports adding coupon for category, product and membership/module (pricelist).</li>
                <li>Coupon module to allow per coupon limit (issue #18).</li>
                <li>Coupon module to allow per user limit (issue #19).</li>
                <li>Coupon module to include a start date (issue #56).</li>
                <li>Coupon end date should allow specifying time (issue #58).</li>
            </ol>
            <h4>Note for Contributors</h4>
            <p>All assets are now managed via Grunt and Bower. Please refer to <strong>Install Grunt and Bower dependencies</strong> in the README.md file.</p>
            <h3>Version 0.1.4</h3>
            <p>Released for a major bug fix related to MySQL database and a new feature to allow same sub-category names under different parent.</p>
            <h4>Important:</h4>
            <p>If you're upgrading from &lt;= v0.1.3, please refer to the Upgrade Guide.</p>
            <h4>Bug fixes:</h4>
            <ol>
                <li>Error occurs when create new category, MySQL cannot accept 0 as the foreign key (issue #37).</li>
                <li>Categories is not showing in the products and modules creation sections (issue #38).</li>
            </ol>
            <h4>New feature:</h4>
            <ol>
                <li>Allow same sub-category names under different parent (issue #40).</li>
            </ol>
            <h3>Version 0.1.3</h3>
            <p>The focus of this update was on cleaning up the code and making sure all tests pass.</p>

            <h4>New features:</h4>
            <ol>
                <li>Create and Delete Purchases (issue #23).</li>
                <li>Add Edit and Delete functions to GroupController (issue #30).</li>
                <li>Add Edit function to UserController (issue #31).</li>
            </ol>
            <h4>Enhancements:</h4>
            <ol>
                <li>Use Laravel lists for select input instead of foreach (issue #6).</li>
                <li>Category view page change to hierarchical tree view (issue #12).</li>
                <li>MediaController sort by created_at before parent and name (issue #16).</li>
                <li>Need test for MailinglistController sort function (issue #17).</li>
                <li>Remove the Javascript which add Bootstrap pagination class to ul.pagination (issue #20).</li>
                <li>Users view page has no pagination (issue #21).</li>
                <li>Groups view page has no pagination (issue #22).</li>
                <li>Change test folder names for clarity (issue #26).</li>
                <li>Create test cases for all models (issue #27).</li>
                <li>Create test cases for all controllers (issue #28).</li>
            </ol>
            <h4>Bug fixes:</h4>
            <ol>
                <li>Category view when empty not showing correct message (issue #13).</li>
                <li>AJAX UI delete button doesn't prompt warning (issue #14).</li>
                <li>Prevent same user and pricelist to be added to Purchases (issue #24).</li>
                <li>All view pages where no record is found, don't show table (issue #25).</li>
            </ol>
            <h3>Version 0.1.2</h3>
            <h4>Enhancements:</h4>
            <ol>
                <li>Temporarily increase memory to 256MB for testing.</li>
                <li>Users View page: show Groups instead of Permission.</li>
                <li>Added section head in master layout blade. Can be used by Views to add in styles or scripts in the header without changing the Layout.</li>
                <li>Consolidated Tinymce into a separate view under Plugins folder (issue #1).</li>
                <li>Minor UI change: autofocus email input on login page.</li>
                <li>Category select list changed to hierarchical list (issue #11).</li>
            </ol>
            <h4>Bug fixes:</h4>
            <ol>
                <li>Resolved missing translation text on user login and creation page.</li>
                <li>Hide menu from unauthorised user.</li>
                <li>Resolved issue #3 where login catch exception not working.</li>
                <li>Resolved issue #10 where media controller change category unable to move existing files.</li>
            </ol>
            <h4>Pull requests:</h4>
            <ol>
                <li>Fixed migration foreign reference by @tusharvikky</li>
            </ol>
            <h3>Version 0.1.1</h3>
            <ol>
                <li>Supports Laravel 4.2.</li>
                <li>Moved Twitter Bootstrap to require-dev.</li>
                <li>Dynamically creates in-memory sqlite database for test.</li>
                <li>Fixed an issue when accessing admin/login directly.</li>
                <li>Added translation zh-tw and zh-cn to login page and main menus.</li>
                <li>Resolved issue where package Config cannot be overriden.</li>
                <li>Temporarily increase memory to 256MB for testing.</li>
            </ol>
            <h3>Version 0.1.0</h3>
            <ol>
                <li>Supports Laravel 4.1.</li>
                <li>Added PHPUnit testing and fixed Model test errors.</li>
                <li>Menu view can be controlled via config/menu.php file.</li>
                <li>UI improvement.</li>
                <li>Added Purchase history page.</li>
                <li>Media supports huge file upload (via a plugin).</li>
                <li>Able to activate user from admin portal.</li>
                <li>Added ability to insert picture in Announcement page.</li>
                <li>Added downloadable CSV reports for Purchases and Mailinglist.</li>
                <li>Many bug fixes.</li>
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
        </div>
        <div class='col-md-4'>
            <div class="panel">
                <div class="panel-body">
                    {{ HTML::image( URL::to('packages/redooor/redminportal/assets/img/redminportal_logo.png'), 'RedminPortal', array('class' => 'img-responsive pull-right') )}}
                    <h3>Contribution</h3>
                    <p>Source code at <a href="https://github.com/redooor/redminportal">Github</a></p>
                    <h3>Maintenance</h3>
                    <p>Maintained by the core team with the help of our contributors.</p>
                    <h3>License</h3>
                    <p>RedminPortal is open-sourced software licensed under the <a href="http://opensource.org/licenses/MIT">MIT license</a>.</p>
                </div>
            </div>
            
        </div>
    </div>
@stop
