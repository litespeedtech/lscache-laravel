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
     * @param  string  ...$tags
     * @return mixed
     */
    public function handle($request, Closure $next, ...$tags)
    {
        $response = $next($request);

        if (!in_array($request->getMethod(), ['GET', 'HEAD']) || !$response->getContent() || empty($tags)) {
            return $response;
        }

        // Combine and normalize all tags (supports , and ; separators)
        $lscache_string = str_replace(';', ',', implode(',', $tags));

        // Parse dynamic route parameters {param}
        $lscache_string = preg_replace_callback('/\{([^}]+)\}/', function ($matches) use ($request) {
            $paramName = $matches[1];
            $value = $request->route($paramName);

            if (is_null($value)) {
                return $matches[0];
            }

            if (is_object($value) && method_exists($value, 'getKey')) {
                return $value->getKey();
            }

            return (string) $value;
        }, $lscache_string);

        if (empty($lscache_string)) {
            return $response;
        }

        // append to existing headers if present
        if ($response->headers->has('X-LiteSpeed-Tag')) {
            $existing = $response->headers->get('X-LiteSpeed-Tag');
            $response->headers->set('X-LiteSpeed-Tag', $existing . ',' . $lscache_string);
        } else {
            $response->headers->set('X-LiteSpeed-Tag', $lscache_string);
        }

        return $response;
    }
}
