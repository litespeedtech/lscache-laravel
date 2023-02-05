# Changelog
## 1.3.5
- Update composer.json to support Laravel 10

## 1.3.4
- Update composer.json to support Laravel 9

## 1.3.3
- Drop laravel/framework from composer, use illuminate/support to add support for Lumen installation

## 1.3.2
- Add Laravel 8 support
- Default max-age to `0` if config ain't published

## v1.3.1
- Remove the use of `isMethodCacheable` to support older versions of Laravel

## v1.3.0
- Add `purgeAll`, `purgeTags` and `purgeItems` functions (Contributed by @piannelli)

## v1.2.1
- Add Laravel 7 support

## v1.2.0
- Add `GUEST_ONLY` option to only cache users that are not authenticated in Laravel
- Fixing a bug for global ESI to not be set when defining cache control on routes or route groups

## v1.1.0
- Add stale option for LSCache::purge

## v1.0.3
- Add Laravel 6 support

## v1.0.2
- Allow Laravel 5.1 to 5.5
- Add documentation for how to use with Laravel >=5.1 and <=5.4

## v1.0.1
- Do not return X-LiteSpeed-Cache-Control if the default max-age is set to `0`
- Update the README.md to reflect the X-LiteSpeed-Cache-Control change

## v1.0.0
- Added support for setting LSCache Cache-Control via a middleware
- Added support for purging the cache via a Facade
