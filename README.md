## Laravel LSCache

This package allows you to use lscache together with Laravel.

It provides two middlewares and one facade:

- LSCache Middleware to control the cache-control header for LiteSpeed LSCache
- LSTags Middleware to control the tag header for LiteSpeed LSCache
- LSCache facade to handle purging

## Installation

Require this package using composer.

```
composer require litespeed/lscache-laravel
```

Laravel uses Auto-Discovery, so you won't have to make any changes to your application, the two middlewares and facade will be available right from the beginning.

#### Steps for Laravel >=5.1 and <=5.4

The package can be used for Laravel 5.1 to 5.4 as well, however due to lack of Auto-Discovery, a few additional steps have to be performed.

In `config/app.php` you have to add the following code in your `aliases`:

```
'aliases' => [
    ...
    'LSCache'   => Litespeed\LSCache\LSCache::class,
],
```

In `app/Http/Kernel.php` you have to add the two middlewares under `middleware` and `routeMiddleware`:

```
protected $middleware = [
    ...
    \Litespeed\LSCache\LSCacheMiddleware::class,
    \Litespeed\LSCache\LSTagsMiddleware::class,
];

protected $routeMiddleware = [
    ...
    'lscache' => \Litespeed\LSCache\LSCacheMiddleware::class,
    'lstags' => \Litespeed\LSCache\LSTagsMiddleware::class,
];
```

Copy `lscache.php` to `config/`:

Copy the package `config/lscache.php` file to your `config/` directory.

**important**: Do not add the ServiceProvider under `providers` in `config/app.php`.

#### Steps for Laravel 5.5 and above

You should publish the package configuration, which allows you to set the defaults for the `X-LiteSpeed-Cache-Control` header:

```
php artisan vendor:publish --provider="Litespeed\LSCache\LSCacheServiceProvider"
```

### Enable CacheLookup for LiteSpeed Cache

To enable CacheLookup for LiteSpeed Cache, you have to include the following code, either on server, vhost or .htaccess level:

```apacheconf
<IfModule LiteSpeed>
   CacheLookup on
</IfModule>
```

## Usage

The package comes with 3 functionalities: Setting the cache control headers for lscache, settings specific tags and purging.

### cache-control

You'll be able to configure defaults in the `config/lscache.php` file, here you can set the max-age (`default_ttl`), the cacheability (`default_cacheability`) such as public, private or no-cache or enable esi (`esi`) in the `X-LiteSpeed-Cache-Control` response header.

If the `default_ttl` is set to `0`, then we won't return the `X-LiteSpeed-Cache-Control` response header.

You can control the config settings in your `.env` file as such:

- `LSCACHE_ESI_ENABLED` - accepts `true` or `false` to whether you want ESI enabled or not globally; Default `false`
- `LSCACHE_DEFAULT_TTL` - accepts an integer, this value is in seconds; Default: `0`
- `LSCACHE_DEFAULT_CACHEABILITY` - accepts a string, you can use values such as `private`, `no-cache`, `public` or `no-vary`; Default: `no-cache`
- `LSCACHE_GUEST_ONLY` - accepts `true` or `false` to decide if the cache should be enabled for guests only; Defaults to `false`

You set the cache-control header for lscache using a middleware, so we can in our routes do something like this:

```php
Route::get('/', function() {
    return view('frontpage');
});

Route::get('/about-us', function() {
    return view('about-us');
})->middleware('lscache:max-age=300;public');

Route::get('/contact', function() {
    return view('contact');
})->middleware('lscache:max-age=10;private;esi=on');

Route::get('/admin', function() {
    return view('admin');
})->middleware('lscache:no-cache');
```

Below is 4 examples:
- the `/` route will use the default X-LiteSpeed-Cache-Control header that you've configured in `config/lscache.php`.
- the `/about-us` route sets a max-age of 300 seconds as well as setting the cacheability to `public`, keep in mind you'll use semi-colon (`;`) to separate these values.
- the `/contact` route uses a max-age of 10 seconds, uses private cacheability and turns ESI on. Turning ESI on, allows you to use `<esi:include>` within your blade templates and these will be parsed by the ESI engine in LiteSpeed Web Server.
- the `/admin` route will never be cached by setting a `X-LiteSpeed-Cache-Control: no-cache` -header.

Now, you'll also be able to apply the same middleware to route groups in Laravel, let's take an example:

