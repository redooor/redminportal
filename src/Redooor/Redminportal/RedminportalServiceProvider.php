<?php namespace Redooor\Redminportal;

use Illuminate\Support\ServiceProvider;

class RedminportalServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

    /**
    * Bootstrap the application events.
    *
    * @return void
    */
    public function boot()
    {
        $this->package('redooor/redminportal');

		$this->app->register('Maatwebsite\Excel\ExcelServiceProvider');
		$this->app->register('Cartalyst\Sentry\SentryServiceProvider');

        include __DIR__.'/../../routes.php';
        include __DIR__.'/../../filters.php';
    }

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app['redminportal'] = $this->app->share(function($app)
        {
            return new Redminportal;
        });

        $this->app->booting(function()
        {
            $loader = \Illuminate\Foundation\AliasLoader::getInstance();
            $loader->alias('Redminportal', 'Redooor\Redminportal\Facades\Redminportal');
			$loader->alias('Excel', 'Maatwebsite\Excel\Facades\Excel');
			$loader->alias('Sentry', 'Cartalyst\Sentry\Facades\Laravel\Sentry');
        });
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('redminportal');
	}

}
