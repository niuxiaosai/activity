<?php
/*
 * creator: hexuan
 * */
abstract class BaseDao extends Singleton
{
  protected $DB_NAME = '';
  protected $TABLE_NAME = '';
  protected $CACHE_TABLE = '';
  protected $KEY = '';

  //单条插入
  public function Insert($data)
  {
    if (\BaseDB::Insert($this->DB_NAME, $this->TABLE_NAME, array_keys($data), array(array_values($data))) == true)
    {
      $id = \MysqlClient::GetInsertID($this->DB_NAME);
      //\BaseCache::Dels($this->CACHE_TABLE, array($id));
      return true;
    }
    return false;
  }
  //批量插入
  public function InsertList($data)
  {
    if (\BaseDB::InsertList($this->DB_NAME, $this->TABLE_NAME,$data) == true)
    {
      return true;
    }
    return false;
  }
  //查询插入的上一条记录ID
  public function GetInsertID(){
  	$id = \MysqlClient::GetInsertID($this->DB_NAME);
  	return $id;
  }
  //更新
  public function Update($behavior, $conditions)
  {
    if (empty($conditions[$this->KEY]))
    {
      return false;
    }
    if (\BaseDB::Update($this->DB_NAME, $this->TABLE_NAME, $behavior, $conditions) == true)
    {
      \BaseCache::Dels($this->CACHE_TABLE, array($conditions[$this->KEY]));
      return true;
    }
    return false;
  }

  //批量获取
  public function Gets($ids)
  {
    $data = \BaseCache::Gets($this->CACHE_TABLE, $ids);
    
    $db_ids = array_diff($ids, array_keys($data));
    if (!empty($db_ids))
    {
      $db_ids_string = implode(' , ', $db_ids);
      $db_data = \BaseDB::Gets($this->DB_NAME, $this->TABLE_NAME, array($this->KEY . " in ($db_ids_string)"));

      foreach ($db_data as $value)
      {
        $data[$value[$this->KEY]] = $value;
      }
    }

    foreach (array_diff($ids, array_keys($data)) as $id)
    {
      $data[$id] = false;
    }
    \BaseCache::Sets($this->CACHE_TABLE, $data);
    return $data;
  }

  public function Get($id)
  {
    return \BaseDB::Gets($this->DB_NAME, $this->TABLE_NAME, array($this->KEY." = $id"));
  }

  public function Del($id)
  {
    return \BaseDB::Del($this->DB_NAME, $this->TABLE_NAME, array("id = $id"));
  }

  public function Search($conditions)
  {
    return \BaseDB::Search($this->DB_NAME, $this->TABLE_NAME, $this->KEY,$conditions);
  }

  public function Query($conditions = array(),$fields ='*',$sort,$offset,$limit)
  {
    return \BaseDB::Query($this->DB_NAME, $this->TABLE_NAME, $fields, $conditions,$sort,$offset,$limit);
  }
  
 public function GetList()
  {
    return \BaseDB::Gets($this->DB_NAME, $this->TABLE_NAME,array());
  }

  public function UpdateBySql($sql)
  {
    return \BaseDB::UpdateBySql($this->DB_NAME,$sql);
  }

   public function Sets($data)
  {
    if (sizeof($data) <= 0)
    {
      return false;
    }
	return \BaseDB::Sets($this->DB_NAME, $this->TABLE_NAME, array_keys($data[0]), $data);
  }

  public function Begin()
  {
    \BaseDB::Begin($this->DB_NAME);
    return true;
  }

  public function Commit()
  {
    \BaseDB::Commit($this->DB_NAME);
    return true;
  }

  public function ROLLBACK()
  {
    \BaseDB::ROLLBACK($this->DB_NAME);
    return true;
  }
}
