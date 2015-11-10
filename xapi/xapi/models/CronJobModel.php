<?php
/*
 * creator: hexuan
 */
require_once (WEB_ROOT . 'models/extra/BaseModel.php');
require_once (FFAN_ROOT . 'dao/ActivityDao.php');
require_once (FFAN_ROOT . 'dao/ActivityPrizeDao.php');
class CronJobModel extends BaseModel {
	public function GetResponse() {
		$response = new Response ();
		return $response;
	}
	// 计划任务入口
	public function resetStock() {
		$response = new Response ();
		$info = $this->activityList ();
		$ids = array ();
		if ($info) {
			foreach ( $info as $key => $value ) {
				$ids [] = $value ['id'];
			}
			$activityIds = implode ( ',', $ids );
			$sql = "update activity_prize set day_margin = day_num where activity_id in($activityIds)";
			\core\ActivityPrizeDao::GetInstance ()->UpdateBySql ( $sql );
		}
		return $response;
	}
	
	/**
	 * *获取活动列表
	 */
	public function activityList() {
		$condition [] = "status=3";
		$info = \core\ActivityDao::GetInstance ()->Query ( $condition, '*', NULL, NULL, NULL );
		return $info;
	}
	
	/**
	 * * 验证数据逻辑方法
	 */
	public function msEscapeInt($str) {
		if (is_numeric ( $str )) {
			return $str;
		} else {
			return str_replace ( "'", "''", $str );
		}
	}
	public function msEscapeStr($str) {
		if (is_string ( $str )) {
			return $str;
		} else {
			return str_replace ( "'", "''", $str );
		}
	}
}