```php
Route::group(['prefix' => 'admin', 'middleware' => ['lscache:private;esi=on;max-age=120']], function() {
    Route::get('/dashboard', function() {
        return view('dashboard');
    });

    Route::get('/stats', function() {
        return view('stats');
    })->middleware('lscache:no-cache');
});
```

In the above case, we've set the whole `admin` group to be private with esi enabled and a max-age of 120 seconds, however in the `/admin/stats` route, we override the `X-LiteSpeed-Cache-Control` header to `no-cache`.

### tags

You're also able to set tags for LSCache using the `lstags` middleware. If we use the previous example of our `admin` route group:

```php
Route::group(['prefix' => 'admin', 'middleware' => ['lscache:private;esi=on;max-age=900', 'lstags:admin']], function() {
    Route::get('/dashboard', function() {
        return view('dashboard');
    });

    Route::get('/users', function() {
        return view('users');
    });
});
```

Here we've added the `lstags:admin` middleware, this means that the cache will get tagged with an `admin` tag, so when we later want to purge the cache, we can target all admin pages using the tag `admin`.

You can also do more complex tags as such:

```php
Route::get('/view', function() {
    return view('view');
})->middleware(['lscache:private', 'lstags:public:pubtag1;public:pubtag2;public:pubtag3;privtag1;privtag2']);
```

### purge

If we have an admin interface that controls for example a blog, when you publish a new article, you might want to purge the frontpage of the blog so the article appears in the overview.

You'd do this in your controller by doing

```php
<?php

namespace App\Http\Controllers;

use LSCache;

class BlogController extends BaseController
{
    // Your article logic here

    LSCache::purge('/');
}
```

In the above example, we're simply telling it to add an additional header called `X-LiteSpeed-Purge` with the value `stale,/`, this will invalidate the frontpage of the site.

You can also purge everything by doing:

```php
LSCache::purge('*');
// or
LSCache::purgeAll();
```

One or multiple URIs can be purged by using a comma-separated list:

```php
LSCache::purge('/blog,/about-us,/');
// or
LSCache::purgeItems(['/blog', '/about-us', '/']);
```

You can purge individual or multiple tags:

```php
LSCache::purge('tag=archive, tag=categories');
// or
LSCache::purgeTags(['archive', 'categories']);
```

Or if you want to purge private cache by tag:

```php
LSCache::purge('private, tag=users');
```

You even have the possibility to purge a set of public tags and and purge all the private tags:

```php
LSCache::purge('pubtag1, pubtag2, pubtag3; private, *');
```

LiteSpeed Cache for Laravel 1.1.0 comes with a stale option turned on by default for the `LSCache::purge` function, this can be turned off by using `false` as the second parameter in the `purge` function:

```php
LSCache::purge('*', false);
// or
LSCache::purge('*', $stale=false);
// or
LSCache::purgeAll(false);
```

#### Why stale purge matters

By default the way Lscache works in LiteSpeed is by purging an element in the cache, and next request will generate the cached version.

This works great if you're running a fairly low traffic site, however if your application takes let's say 2 seconds to process a given request, all traffic received to this endpoint within those 2 seconds will end up hitting the backend, and all visitors will hit PHP.

By using the `stale,` keyword in front the "key" you're purging, you're telling Lscache to purge the item, but if multiple visitors hit the same endpoint right after each other, only the first visitor will be the one generating the cache item, all remaining vistors will get served the stale cached page until the new cached page is available.

Since a page generation should be rather fast, we're only serving this stale content for maybe a couple of seconds, thus also the reason it's being enabled by default.

If your application cannot work with stale content at all, then you can use `false` or `$stale=false` as the second parameter in the `LSCache::purge()` function to disable this functionality.

You can also purge specific public tags by adding `~s` after the tag, such as:

```php
LSCache::purge('pubtag1, pubtag2~s, pubtag3; private, privtag1, privtag2', $stale=false);
```
Only `pubtag2` will be served stale.

### Laravel Authentication

If you use authentication in Laravel for handling guests and logged-in users, you'll likely want to also separate the cache for people based on this.

This can be done in the `.htaccess` file simply by using the cache-vary on the Authorization cookie:

```apache
RewriteEngine On
RewriteRule .* - [E=Cache-Vary:Authorization]
```

**Note: In the above example we use `Authorization`, this may have a different name depending on your setup, so it has to be changed accordingly.**
