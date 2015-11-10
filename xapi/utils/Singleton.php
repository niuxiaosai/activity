<?php
/*
 * creator: hexuan
 * */
class Singleton
{
  private static $instances = array();
  protected function __construct() {}
  protected function __clone() {}
  public function __wakeup()
  {
    throw new Exception('Cannot unserialize');
  }

  public static function GetInstance()
  {
    $cls = get_called_class();
    if (!isset(self::$instances[$cls]))
    {
      self::$instances[$cls] = new static;
    }
    return self::$instances[$cls];
  }
}
