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

    public function purgeAll($stale = true)
    {
        return $this->purge('*', $stale);
    }

    public function purgeTags(array $tags, $stale = true)
    {
        if (count($tags)) {
            return $this->purge(implode(',', array_map(function($tag) { return 'tag=' . $tag; }, $tags)), $stale);
        }
    }

    public function purgeItems(array $items, $stale = true)
    {
        if (count($items)) {
            return $this->purge(implode(',', $items), $stale);
        }
    }
}
