<?php
/**
 * 旅游业态，现在要跑得快
 */
require_once(PHP_ROOT . 'libs/dao/DaoProxyBase.php');
require_once(FFAN_ROOT . 'dao/v2/CachedActivityDaoImpl.php');

class ActivityDao extends DaoProxyBase {
  protected static $client_;

  // 获取实例
  public static function GetClient() {
    if (!isset(self::$client_))
      self::$client_ = new CachedActivityDaoImpl();
    return self::$client_;
  }
}
