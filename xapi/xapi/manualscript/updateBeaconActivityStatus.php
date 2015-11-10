<?php
/**
 * beacon营销活动：如果活动时间已经过期，则自动更新活动状态为已结束
 */
require_once(dirname(__FILE__).'/../index.php');
require_once(PHP_ROOT . 'libs/util/MysqlClient.php');
$dbName = 'ff_cloud_marketing_platform';
$tableNameActivity = 'activity_beacon_activity';
$tableNameStore = 'activity_beacon_store';
$endStatus = 5;//活动已结束状态的ID
$curTime = time();
//活动时间已经过期且活动状态不是已结束
$where = 'where '.$curTime.' > end_date and `status`!=' . $endStatus;
$result = MysqlClient::QueryFields($dbName,$tableNameActivity,array('id'),$where);
if($result) {
  $str = '';
  foreach($result as $r) {
    $str .= $r['id'] . ',';
  }
  $str = rtrim($str,',');
  $where = 'where id in('.$str.')';
  $r = MysqlClient::UpdateFields($dbName,$tableNameActivity,array("status"=>$endStatus),$where);
  $where = 'where activity_id in('.$str.')';
  $r = MysqlClient::UpdateFields($dbName,$tableNameStore,array("activity_status"=>$endStatus),$where);
}
exit;
