<?php
require_once(PHP_ROOT . 'libs/dao/DaoProxyBase.php');
require_once('CachedActivity_BeaconActivityDaoImpl.php');

class Activity_BeaconActivityDao extends DaoProxyBase {
  protected static $client_;

  // 获取实例
  public static function GetClient() {
    if (!isset(self::$client_))
      self::$client_ = new CachedActivity_BeaconActivityDaoImpl();
    return self::$client_;
  }
}
