<?php
require_once(PHP_ROOT . 'libs/util/MemCachedClient.php');
require_once('Activity_BeaconStoreDaoImpl.php');

class CachedActivity_BeaconStoreDaoImpl extends Activity_BeaconStoreDaoImpl{
  const MEMCACHE_GROUP = 'default';
  const CACHE_EXPIRE = 60;
  const CACHE_EXPIRE_DETAIL = 3600;
  const CACHE_EXPIRE_LIST = 300 ;
  
  var $isBatchOperate = 0;


}
