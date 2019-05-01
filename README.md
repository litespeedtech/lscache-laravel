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

Laravel uses Auto-Discovery, so you won't have to do any changes to your application, the two middlewares and facade will be available right from the beginning.

You should publish the package configuration, which allows you to set the defaults for the `X-LiteSpeed-Cache-Control` header:

```
php artisan vendor:publish --provider="Litespeed\LSCache\LSCacheServiceProvider"
```

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

In the above example, we're simply telling it to add an additional header called `X-LiteSpeed-Purge` with the value `/`, this will invalidate the frontpage of the site.

You can also purge everything by doing:

```php
LSCache::purge('*');
```

You can purge individual or multiple tags:

```php
LSCache::purge('tag=archive, tag=categories');
```

Or if you want to purge private cache by tag:

```php
LSCache::purge('private, tag=users');
```

You even have the possibility to purge a set of public tags and and purge all the private tags:

```php
LSCache::purge('pubtag1, pubtag2, pubtag3; private, *');
```
