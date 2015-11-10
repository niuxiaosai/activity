<?php
/*
 * creator: huangxin29
 * */
namespace core;
require_once(PHP_ROOT . 'libs/util/MemCachedClient.php');

class FBaseCache
{
  const GROUP = 'dao';
  const EXPIRE = 0;
  const SEP = '^_^';


  public static function Handle($class, $func, $args)
  {
    switch (substr($func, 0, 3))
    {
      case 'Set':
        return self::Set($class.self::SEP.$args[0], $args[1], @$args[2]);
      case 'Get':
        return self::Get($class.self::SEP.$args[0]);
      case 'Del':
        return self::Del($class.self::SEP.$args[0]);
      default:
        return false;
    }
  }

  private static function Set($key, $value, $expire)
  {
    $client = \MemCachedClient::GetInstance(self::GROUP);
    $expire = $expire === null ? self::EXPIRE : intval($expire);
    \Logger::getLogger('core')->warn("cache set ($key) expire($expire)");
    return $client->add($key, $value, $expire);
  }

  private static function Del($key)
  {
    $client = \MemCachedClient::GetInstance(self::GROUP);
    if ($client->delete($key) == false)
    {
      \Logger::getLogger('core')->warn("cache ($key) 删除失败");
    }
    else
    {
      \Logger::getLogger('core')->info("cache del ($key)");
    }
  }

  private static function Get($key)
  {
    $client = \MemCachedClient::GetInstance(self::GROUP);
    $value = $client->get($key);
    if (empty($value) || $value === false)
    {
      \Logger::getLogger('core')->warn("cache get ($key) 未命中");
      return false;
    }
    else
    {
      \Logger::getLogger('core')->info("cache get ($key) 命中");
      return $value;
    }
  }
}
