<?php

namespace Litespeed\LSCache;

use Closure;

class LSCacheMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string $lscache_control
     * @return mixed
     */
    public function handle($request, Closure $next, string $lscache_control = null)
    {
        $response = $next($request);

        if (! $request->isMethodCacheable() || ! $response->getContent()) {
            return $response;
        }

        $esi_enabled    = config('lscache.esi');
        $maxage         = config('lscache.default_ttl');
        $cacheability   = config('lscache.default_cacheability');

        if($maxage === 0 && $lscache_control === null) {
          return $response;
        }

        $lscache_string = "max-age=$maxage,$cacheability" . ($esi_enabled ? ',esi=on' : null);

        if(isset($lscache_control)) {
            $lscache_string = str_replace(';', ',', $lscache_control);
        }

        if($response->headers->has('X-LiteSpeed-Cache-Control') == false) {
            $response->headers->set('X-LiteSpeed-Cache-Control', $lscache_string);
        }

        return $response;
    }
}
