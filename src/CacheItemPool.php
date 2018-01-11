<?php

namespace rapidweb\RWFileCachePSR6;

use Psr\Cache\CacheItemPoolInterface;
use rapidweb\RWFileCache\RWFileCache;
use Psr\Cache\CacheItemInterface;

class CacheItemPool implements CacheItemPoolInterface
{
    private $rwFileCache;
    private $deferredItems = [];

    public function __construct()
    {
        $this->rwFileCache = new RWFileCache();
    }

    public function changeConfig(array $config)
    {
        return $this->rwFileCache->changeConfig($config);
    }

    private function sanityCheckKey($key) 
    {
        if (!is_string($key)) {
            throw new CacheInvalidArgumentException;
        }

        $invalidChars = ['{', '}', '(', ')', '/', '\\', '@', ':'];

        foreach($invalidChars as $invalidChar) {
            if (stripos($key, $invalidChar)!==false) {
                throw new CacheInvalidArgumentException;
            }
        }

    }

    public function getItem($key)
    {
        $this->sanityCheckKey($key);

        if (array_key_exists($key, $this->deferredItems)) {
            return $this->deferredItems[$key];
        }

        return new CacheItem($key, $this->rwFileCache->get($key));
    }

    public function getItems(array $keys = [])
    {
        $results = [];

        foreach($keys as $key) {
            $results[$key] = $this->getItem($key);
        }

        return $results;
    }

    public function hasItem($key)
    {
        $this->sanityCheckKey($key);

        return $this->getItem($key)->isHit();
    }

    public function clear()
    {
        $this->deferredItems = [];
        return $this->rwFileCache->flush();
    }

    public function deleteItem($key)
    {
        $this->sanityCheckKey($key);

        if (array_key_exists($key, $this->deferredItems)) {
            unset($this->deferredItems[$key]);
            return true;
        }

        $this->rwFileCache->delete($key);

        return true;
    }

    public function deleteItems(array $keys)
    {


        foreach($keys as $key) {
            $this->deleteItem($key);
        }

        return true;
    }

    public function save(CacheItemInterface $item)
    {
        return $this->rwFileCache->set($item->getKey(), $item->get(), $item->getExpires());
    }

    public function saveDeferred(CacheItemInterface $item)
    {
        $this->deferredItems[$item->getKey()] = $item->prepareForSaveDeferred();
        return true;
    }

    public function commit()
    {
        foreach($this->deferredItems as $item) {
            $this->save($item);
        }
        $this->deferredItems = [];
        return true;
    }

    public function __destruct()
    {
        $this->commit();
    }
}