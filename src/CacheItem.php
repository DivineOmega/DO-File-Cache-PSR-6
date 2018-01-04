<?php

namespace rapidweb\RWFileCachePSR6;

use Psr\Cache\CacheItemInterface;

class CacheItem implements CacheItemInterface
{
    private $key;
    private $value;
    private $expires = 0;
    public $isDeferred = false;

    public function __construct($key, $value)
    {
        $this->key = $key;
        $this->value = $value;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function get()
    {
        if ($this->isHit()===false) {
            return null;
        }

        return $this->value;
    }

    public function getExpires()
    {
        return $this->expires;
    }

    public function isHit()
    {
        return $this->value !== false;
    }

    public function set($value)
    {
        if ($this->isDeferred) {
            return;
        }

        $this->value = $value;
        return $this;
    }

    public function expiresAt($expiration)
    {
        return $this;
    }

    public function expiresAfter($time)
    {
        return $this;
    }

}