<?php
/*
 * creator: hexuan
 * */


class BaseDB
{
  public static function Gets($db, $table, array $conditions)
  {
    $c = '';
    if (count($conditions) != 0)
      $c = 'where '.implode(' and ', $conditions);
    $sql = <<<SQL
select * from $table $c
SQL;
    return \MysqlClient::ExecuteQuery($db, $sql);
  }

  public static function Update($db, $table, array $behavior, array $conditions)
  {
    $c = '';
    if (count($conditions) != 0)
    {
      $t = array();
      foreach ($conditions as $key => $value)
      {
        if (is_string($key))
        {
          $t[] = "$key = '$value'";
        }
        else
        {
          $t[] = $value;
        }
      }
      $c = 'where '.implode(' and ', $t);
    }
    $b = '';
    if (count($behavior) != 0)
    {
      $t = array();
      foreach ($behavior  as $key => $value)
      {
        $t[] = "$key = '$value'";
      }
      $b = 'set '.implode(', ', $t);
    }
    $sql = <<<SQL
update $table $b $c
SQL;
    if (\MysqlClient::ExecuteUpdate($db, $sql) == false)
    {
      return false;
    }
    if (\MysqlClient::UpdateAffectedRows($db) == 0)
    {
      return false;
    }
    return true;
  }

  public static function Sets($db, $table, array $keys, array $values)
  {
    $keys = implode(',', $keys);
    foreach ($values as $key => $value)
    {
      foreach ($value as &$v)
      {
        $v = "'$v'";
      }
      $values[$key] = '('.implode(',', $value).')';
    }
    $values = implode(',', $values);
    $sql = <<<SQL
replace into $table($keys) values $values
SQL;
    return \MysqlClient::ExecuteUpdate($db, $sql);
  }

  public static function Insert($db, $table, array $keys, array $values)
  {
    $keys = implode(',', $keys);
    foreach ($values as $key => $value)
    {
      foreach ($value as &$v)
      {
        $v = "'$v'";
      }
      $values[$key] = '('.implode(',', $value).')';
    }
    $values = implode(',', $values);
    $sql = <<<SQL
insert into $table($keys) values $values
SQL;
    return \MysqlClient::ExecuteUpdate($db, $sql);
  }

  public static function Search($db, $table, $key,$conditions)
  {
    $c = '';
    if ($conditions!== null)
      $c = 'where '.$conditions;
    $sql = <<<SQL
select DISTINCT($key) from $table $c
SQL;
    return \MysqlClient::ExecuteQuery($db, $sql);
  }

  public static function Query($db, $table, $fields, array $conditions,$sort,$offset,$limit)
  {
    $c = '';
    $l='';
    if($offset && $limit){
      $l='limit '.$offset.','.$limit;
    }elseif($limit){
      $l='limit '.$limit;
    }
    if (count($conditions) != 0)
      $c = 'where '.implode(' and ', $conditions);
    $sql = <<<SQL
select $fields from $table $c $sort $l
SQL;
    return \MysqlClient::ExecuteQuery($db, $sql);
  }
  
   public static function Begin($db)
  {
    return \MysqlClient::ExecuteUpdate($db, 'begin');
  }

  public static function Commit($db)
  {
    return \MysqlClient::ExecuteUpdate($db, 'commit');
  }

  public static function ROLLBACK($db)
  {
    return \MysqlClient::ExecuteUpdate($db, 'rollback');
  }

    public static function InsertList($db, $table, array $values)
  {
    foreach ($values as $key => $value)
    {
      $keys=array();
      foreach ($value as $k => &$v)
      {
        $keys[] = $k;
        $v = "'$v'";
      }
      $values[$key] = '('.implode(',', $value).')';
    }
    $keys = implode(',', $keys);
    $values = implode(',', $values);
    $sql = <<<SQL
insert into $table($keys) values $values
SQL;
    return \MysqlClient::ExecuteUpdate($db, $sql);
  }

  public static function Del($db, $table, array $conditions)
  {
    $c = '';
    if (count($conditions) != 0)
      $c = 'where '.implode(' and ', $conditions);
    $sql = <<<SQL
delete from $table $c
SQL;
    return \MysqlClient::ExecuteUpdate($db, $sql);
  }


  public static function UpdateBySql($db,$sql)
  {
    if (\MysqlClient::ExecuteUpdate($db, $sql) == false)
    {
      return false;
    }
    if (\MysqlClient::UpdateAffectedRows($db) == 0)
    {
      return false;
    }
    return true;
  }
}
