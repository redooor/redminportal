@extends('redminportal::layouts.master')

@section('content')
    <div class='row'>
        <div class='col-md-8'>
            <div class="panel panel-default">
                <div class="panel-body">
                    <h1>RedminPortal <small>by Redooor</small></h1>
                    <img src="https://travis-ci.org/redooor/redminportal.svg?branch=develop-v0.2">
                    <h2><u>Change log</u></h2>
                    <p>Version 0.2 and 0.3 are developed in parallel. The only difference between them is the Laravel version they support. However, this may change in future.</p>
                    <h3>Version 0.3.3 and 0.2.3</h3>
                    <p>This update introduces some new tables and lots of changes.</p>
                    <p><strong>IMPORTANT:</strong> Refer to UPGRADE.md for the upgrading instructions.</p>
                    <h4>New features:</h4>
                    <ol>
                        <li>Added product variations. You can now add variations to each product. (issue #125)</li>
                        <li>Added reusable partial templates for Modal Window and Language selector tab. (issue #130)</li>
                        <li>Added shipping properties to products: Weight, Length, Width, Height, units. (issue #126)</li>
                        <li>Allow Tinymce content to follow front end CSS. (issue #143)</li>
                        <li>Moved pagination to config file so developer can set the default pagination size. (issue #121)</li>
                        <li>Usable Redminportal Facade for HTML helpers.</li>
                        <li>Added Sorting capability for (issue #15):
                            <ul>
                                <li>Orders</li>
                                <li>Announcements</li>
                                <li>Pages</li>
                                <li>Posts</li>
                                <li>Portfolios</li>
                                <li>Promotions</li>
                                <li>Products</li>
                                <li>Memberships</li>
                                <li>Modules</li>
                                <li>Medias</li>
                            </ul>
                        </li>
                        <li>Added capability to sort by Category for Bundle. (issue #15)</li>
                        <li>Improve Tagging (issue #147)
                            <ul>
                                <li>Added Typeahead for tag suggestion</li>
                                <li>Use label visual for tags</li>
                            </ul>
                        </li>
                        <li>Added API for retrieving tag names
                            <ul>
                                <li>/api/tag: Get JSON list of tags with id and name</li>
                                <li>/api/tag/name: Get JSON list of tags with name only</li>
                                <li>Check <a href="https://github.com/redooor/redminportal/wiki/Public-API">list of API on github Wiki page</a></li>
                            </ul>
                        </li>
                        <li>Able to add user to multiple groups. (issue #53)</li>
                        <li>Introduced HTML and Form helpers
                            <ul>
                                <li>HTML and Form helpers provide a shortcut to partial views.</li>
                                <li>Check <a href="https://github.com/redooor/redminportal/wiki/HTML-and-Form-Helpers">list of helpers on github Wiki page</a></li>
                            </ul>
                        </li>
                        <li>Added tag to Page and Post (issue #146)</li>
                        <li>Added API for retrieving emails for authenticated admin users only
                            <ul>
                                <li>/api/email: Unused, returns an empty JSON list</li>
                                <li>/api/email/all: Get JSON list of emails</li>
                                <li>Check <a href="https://github.com/redooor/redminportal/wiki/Private-API">list of Private API on github Wiki page</a></li>
                            </ul>
                        </li>
                        <li>UI: Group permission management, able to edit group permission for any route. (issue #69)</li>
                        <li>UI: User permission management, able to edit user permission for any route. (issue #69)</li>
                        <li>Search order by transaction id and more! (issue #137)</li>
                        <li>Search users by group, first name, last name, email and more!</li>
                        <li>Able to change Order statuses with tracking changes in Order model. (issue #138)</li>
                        <li>Added reusable Revision and Revisionable trait for tracking of changes in any model. (issue #138)</li>
                        <li>Added UI for viewing Revision History for Orders.</li>
                    </ol>
                    <h4>Enhancements:</h4>
                    <ol>
                        <li>Category model missing relationships with other models. (issue #140)</li>
                        <li>Fixed UI: Category hierarchy list word wrap. (issue #133)</li>
                        <li>Developer: use grunt to publish assets to public folder automatically. (issue #139)</li>
                        <li>Moved static text to translation file. (issue #102)</li>
                        <li>Add getTotaldiscount method to Order. (issue #142)</li>
                        <li>Improved Tinymce editor to match RedminPortal look and feel.</li>
                        <li>Updated Tinymce to version 4.3.2.</li>
                        <li>Create Category Select reusable form (issue #130)</li>
                        <li>Rearranged menu to push User Management to bottom.</li>
                        <li>Increase default pagination to 50. (issue #121)</li>
                        <li>Introduced SorterController trait to reduce code for sortable pages.</li>
                        <li>Introduced partial Blade template for sortable header.</li>
                        <li>Overall Line count decreased from 23031 to 22805 with trait and partial template.</li>
                        <li>Improve Usability: after delete action should go back to last visited page (issue #131)
                            <ul>
                                <li>Announcement</li>
                                <li>Bundle</li>
                                <li>Category</li>
                                <li>Coupon</li>
                                <li>Group</li>
                                <li>Mailinglist</li>
                                <li>Media</li>
                                <li>Membership</li>
                                <li>Module</li>
                                <li>Order</li>
                                <li>Page</li>
                                <li>Portfolio</li>
                                <li>Post</li>
                                <li>Product</li>
                                <li>Promotion</li>
                                <li>Purchase</li>
                                <li>User</li>
                            </ul>
                        </li>
                        <li>Improved Permission management, allow granular control for every page and action. (issue #69)
                            <ul>
                                <li>When users are deactivated after they login, they should be logged out and require re-login.</li>
                                <li>Various actions for create, update and delete are added in the permission checker.</li>
                            </ul>
                        </li>
                        <li>Improved search engine to be reusable.</li>
                        <li>Payment Statuses can now be edited via config file. See UPGRADE.md for instructions.</li>
                    </ol>
                    <h4>Bug fixes:</h4>
                    <ol>
                        <li>User deletion: prevent user from deleting or deactivating own account while they are logged in. (issue #136)</li>
                        <li>UI: Page and Post cannot change category back to No Category. (issue #145)</li>
                        <li>Fix Tinymce editor not showing Bootstrap components correctly by adding container-fluid class to body.</li>
                        <li>Fix error message when deleting Category in used. Deleting Category will delete all related data. (issue #135)</li>
                        <li>Fix Module on change category doesn't load media. (issue #150)</li>
                        <li>Click menu overlay to close doesn't work on iPad. (issue #151)</li>
                        <li>Fix Build error for PHP7 due to getID3 package. Switch to JamesHeinrich/getID3. (issue #152)</li>
                        <li>Added delete image functionality to Bundles. (issue #149)</li>
                        <li>Fixed Typeahead styling messed up for email suggestions. (issue #156)</li>
                        <li>Upload image with same name will now be appended with number. (issue #154)</li>
                        <li>Product variant create/edit form on error doesn't populate previous inputs. (issue #134)</li>
                    </ol>
                    <hr>
                    <h3>Version 0.3.2 and 0.2.2</h3>
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
                    <hr>
                    <h3>Version 0.3.1 and 0.2.1</h3>
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
                    <h3>Version 0.2.0</h3>
                    <p>Focus on supporting Laravel 5.0.</p>
                    <h4>New feature:</h4>
                    <ol>
                        <li>Support Laravel 5.0.</li>
                        <li>Removed dependancy of Cartalyst/Sentry.</li>
                        <li>Use Laravel's Authentication instead of Cartalyst/Sentry.</li>
                        <li>Users and Groups tables should work with previous schema. (But need to add column remember_token in users table)</li>
                        <li>Drop Discount module support from v0.2.0 onwards (issue #63)</li>
                        <li>Changed UI to use [Redmaterials](http://redmaterials.redooor.com) theme.</li>
                        <li>Removed Bootstrap from redminportal.css. It should be linked separately.</li>
                        <li>Consistent UI across all views (issue #76).</li>
                        <li>Add active column to Pricelists table (issue #78).</li>
                        <li>Add UI to views for additional active column in Pricelists table (issue #78).</li>
                        <li>Rearrange menu to split Physical product store and Digital product store (issue #52).</li>
                        <li>Promotions, Products and Portfolios now allow translation (issue #9).</li>
                        <li>Use Laravel authentication, removed Sentry dependency (issue #81).</li>
                        <li>Allow multiple Category images (issue #79).</li>
                        <li>Add Post Model and Controller (issue #73).</li>
                        <li>Revamp Translation implementation for Category, Media, Module, Portfolio, Product and Promotion (issue #85).</li>
                        <li>Add Page model and Controller (issue #74).</li>
                        <li>Save orders for products (issue #51).</li>
                        <li>Download orders in CSV format.</li>
                    </ol>
                    <h4>Bug fixes:</h4>
                    <ol>
                        <li>Translation to support any language (issue #83).</li>
                    </ol>
                    <hr>
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
