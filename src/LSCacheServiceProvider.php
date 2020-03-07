<?php

namespace Litespeed\LSCache;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use Illuminate\Contracts\Http\Kernel;

class LSCacheServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(Router $router, Kernel $kernel)
    {
        $router->aliasMiddleware('lscache', LSCacheMiddleware::class);
        $router->aliasMiddleware('lstags', LSTagsMiddleware::class);
        $kernel->pushMiddleware(LSCacheMiddleware::class);

        $this->publishes([
            __DIR__ . '/../config/lscache.php' => config_path('lscache.php'),
        ], 'config');
    }
}
