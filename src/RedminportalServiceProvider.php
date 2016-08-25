<?php namespace Redooor\Redminportal;

use Illuminate\Support\ServiceProvider;
use Redooor\Redminportal\App\Classes\Redminportal;

class RedminportalServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // Get routes
        include __DIR__.'/App/Http/routes.php';
        
        // Get views
        if (file_exists(base_path('resources/views/vendor/redooor/redminportal'))) {
            $this->loadViewsFrom(base_path('resources/views/vendor/redooor/redminportal'), 'redminportal');
        } else {
            $this->loadViewsFrom(__DIR__.'/resources/views', 'redminportal');
        }
        
        // Establish Translator Namespace
        $this->loadTranslationsFrom(__DIR__.'/resources/lang', 'redminportal');
        
        // Allow end users to publish and modify views
        $this->publishes([
            __DIR__.'/resources/views/' => base_path('resources/views/vendor/redooor/redminportal/'),
        ], 'views');
        
        // Allow end users to publish and modify public assets
        $this->publishes([
            __DIR__.'/public/' => public_path('vendor/redooor/redminportal/'),
        ], 'public');
        
        // Publish a config file
        $this->publishes([
            __DIR__.'/config/' => config_path('vendor/redooor/redminportal/')
        ], 'config');
        
        // Publish your migrations
        $this->publishes([
            __DIR__.'/database/migrations/' => base_path('database/migrations/vendor/redooor/redminportal/')
        ], 'migrations');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        // Load autoload for package development environment only
        $autoloader = __DIR__ . '/../vendor/autoload.php';
        if (file_exists($autoloader)) {
            require_once $autoloader;
        }
        
        /**
         * Include getID3 dependencies
         * If app vendor folder exists, use that.
         * Otherwise use development vendor folder
         **/
        if (file_exists(base_path('vendor/james-heinrich/getid3/getid3/getid3.php'))) {
            include_once base_path('vendor/james-heinrich/getid3/getid3/getid3.php');
        } else {
            include_once __DIR__ . "/../vendor/james-heinrich/getid3/getid3/getid3.php";
        }
        if (file_exists(base_path('vendor/james-heinrich/getid3/getid3/write.php'))) {
            include_once base_path('vendor/james-heinrich/getid3/getid3/write.php');
        } else {
            include_once __DIR__ . "/../vendor/james-heinrich/getid3/getid3/write.php";
        }
        
        $this->bindSharedInstances();
        
        $this->app->register('Collective\Html\HtmlServiceProvider');
        $this->app->register('Orchestra\Imagine\ImagineServiceProvider');
        $this->app->register('Maatwebsite\Excel\ExcelServiceProvider');
        
        $this->app->booting(function () {
            $loader = \Illuminate\Foundation\AliasLoader::getInstance();
            $loader->alias('Redminportal', 'Redooor\Redminportal\App\Facades\Redminportal');
            $loader->alias('Form', 'Collective\Html\FormFacade');
            $loader->alias('HTML', 'Collective\Html\HtmlFacade');
            $loader->alias('Imagine', 'Orchestra\Imagine\Facade');
            $loader->alias('Excel', 'Maatwebsite\Excel\Facades\Excel');
        });
        
        $this->registerResources('image', 'redminportal::image');
        $this->registerResources('menu', 'redminportal::menu');
        $this->registerResources('translation', 'redminportal::translation');
        $this->registerResources('tinymce', 'redminportal::tinymce');
        $this->registerResources('pagination', 'redminportal::pagination');
        $this->registerResources('permissions', 'redminportal::permissions');
        $this->registerResources('payment_statuses', 'redminportal::payment_statuses');
        
        // Change Authentication model
        $this->registerResources('auth', 'auth');
    }
    
    /**
     * Register the package resources.
     *
     * @return void
     */
    protected function registerResources($name, $setname)
    {
        $userConfigFile    = config_path('vendor/redooor/redminportal/' . $name . '.php');
        $packageConfigFile = __DIR__.'/config/' . $name . '.php';
        $config            = $this->app['files']->getRequire($packageConfigFile);

        if (file_exists($userConfigFile)) {
            $userConfig = $this->app['files']->getRequire($userConfigFile);
            $config     = $userConfig;
        }

        $this->app['config']->set($setname, $config);
    }
    
    /**
     * Bind all shared instances.
     *
     * @return void
     */
    protected function bindSharedInstances()
    {
        $this->app->bindShared('redminportal', function ($app) {
            return new Redminportal($app['url']);
        });
    }
}
