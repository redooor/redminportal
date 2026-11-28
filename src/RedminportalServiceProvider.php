<?php namespace Redooor\Redminportal;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Redooor\Redminportal\App\Classes\Redminportal;
use Redooor\Redminportal\App\Models\User;
use Illuminate\Routing\Router;

class RedminportalServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(Router $router)
    {
        // Get routes
        $this->loadRoutesFrom(__DIR__.'/App/Http/routes.php');
        
        /**
         * @permitted('admin/pages/edit/5') ... @endpermitted
         * Shows its content only if the signed-in admin may open any of the given paths. It runs the same check as the
         * route middleware, so a button is shown exactly when its link would be allowed.
         */
        Blade::if('permitted', function (...$paths) {
            $user = Auth::guard('redminguard')->user();

            if ($user === null) {
                return false;
            }

            foreach ($paths as $path) {
                if ($user->hasAccess(Request::create($path, 'GET'))) {
                    return true;
                }
            }

            return false;
        });

        /**
         * Gate::allows('redmin', ['admin/pages', 'update']) or @can('redmin', ['admin/pages', 'update'])
         * Answers from the same stored permissions as the route middleware and @permitted, for a path and an action
         * (view, create, update or delete). It answers for the user passed to the Gate (for example
         * Gate::forUser($user)), which must be a Redmin user; when there is no user on the default guard it uses the
         * user signed in on the redminguard.
         */
        Gate::define('redmin', function (?Authenticatable $user, string $path, string $action = 'view') {
            return static::mayAccess($user, $path, $action);
        });

        // Get views
        if (file_exists(base_path('resources/views/vendor/redooor/redminportal'))) {
            $this->loadViewsFrom(base_path('resources/views/vendor/redooor/redminportal'), 'redminportal');
        } else {
            $this->loadViewsFrom(__DIR__.'/resources/views', 'redminportal');
        }
        
        // Establish Translator Namespace
        $this->loadTranslationsFrom(__DIR__.'/resources/lang', 'redminportal');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
        
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

        $router->middlewareGroup('redminsession', [
            // First, so it also covers responses built from exceptions such as the CSRF rejection
            \Redooor\Redminportal\App\Http\Middleware\PreventFraming::class,
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Redooor\Redminportal\App\Http\Middleware\PreventRequestForgery::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        $router->aliasMiddleware('redminauth', \Redooor\Redminportal\App\Http\Middleware\Authenticate::class);
        $router->aliasMiddleware('redminuser', \Redooor\Redminportal\App\Http\Middleware\AuthenticateUser::class);
        $router->aliasMiddleware('redminfingerprint', \Redooor\Redminportal\App\Http\Middleware\EnsureSessionFingerprint::class);
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
        
        $this->app->register('Maatwebsite\Excel\ExcelServiceProvider');
        
        $this->app->booting(function () {
            $loader = \Illuminate\Foundation\AliasLoader::getInstance();
            $loader->alias('Redminportal', 'Redooor\Redminportal\App\Facades\Redminportal');
            $loader->alias('Excel', 'Maatwebsite\Excel\Facades\Excel');
        });
        
        // Register all config files
        $this->registerResources('image', 'redminportal::image');
        $this->registerResources('media', 'redminportal::media');
        $this->registerResources('menu', 'redminportal::menu');
        $this->registerResources('translation', 'redminportal::translation');
        $this->registerResources('tinymce', 'redminportal::tinymce');
        $this->registerResources('pagination', 'redminportal::pagination');
        $this->registerResources('permissions', 'redminportal::permissions');
        $this->registerResources('payment_statuses', 'redminportal::payment_statuses');
        $this->registerResources('password', 'redminportal::password');
        
        // Change Authentication model
        $this->mergeAuthConfig();
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
        $this->app->singleton('redminportal', function ($app) {
            return new Redminportal($app['url']);
        });
    }

    /**
     * Merge user's auth.php with package's.
     */
    private function mergeAuthConfig()
    {
        $userAuthFile = config_path('auth.php');
        $packageAuthFile = __DIR__.'/config/' . 'auth.php';

        if (file_exists($userAuthFile) && file_exists($packageAuthFile)) {
            $authConfig = $this->app['files']->getRequire($userAuthFile);
            $packageAuthConfig = $this->app['files']->getRequire($packageAuthFile);

            $authConfig['guards'] = array_merge($authConfig['guards'], $packageAuthConfig['guards']);
            $authConfig['providers'] = array_merge($authConfig['providers'], $packageAuthConfig['providers']);

            $this->app['config']->set('auth', $authConfig);
        }
    }

    /**
     * Whether a Redmin user may do an action on a path, for the "redmin" gate.
     *
     * @param Authenticatable|null $user The user the Gate resolved. Null (no user on the default guard) means the user
     *                                   signed in on the redminguard; any other user that is not a Redmin user is denied
     * @param string $path Path such as admin/pages, decoded and cleaned the way the router sees it
     * @param string $action view, create, update or delete
     * @return bool
     */
    public static function mayAccess(?Authenticatable $user, string $path, string $action = 'view'): bool
    {
        if ($user === null) {
            $user = Auth::guard('redminguard')->user();
        }

        if (! $user instanceof User || ! $user->activated) {
            return false;
        }

        if (! in_array($action, ['view', 'create', 'update', 'delete'], true)) {
            return false;
        }

        // Same form as Request::decodedPath(), so an encoded path cannot slip past a deny written for the plain one
        $segments = [];

        foreach (explode('/', rawurldecode($path)) as $segment) {
            if ($segment === '.' || $segment === '..') {
                return false;
            }

            if ($segment !== '') {
                $segments[] = $segment;
            }
        }

        if (empty($segments)) {
            return false;
        }

        return $user->hasPermission(implode('/', $segments), $action);
    }
}
