<?php

namespace Litespeed\LSCache;

use Illuminate\Support\Facades\Facade;

class LSCache extends Facade
{
    protected static function getFacadeAccessor()
    {
        return LiteSpeedCache::class;
    }
}
