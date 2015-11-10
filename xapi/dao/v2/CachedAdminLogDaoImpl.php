<?php
/**
 * 旅游业态，现在要跑得快
 */
require_once(PHP_ROOT . 'libs/util/MemCachedClient.php');
require_once('AdminLogDaoImpl.php');

class CachedAdminLogDaoImpl extends AdminLogDaoImpl{
  const MEMCACHE_GROUP = 'default';
  const CACHE_EXPIRE = 60;
}
