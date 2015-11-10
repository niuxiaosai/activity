<?php
require_once(PHP_ROOT . 'libs/util/MemCachedClient.php');
require_once('Activity_BeaconActivityDaoImpl.php');

class CachedActivity_BeaconActivityDaoImpl extends Activity_BeaconActivityDaoImpl{
  const MEMCACHE_GROUP = 'default';
  const CACHE_EXPIRE = 60;
  const CACHE_EXPIRE_DETAIL = 3600;
  const CACHE_EXPIRE_LIST = 300 ;
  
  var $isBatchOperate = 0;


}
