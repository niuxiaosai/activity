<?php
/*
 * creator: hexuan
 * */
require_once(PHP_ROOT . 'libs/util/MemCachedClient.php');
class BaseCache
{
  const SEP = '^_^';
  const EXPIRE = 0;
  const GROUP = 'cache';

  private static $client = null;

  private static function GetClient()
  {
    if (self::$client == null)
    {
      self::$client = \MemCachedClient::GetInstance(self::GROUP);
    }
    return self::$client;
  }

  public static function Sets($table, array $datas, $expire = self::EXPIRE)
  {
    $client = self::GetClient();
    $insert_data = array();
    foreach ($datas as $key => $value)
    {
      $insert_data[$table.self::SEP.$key] = $value;
    }
    return $client->setMulti($insert_data, $expire);
  }

  public static function Gets($table, array $ids)
  {
    $client = self::GetClient();
    $get_keys = array();
    foreach ($ids as $id)
    {
      $get_keys[] = $table.self::SEP.$id;
    }
    $data = $client->getMulti($get_keys);
    $outer = array();
    foreach ($ids as $id)
    {
      if (isset($data[$table.self::SEP.$id]))
      {
        $outer[$id] = $data[$table.self::SEP.$id];
      }
    }
    return $outer;
  }

  public static function Dels($table, array $ids)
  {
    $client = self::GetClient();
    $del_keys = array();
    foreach ($ids as $id)
    {
      $del_keys[] = $table.self::SEP.$id;
    }
    if ($client->CheckMethod('deleteMulti'))
    {
      //memcached2.0
      $data = $client->deleteMulti($del_keys);
      foreach ($data as &$d)
      {
        if ($d !== true)
          $d = false;
      }
    }
    else
    {
      //memcached < 2.0
      $data = array();
      foreach ($del_keys as $key)
      {
        $data[$key] = $client->delete($key);
      }
    }

    $outer = array();
    foreach ($ids as $id)
    {
      if (isset($data[$table.self::SEP.$id]))
      {
        $outer[$id] = $data[$table.self::SEP.$id];
      }
    }
    return $outer;
  }
}

