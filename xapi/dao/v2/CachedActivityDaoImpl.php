<?php
require_once(PHP_ROOT . 'libs/util/MemCachedClient.php');
require_once('ActivityDaoImpl.php');

class CachedActivityDaoImpl extends ActivityDaoImpl
{
    const MEMCACHE_GROUP = 'default';
    const CACHE_EXPIRE = 60;

}
