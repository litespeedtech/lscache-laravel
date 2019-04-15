<?php

namespace Litespeed\LSCache;

use Illuminate\Support\ServiceProvider;

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
    public function boot(\Illuminate\Routing\Router $router, \Illuminate\Contracts\Http\Kernel $kernel)
    {
        $router->aliasMiddleware('lscache', \Litespeed\LSCache\LSCacheMiddleware::class);
        $router->aliasMiddleware('lstags', \Litespeed\LSCache\LSTagsMiddleware::class);
        $kernel->pushMiddleware(\Litespeed\LSCache\LSCacheMiddleware::class);
        $this->publishes([
            __DIR__ . '/../config/lscache.php' => config_path('lscache.php'),
        ], 'config');
    }
}
