<?php

namespace Litespeed\LSCache;

class LiteSpeedCache
{
    protected $stale_key;

    public function __construct()
    {
        $this->stale_key = "";
    }

    public function purge(string $items, bool $stale = true)
    {
        if($stale === true) {
            $this->stale_key = "stale,";
        }

        return header('X-LiteSpeed-Purge: ' . $this->stale_key . $items);
    }
}
