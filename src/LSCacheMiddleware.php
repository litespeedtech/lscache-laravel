<?php

namespace Litespeed\LSCache;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LSCacheMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     * @param  string                   $lscache_control
     * @return mixed
     */
    public function handle($request, Closure $next, string $lscache_control = null)
    {
        $response = $next($request);

        if (!in_array($request->getMethod(), ['GET', 'HEAD']) || !$response->getContent()) {
            return $response;
        }

        $esi_enabled    = config('lscache.esi');
        $maxage         = config('lscache.default_ttl', 0);
        $cacheability   = config('lscache.default_cacheability');
        $guest_only     = config('lscache.guest_only', false);

        if ($maxage === 0 && $lscache_control === null) {
            return $response;
        }

        if ($guest_only && Auth::check()) {
            $response->headers->set('X-LiteSpeed-Cache-Control', 'no-cache');

            return $response;
        }

        $lscache_string = "max-age=$maxage,$cacheability";

        if (isset($lscache_control)) {
            $lscache_string = str_replace(';', ',', $lscache_control);
        }

        if (Str::contains($lscache_string, 'esi=on') == false) {
            $lscache_string = $lscache_string.($esi_enabled ? ',esi=on' : null);
        }

        if ($response->headers->has('X-LiteSpeed-Cache-Control') == false) {
            $response->headers->set('X-LiteSpeed-Cache-Control', $lscache_string);
        }

        return $response;
    }
}
