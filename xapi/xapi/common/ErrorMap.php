<?php
/*
 * creator: hexuan
 * */
class ErrorMap
{
  const E_INVALIDATE_INPUT = -100;
  const E_ACTIVITY_DATE_ERROR=-101;
  const E_ACTIVITY_INSERT_ERROR=-102;
  const E_ACTIVITY_TIME_INSERT_ERROR=-103;
  const E_ACTIVITY_PRIZE_INSERT_ERROR=-104;
  const E_ACTIVITY_UPDATESTATUS_ERROR=-105;
  const E_ACTIVITY_ALIAS_ERROR=-106;
  const E_ACTIVITY_UPDATE_ERROR=-107;
  const E_ACTIVITY_TIME_UPDATE_ERROR=-108;
  const E_ACTIVITY_PRIZE_UPDATE_ERROR=-109;
  const E_ACTIVITY_GET_ERROR =-110;
  private static $error_map = array(
    self::E_INVALIDATE_INPUT => '入参不合法,请检查入参',
    self::E_ACTIVITY_ALIAS_ERROR=>'活动名称不能重复',
    self::E_ACTIVITY_DATE_ERROR=>'开始时间不能大于结束时间',
    self::E_ACTIVITY_INSERT_ERROR=>'插入周周抢基础数据失败',
    self::E_ACTIVITY_TIME_INSERT_ERROR=>'插入周周抢关联时段数据失败',
    self::E_ACTIVITY_PRIZE_INSERT_ERROR=>'插入周周抢关联奖品数据失败',
    self::E_ACTIVITY_UPDATE_ERROR=>'更新基础数据失败',
    self::E_ACTIVITY_UPDATESTATUS_ERROR=>'更新状态失败',
    self::E_ACTIVITY_TIME_UPDATE_ERROR=>'更新关联时段数据失败',
    self::E_ACTIVITY_PRIZE_UPDATE_ERROR=>'更新关联奖品数据失败',
    self::E_ACTIVITY_GET_ERROR=>'获取数据详情失败',
    );

  public static function GetErrorMsg($code)
  {
    if (isset(self::$error_map[$code]))
    {
      return self::$error_map[$code];
    }
    else
    {
      return '未定义错误信息';
    }
  }

  public static function CheckNull($params)
  {
    foreach ($params as $key => $value)
    {
      if (is_null($value))
      {
        return "[$key不能为空]";
      }
    }
  }
}
