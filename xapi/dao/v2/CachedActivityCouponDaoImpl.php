<?php
/**
 * 旅游业态，现在要跑得快
 */
require_once(PHP_ROOT . 'libs/util/MemCachedClient.php');
require_once('ActivityCouponDaoImpl.php');

class CachedActivityCouponDaoImpl extends ActivityCouponDaoImpl{
  const MEMCACHE_GROUP = 'default';
  const CACHE_EXPIRE = 60;
}
