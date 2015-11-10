<?php
require_once(PHP_ROOT . 'libs/dao/DaoProxyBase.php');
require_once('CachedActivity_BeaconStoreDaoImpl.php');

class Activity_BeaconStoreDao extends DaoProxyBase {
  protected static $client_;

  // 获取实例
  public static function GetClient() {
    if (!isset(self::$client_))
      self::$client_ = new CachedActivity_BeaconStoreDaoImpl();
    return self::$client_;
  }
}
