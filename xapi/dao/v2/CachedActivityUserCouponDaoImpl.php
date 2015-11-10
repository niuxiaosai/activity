<?php
/**
 * 旅游业态，现在要跑得快
 */
require_once(PHP_ROOT . 'libs/util/MemCachedClient.php');
require_once (FFAN_ROOT . 'dao/v2/ActivityUserCouponDaoImpl.php');

class CachedActivityUserCouponDaoImpl extends ActivityUserCouponDaoImpl{
  const MEMCACHE_GROUP = 'default';
  const CACHE_EXPIRE = 60;
}
