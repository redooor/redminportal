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
        // Get routes
		include __DIR__.'/app/Http/routes.php';
        
        // Get views
        $this->loadViewsFrom(__DIR__.'/resources/views', 'redminportal');
        
        // Allow end users to publish and modify views
        $this->publishes([
            __DIR__.'/resources/views' => base_path('resources/views/vendor/redminportal'),
        ]);
	}

	/**
	 * Register the application services.
	 *
	 * @return void
	 */
	public function register()
	{
		//
	}

}
