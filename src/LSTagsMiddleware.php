<?php

namespace Litespeed\LSCache;

use Closure;

class LSTagsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string $lscache_tags
     * @return mixed
     */
    public function handle($request, Closure $next, string $lscache_tags = null)
    {
        $response = $next($request);

        $lscache_string = null;

        if (!in_array($request->getMethod(), ['GET', 'HEAD']) || !$response->getContent()) {
            return $response;
        }

        if(isset($lscache_tags)) {
            $lscache_string = str_replace(';', ',', $lscache_tags);
        }

        if(empty($lscache_string)) {
            return $response;
        }

        if($response->headers->has('X-LiteSpeed-Tag') == false) {
            $response->headers->set('X-LiteSpeed-Tag', $lscache_string);
        }

        return $response;
    }
}
