<?php

use PHPUnit\Framework\TestCase;

use Cache\IntegrationTests\CachePoolTest;
use rapidweb\RWFileCachePSR6\CacheItemPool;

class PoolIntegrationTest extends CachePoolTest
{
    public function createCachePool()
    {
        $cacheItemPool = new CacheItemPool();
        $cacheItemPool->changeConfig(['cacheDirectory' => __DIR__.'/Data/']);
        return $cacheItemPool;
    }
}