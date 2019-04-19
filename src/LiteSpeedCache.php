<?php

namespace Litespeed\LSCache;

class LiteSpeedCache
{
    public function purge(string $items)
    {
        return header('X-LiteSpeed-Purge: ' . $items);
    }
}
