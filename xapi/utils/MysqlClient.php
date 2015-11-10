<?php
require_once('RunTimeUtil.php');

class MysqlClient {
  const ERRNO_GONE_AWAY = 2006;
  const DB_CHARSET = 'utf8mb4';
  const MYSQL_CONNECT_TIMEOUT = 5;

  private static $master_config_ = array();
  private static $master_client_ = array();
  private static $slave_config_ = array();
  private static $slave_client_ = array();

  // 构造方法声明为private，防止直接创建对象
  private function __construct() {
    Log::Warn("Create MysqlClient not allowed");
  }

  public static function AddMaster($host, $user, $passwd, $db_name, $port, $db_index=0) {
    $db_key = self::GetDbIndexKey($db_name, $db_index);

    self::$master_config_[$db_key] = array('host' => $host,
                                            'user' => $user,
                                            'passwd' => $passwd,
                                            'port' => $port);
  }

  public static function AddSlave($host, $user, $passwd, $db_name, $port, $db_index=0) {
      print_r($db_name);
    $db_key = self::GetDbIndexKey($db_name, $db_index);
    self::$slave_config_[$db_key] = array('host' => $host,
                                           'user' => $user,
                                           'passwd' => $passwd,
                                           'port' => $port);
  }

  private static function GetMasterClient($db_name, $db_index=0) {
    $db_key = self::GetDbIndexKey($db_name, $db_index);

    if (!empty(self::$master_client_[$db_key]))
      return self::$master_client_[$db_key];
    if (empty(self::$master_config_[$db_key])) {
      Log::Warn("Invalid master db name:${db_name}, index:$db_key");
      return NULL;
    }
    $config = self::$master_config_[$db_key];
    $client = mysqli_init();
    $client->options(MYSQLI_OPT_CONNECT_TIMEOUT, self::MYSQL_CONNECT_TIMEOUT);
    $client->real_connect($config['host'], // host
                          $config['user'], // user
                          $config['passwd'], // passwd
                          $db_name, // db name
                          $config['port']); // port
    if ($client->connect_errno) {
      Log::Warn("Failed to connect to MySQL: (" . $client->connect_errno . ") "
                . $client->connect_error . ${db_name} . " idx:" . $db_key);
      return NULL;
    }
    $client->query('set names ' . self::DB_CHARSET);
    self::$master_client_[$db_key] = $client;
    return $client;
  }

  private static function GetSlaveClient($db_name, $db_index=0) {
    $db_key = self::GetDbIndexKey($db_name, $db_index);

    if (!empty(self::$slave_client_[$db_key]))
      return self::$slave_client_[$db_key];
    if (empty(self::$slave_config_[$db_key])) {
      Log::Warn("Invalid slave db name:${db_name}" . " idx:" . $db_key);
      return NULL;
    }
    $config = self::$slave_config_[$db_key];
    $client = mysqli_init();
    $client->options(MYSQLI_OPT_CONNECT_TIMEOUT, self::MYSQL_CONNECT_TIMEOUT);
    $client->real_connect($config['host'], // host
                          $config['user'], // user
                          $config['passwd'], // passwd
                          $db_name, // db name
                          $config['port']); // port
    if ($client->connect_errno) {
      Log::Warn("Failed to connect to MySQL: (" . $client->connect_errno . ") "
                . $client->connect_error . $db_name . " idx:" . $db_key);
      return NULL;
    }
    $client->query('set names ' . self::DB_CHARSET);
    self::$slave_client_[$db_key] = $client;
    return $client;
  }

  // 执行更新请求，请求主库，要求sql是写操作
  // return value, true:success, false:failure
  public static function ExecuteUpdate($db_name, $sql, $db_index=0) {
    $runTimeUtil = new RunTimeUtil();
    $client = self::GetMasterClient($db_name, $db_index);
    if (!$client)
    {
        Log::Warn('ExecuteUpdate :' . $sql . '; GetMasterClient failed; Cost time: ' . $runTimeUtil->spent() . 'ms;');
        return false;
    }
    $result = $client->query($sql);
    if ($client->errno) {
      if (self::ERRNO_GONE_AWAY == $client->errno) {
        $client->close();
        $db_key = self::GetDbIndexKey($db_name, $db_index);
        unset(self::$master_client_[$db_key]);
      }
      Log::Warn("ExecuteUpdate failed:" . $client->errno . "," . $client->error . '; ExecuteUpdate :' . $sql . '; Cost time: ' . $runTimeUtil->spent() . 'ms;');
      return false;
    }
    Log::Info("ExecuteUpdate success:" . $sql . ';This sql cost time: ' . $runTimeUtil->spent() . 'ms;');
    return true;
  }

