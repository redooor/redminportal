<?php namespace Redooor\Redminportal;

use Illuminate\Support\ServiceProvider;

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
        include __DIR__.'/app/Http/routes.php';
        
        // Get views
        $this->loadViewsFrom(__DIR__.'/resources/views', 'redminportal');
        
        // Establish Translator Namespace
        $this->loadTranslationsFrom(__DIR__.'/resources/lang', 'redminportal');
        
        // Allow end users to publish and modify views
        $this->publishes([
            __DIR__.'/resources/views' => base_path('resources/views/vendor/redooor/redminportal'),
        ]);
        
        // Allow end users to publish and modify public assets
        $this->publishes([
            __DIR__.'/public' => public_path('vendor/redooor/redminportal'),
        ], 'public');
        
        // Publish a config file
        $this->publishes([
            __DIR__.'/config/image.php' => config_path('vendor/redooor/redminportal/image.php'),
            __DIR__.'/config/menu.php' => config_path('vendor/redooor/redminportal/menu.php'),
            __DIR__.'/config/translation.php' => config_path('vendor/redooor/redminportal/translation.php')
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
        
        $this->app->register('Illuminate\Html\HtmlServiceProvider');
        $this->app->register('Orchestra\Imagine\ImagineServiceProvider');
        $this->app->register('Maatwebsite\Excel\ExcelServiceProvider');
        
        $this->app->booting(function() {
            $loader = \Illuminate\Foundation\AliasLoader::getInstance();
            $loader->alias('Redminportal', 'Redooor\Redminportal\Facades\Redminportal');
            $loader->alias('Form', 'Illuminate\Html\FormFacade');
            $loader->alias('HTML', 'Illuminate\Html\HtmlFacade');
            $loader->alias('Imagine', 'Orchestra\Imagine\Facade');
            $loader->alias('Excel', 'Maatwebsite\Excel\Facades\Excel');
        });
        
        $this->registerResources('image');
        $this->registerResources('menu');
        $this->registerResources('translation');
    }
    
    /**
     * Register the package resources.
     *
     * @return void
     */
    protected function registerResources($name)
    {
        $userConfigFile    = config_path('vendor/redooor/redminportal/' . $name . '.php');
        $packageConfigFile = __DIR__.'/config/' . $name . '.php';
        $config            = $this->app['files']->getRequire($packageConfigFile);

        if (file_exists($userConfigFile)) {
            $userConfig = $this->app['files']->getRequire($userConfigFile);
            $config     = array_replace_recursive($config, $userConfig);
        }

        $this->app['config']->set('redminportal::' . $name, $config);
    }
}
