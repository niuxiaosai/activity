<?php
/**
 * 旅游业态，现在要跑得快
 */
require_once(PHP_ROOT . 'libs/dao/DaoProxyBase.php');
require_once('CachedAdminLogDaoImpl.php');

class AdminLogDao extends DaoProxyBase {
  protected static $client_;

  // 获取实例
  public static function GetClient() {
    if (!isset(self::$client_))
      self::$client_ = new CachedAdminLogDaoImpl();
    return self::$client_;
  }
}
