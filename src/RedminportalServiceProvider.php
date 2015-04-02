<?php namespace Redooor\Redminportal;

use Illuminate\Support\ServiceProvider;

class RedminportalServiceProvider extends ServiceProvider {

	/**
	 * Bootstrap the application services.
	 *
	 * @return void
	 */
	public function boot()
	{
        //$this->app->register('Maatwebsite\Excel\ExcelServiceProvider');
        //\Event::listen('Excel', 'Maatwebsite\Excel\ExcelServiceProvider');
        
        // Get routes
		include __DIR__.'/app/Http/routes.php';
        
        // Get views
        $this->loadViewsFrom(__DIR__.'/resources/views', 'redminportal');
        
        // Establish Translator Namespace
        $this->loadTranslationsFrom(__DIR__.'/resources/lang', 'redminportal');
        
        // Merging package's config with app's config
        $this->mergeConfigFrom(__DIR__.'/config/image.php', 'redminportal::image');
        $this->mergeConfigFrom(__DIR__.'/config/menu.php', 'redminportal::menu');
        $this->mergeConfigFrom(__DIR__.'/config/translation.php', 'redminportal::translation');
        
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
		$this->app->booting(function()
        {
            $loader = \Illuminate\Foundation\AliasLoader::getInstance();
            $loader->alias('Redminportal', 'Redooor\Redminportal\Facades\Redminportal');
			//$loader->alias('Excel', 'Maatwebsite\Excel\Facades\Excel');
        });
	}

}