  // 执行查询请求，请求从库，要求sql是只读操作
  public static function ExecuteQuery($db_name, $sql, $db_index=0, $assoc=1, $slave=1) {
    $runTimeUtil = new RunTimeUtil();
    if ($slave)
      $client = self::GetSlaveClient($db_name, $db_index);
    else
      $client = self::GetMasterClient($db_name, $db_index);
    if (!$client)
    {
        Log::Warn('ExecuteQuery:' . $sql . ';Get client failed; Cost time: ' . $runTimeUtil->spent() . 'ms;');
        return false;
    }
    $result = $client->query($sql);
    if ($client->errno) {
      if (self::ERRNO_GONE_AWAY == $client->errno) {
        $client->close();
        $db_key = self::GetDbIndexKey($db_name, $db_index);
        unset(self::$slave_client_[$db_key]);
      }
      Log::Warn("ExecuteQuery failed:".$client->errno.",".$client->error . '; ExecuteQuery:' . $sql . '; Cost time: ' . $runTimeUtil->spent() . 'ms;');
      return false;
    }
    if (!($result) || 0 == $result->num_rows)
    {
        Log::Info('ExecuteQuery:' . $sql . ';This sql cost time: ' . $runTimeUtil->spent() . 'ms;');
        return array();
    }
    $result_array = array();
    if ($assoc) {
      while ($row = $result->fetch_array(MYSQLI_ASSOC))
        $result_array[] = $row;
    } else {
      while ($row = $result->fetch_array(MYSQLI_NUM))
        $result_array[] = $row;
    }
    Log::Info('ExecuteQuery:' . $sql . ';This sql cost time: ' . $runTimeUtil->spent() . 'ms;');
    return $result_array;
  }

  // 查询更新记录数量
  public static function UpdateAffectedRows($db_name, $db_index=0) {
    $client = self::GetMasterClient($db_name, $db_index);
    if (!$client) return 0;
    return $client->affected_rows;
  }

  // 查询获取记录数量
  public static function QueryResultRows($db_name, $db_index=0, $slave=1) {
    if ($slave)
      $client = self::GetSlaveClient($db_name, $db_index);
    else
      $client = self::GetMasterClient($db_name, $db_index);
    if (!$client) return 0;
    return $client->affected_rows;
  }

  // 查询插入的上一条记录ID
  public static function GetInsertID($db_name, $db_index=0) {
    $client = self::GetMasterClient($db_name, $db_index);
    if (!$client) return 0;
    return $client->insert_id;
  }

  // 数据入库
  public static function InsertData($db_name, $table_name, $fields, $rows, $db_index=0) {
    $result = true;
    $sql_prefix = "INSERT INTO  " . $table_name . " (" .
                  implode(",", $fields) . ")" . " VALUES (";

    foreach ($rows as $row) {
      $row_data = array();
      foreach ($fields as $field)
        $row_data[] = "'" . @addslashes($row[$field]) . "'";
      $sql = $sql_prefix . implode(",", $row_data) . ")";
      if (!self::ExecuteUpdate($db_name, $sql, $db_index))
        $result = false;
    }
    return $result;
  }

  // 查询全部的字段, 有条件
  public static function QueryAllFields($db_name, $table_name, $condition, $db_index=0, $assoc=1, $slave=1) {
    $sql = "SELECT * FROM " . $table_name . " " . $condition;
    return self::ExecuteQuery($db_name, $sql, $db_index, $assoc, $slave);
  }

  // 查询特定的字段, 有条件
  public static function QueryFields($db_name, $table_name, $fields, $condition, $db_index=0, $assoc=1, $slave=1) {
    $sql = "SELECT " . implode(",", $fields) . " FROM " . $table_name
           . " " . $condition;
    return self::ExecuteQuery($db_name, $sql, $db_index, $assoc, $slave);
  }

  // 查询数量
  public static function QueryCount($db_name, $table_name, $condition, $db_index=0, $assoc=1, $slave=1) {
    $sql = "SELECT count(1) as num FROM " . $table_name . " " . $condition;
    $result = self::ExecuteQuery($db_name, $sql, $db_index, $assoc, $slave);
    if ($result) return $result[0]['num'];
    return 0;
  }

  // 更新特定的字段, 有条件
  public static function UpdateFields($db_name, $table_name, $field_values, $condition, $db_index=0) {
    $update_field_values = array();
    foreach ($field_values as $field => $value)
      $update_field_values[] = $field . "=" . "'" . addslashes($value) . "'";
    $sql = "UPDATE " . $table_name . " SET " . implode(",", $update_field_values) .
           " " . $condition;
    return self::ExecuteUpdate($db_name, $sql, $db_index);
  }

  // 更新特定的字段, 有条件，有安全过滤
  public static function UpdateDataWithFilter($db_name, $table_name, $fields, $row_data, $condition, $db_index=0) {
    $update_field_values = array();
    foreach ($fields as $field) {
      if (isset($row_data[$field])) {
        $update_field_values[$field] = $row_data[$field];
      }
    }
    return self::UpdateFields($db_name, $table_name, $update_field_values, $condition, $db_index);
  }

  // 删除数据, 有条件
  public static function Delete($db_name, $table_name, $condition, $db_index=0) {
    $sql = "DELETE FROM " . $table_name . " " . $condition;
    return self::ExecuteUpdate($db_name, $sql, $db_index);
  }

  // 拼装db_name和数字索引
  private static function GetDbIndexKey($db_name, $db_index){
    if ($db_index > 0) return $db_name . '|' . (int)$db_index;
    return $db_name;
  }
}

