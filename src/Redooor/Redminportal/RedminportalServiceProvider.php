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

		// Get config loader
		$loader = $this->app['config']->getLoader();

		// Get environment name
		$env = $this->app['config']->getEnvironment();

		// Add package namespace with path set base on your requirement
		$loader->addNamespace('redminportal',__DIR__.'/../../config');

		// Load package override config file
		$menu = $loader->load($env,'menu','redminportal');
		$image = $loader->load($env,'image','redminportal');
		$translation = $loader->load($env,'translation','redminportal');

		// Override value
		$this->app['config']->set('redminportal::menu',$menu);
		$this->app['config']->set('redminportal::image',$image);
		$this->app['config']->set('redminportal::translation',$translation);
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
