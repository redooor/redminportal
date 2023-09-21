# Contributing to Redooor Redmin Portal

Your contributions to the project are most welcome. Please review these guidelines before submitting any pull requests to the project.

# Which Branch?

ALL bug fixes should be made to the 0.x branch which they belong. Bug fixes should never be sent to the master branch unless they fix features that exist only in the upcoming release.

# Pull Requests

The pull request process differs for new features and bugs. Before sending a pull request for a new feature, you should first create an issue with [Proposal] in the title. The proposal should describe the new feature, as well as implementation ideas. The proposal will then be reviewed and either approved or denied. Once a proposal is approved, a pull request may be created implementing the new feature. Pull requests which do not follow this guideline will be closed immediately.

Pull requests for bugs may be sent without creating any proposal issue. If you believe that you know of a solution for a bug that has been filed on GitHub, please leave a comment detailing your proposed fix.

# Feature Requests

If you have an idea for a new feature you would like to see added to RedminPortal, you may create an issue on GitHub with [Request] in the title. The feature request will then be reviewed by @kongnir.

# House Rules

* Describe the problem clearly in the Pull Request description
* If you are submitting a bug-fix, or an enhancement that is not a breaking change, submit your pull request to the branch corresponding to the latest stable release of the project, such as the 0.1 branch.
* If you are submitting a breaking change or an entirely new component, submit your pull request to the master branch.
* Do not edit compiled asset files such as redooor.css directly. Instead, please edit the LESS files inside the src/less/ directory and then use a compiler. Such submission should include both the less file and the css file.
* For any change that you make, please try to add a test case(s) in the tests/unit or tests/integration directory. This helps us understand the issue and make sure that it will stay fixed forever.

# PSR Coding standards

Also ensure that your Pull Request satisfies the following coding standards:

* [PSR 4 Autoloader](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md)
* [PSR 1 Basic Coding Standard](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md)
* [PSR 2 Coding Style Guide](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)

As of RedminPortal 0.2.0, we're changing our autoloading standard from PSR-0 to PSR-4 in order to match the same in Laravel 5.*.

Please use [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer) to detect violations of coding standard.

If you're using [Adobe Brackets](http://brackets.io), you may want to install this extension: [Brackets PHP Code Quality Tools](https://github.com/mikaeljorhult/brackets-php-code-quality-tools).

Also read [Optional Development Tools](#optional-development-tools) below.

# PHPUnit Test

Every method in controller or model must have a test case.

* In packages\redooor\redminportal folder, run 

        ?> composer update --prefer-dist -vvv --profile
        ?> vendor/bin/phpunit

    **NOTE: If you run out of memory while running the full tests, try running the tests by sub-folders.**
    
        ?> vendor/bin/phpunit tests/models/
        ?> vendor/bin/phpunit tests/controllers/
        ?> vendor/bin/phpunit tests/relationships/

        ?> ./vendor/bin/phpunit --testsuite "suite1","suite2"
        ?> ./vendor/bin/phpunit --testsuite "suite3","suite4"

## Model
* All tests related to model must be store within tests/models folder. 
* File name must be {ModelName}ModelTest.php.
* All model tests must inherit parent class BaseModelTest (which resides in tests/bases folder).
* You may include other tests in the model test itself if it's not tested in base class.

## Controller
* All tests related to controller must be store within tests/controllers folder. 
* File name must be {ControllerName}Test.php.
* All controller tests must inherit parent class BaseControllerTest (which resides in tests/bases folder).
* You may include other tests in the controller test itself if it's not tested in base class.

# Documentation

All contributions are most welcome.
All deployment-related documentation should be done within our Wiki page.

# Optional Development Tools

We're using these tools in our development of RedminPortal. It'll help if you use the same tool too.
Please feel free to suggest a better alternative.

* [Laravel Herd](https://herd.laravel.com/) for consistent environment.
* [Visual Studio Code](https://code.visualstudio.com/) for text editing.
* [PHP by Devsense](https://marketplace.visualstudio.com/items?itemName=DEVSENSE.phptools-vscode) a PHP plugin for Visual Studio Code.

# Dealing with Case-sensitive naming convention

It was only recently that we found out the huge difference when deploying to our hosting server which was case sensitive. In our vagrant environment, we thought the Ubuntu virtual machine will take care of that. Unfortunately, VirtualBox uses the host machine's file system instead of the Virtual machine's. So we couldn't really get a consistent environment just by using Vagrant.

This link shows a good solution for Mac OSX users. Unfortunately, we couldn't find a suitable solution for Windows users yet.
[https://plog.logston.me/Vagrant-development-environment-on-OSX/](https://plog.logston.me/Vagrant-development-environment-on-OSX/)
