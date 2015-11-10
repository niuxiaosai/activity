<?php

require_once (WEB_ROOT . 'models/extra/BaseModel.php');
require_once (FFAN_ROOT . 'dao/Activity_ActivityDao.php');
require_once(PHP_ROOT . 'libs/util/HttpRequestHelper.php');
class TestModel extends BaseModel {
	public function GetResponse() {
		$response = new Response ();
		return $response;
	}

    /**
     * 请求路由处理，将不同的请求分发到不同的处理器中进行处理。
     * @param unknown $id
     * @return unknown
     */
    public function DoModel()
    {
        $response = new Response();

        $method_request = strtoupper($_SERVER['REQUEST_METHOD']);
        $path_info = trim($_SERVER["PATH_INFO"], "/");

        $path_info_array = explode("/", $path_info);

        if ($path_info_array[1] == "test" && $method_request == "GET") {

            return $this->save();

        }
        else {
            $response->status = "500";
            $response->msg = "请求方式或请求参数不正确";
            return $response;
        }
    }
	/**
	 * *保存
	 */
	public function save() {
		$response = new Response ();
		$now = date ( 'Y-m-d H:i:s' );

		return $response;
	}
	
	/**
	 * *更新
	 */
	public function update() {
		$response = new Response ();
		$now = date ( 'Y-m-d H:i:s' );
		// 参数数组
		$array_request = array (
				'planId',
				'title',
				'begin_time',
				'end_time',
				'interval_info',
				'hit_count',
				'win_count',
				'status',
				'activity_prize',
				'submitter' 
		);
		foreach ( $array_request as $key => $value ) {
			$$value = HttpRequestHelper::RequestParam ( $value, NULL );
			if ($value == 'planId' || $value == 'hit_count' || $value == 'status' || $value == 'win_count') {
				$$value = $this->msEscapeInt ( $$value );
			}
			if ($$value == NULL || $$value == '') {
				$response->status = ErrorMap::E_INVALIDATE_INPUT;
				$response->msg = ErrorMap::GetErrorMsg ( $response->status );
				return $response;
			}
		}
		if (strtotime ( $begin_time ) > strtotime ( $end_time )) {
			$response->status = ErrorMap::E_ACTIVITY_DATE_ERROR;
			$response->msg = ErrorMap::GetErrorMsg ( $response->status );
			return $response;
		}
		// 验证是否有重复数据
		$alias = sha1 ( $title );
		$repeat = $this->getRepeat ( $alias, $planId );
		if ($repeat) {
			$response->status = ErrorMap::E_ACTIVITY_ALIAS_ERROR;
			$response->msg = ErrorMap::GetErrorMsg ( $response->status );
			return $response;
		}
		// 更新主数据
		$behavior = array (
				'update_time' => $now,
				'title' => $title,
				'alias' => $alias,
				'begin_time' => $begin_time,
				'end_time' => $end_time,
				'hit_count' => $hit_count,
				'win_count' => $win_count,
				'status' => $status,
				'submitter' => $submitter 
		);
		$res = $this->updateBaseInfo ( $behavior, $planId );
		if ($res == false) {
			$response->status = ErrorMap::E_ACTIVITY_UPDATE_ERROR;
			$response->msg = ErrorMap::GetErrorMsg ( $response->status );
			return $response;
		}
		// 更新时段从表数据
		$res1 = $this->updateActivityTime ( $interval_info, $planId );
		if ($res1 == false) {
			$response->status = ErrorMap::E_ACTIVITY_TIME_UPDATE_ERROR;
			$response->msg = ErrorMap::GetErrorMsg ( $response->status );
			return $response;
		}
		// 更新奖品从表数据
		$res2 = $this->updateActivityPrize ( $activity_prize, $planId );
		if ($res2 == false) {
			$response->status = ErrorMap::E_ACTIVITY_PRIZE_UPDATE_ERROR;
			$response->msg = ErrorMap::GetErrorMsg ( $response->status );
			return $response;
		}
		return $response;
	}
	
	/**
	 * *列表
	 */
	public function search() {
		$response = new Response ();
		$offset = HttpRequestHelper::RequestParam ( 'offset', NULL );
		$limit = HttpRequestHelper::RequestParam ( 'limit', NULL );
		$title = HttpRequestHelper::RequestParam ( 'title', NULL );
		$submitter = HttpRequestHelper::RequestParam ( 'submitter', NULL );
		$status = HttpRequestHelper::RequestParam ( 'status', NULL );
		$begin_time = HttpRequestHelper::RequestParam ( 'begin_time', NULL );
		$end_time = HttpRequestHelper::RequestParam ( 'end_time', NULL );
		$condition = array ();
		if (! $offset)
			$offset = 0;
		if (! $limit)
			$limit = 20;
		if ($title) {
			$condition [] = "title like'%{$title}%'";
		}
		if ($submitter) {
			$condition [] = "submitter='{$submitter}'";
		} 
		if ($status) {
			$condition [] = "status={$status}";
		}
		if ($begin_time) {
			$condition [] = "begin_time>='{$begin_time}'";
		}
		if ($end_time) {
			$condition [] = "begin_time <='{$end_time}'";
		}
		$sort = 'ORDER BY update_time desc';
		$info = \core\ActivityDao::GetInstance ()->Query ( $condition, '*', $sort, $offset, $limit );
		$info_count = \core\ActivityDao::GetInstance ()->Query ( $condition, '*', $sort, NULL, NULL );
		$response->data ['list'] = $info;
		$response->data ['totalCnt'] = count ( $info_count );
		return $response;
	}
	
	/**
	 * *操作日志列表
	 */
	public function getActionLog() {
		$response = new Response ();
		$planId = HttpRequestHelper::RequestParam ( 'planId', NULL, 'int' );
		if ($planId == NULL) {
			$response->status = ErrorMap::E_INVALIDATE_INPUT;
			$response->msg = ErrorMap::GetErrorMsg ( $response->status );
			return $response;
		}
		$condition = array ();
		$condition [] = "activity_id={$planId}";
		$sort = 'ORDER BY time';
		$info = \core\ActivityActionLogDao::GetInstance ()->Query ( $condition, '*', $sort, NULL, NULL );
		$response->data = $info;
		return $response;
	}
	
	/**
	 * *状态更新
	 */
	public function updateStatus() {
		$response = new Response ();
		$planId = HttpRequestHelper::RequestParam ( 'planId', NULL, 'int' );
		$status = HttpRequestHelper::RequestParam ( 'status', NULL, 'int' );
		$submitter = HttpRequestHelper::RequestParam ( 'submitter', NULL );
		if ($planId == NULL || $status == NULL || $submitter == NULL) {
			$response->status = ErrorMap::E_INVALIDATE_INPUT;
			$response->msg = ErrorMap::GetErrorMsg ( $response->status );
			return $response;
		}
		$behavior = array (
				'update_time' => date ( 'Y-m-d H:i:s' ),
				'status' => $status 
		);
		$condition = array (
				'id' => $planId 
		);
		if (\core\ActivityDao::GetInstance ()->Update ( $behavior, $condition ) == false) {
			$response->status = ErrorMap::E_ACTIVITY_UPDATESTATUS_ERROR;
			$response->msg = ErrorMap::GetErrorMsg ( $response->status );
			return $response;
		}
		$this->saveActionLog ( $planId, $submitter, $status );
		return $response;
	}
	
	/**
	 * *获取详情
	 */
	public function searchDetail() {
		$response = new Response ();
		$planId = HttpRequestHelper::RequestParam ( 'planId', NULL, 'int' );
		if ($planId == NULL) {
			$response->status = ErrorMap::E_INVALIDATE_INPUT;
			$response->msg = ErrorMap::GetErrorMsg ( $response->status );
			return $response;
		}
		$info = \core\ActivityDao::GetInstance ()->Get ( $planId );
		if ($info) {
			$info = $info ['0'];
			// 获取时段数据
			$info ['interval_info'] = $this->getInterval ( $planId );
			$info ['activity_prize'] = $this->getPrize ( $planId );
		} else {
			$response->status = ErrorMap::E_ACTIVITY_GET_ERROR;
			$response->msg = ErrorMap::GetErrorMsg ( $response->status );
			return $response;
		}
		$response->data = $info;
		return $response;
	}
	
	/**
	 * *查询是否有重复数据
	 */
	public function getRepeat($alias, $planId) {
		$condition [] = "alias='{$alias}'";
		if ($planId)
			$condition [] = "id !='{$planId}'";
		$info = \core\ActivityDao::GetInstance ()->Query ( $condition, 'id', NULL, 0, 1 );
		return $info;
	}
	
	/**
	 * *保存基础数据
	 */
	public function saveBaseInfo($data) {
		$planId = 0;
		if (\core\ActivityDao::GetInstance ()->Insert ( $data ) == false) {
			return $planId;
		}
		$planId = \core\ActivityDao::GetInstance ()->GetInsertID ();
		// 记录操作日志
		$this->saveActionLog ( $planId, $data ['submitter'], $data ['status'] );
		return $planId;
	}
	
	/**
	 * *更新基础数据
	 */
	public function updateBaseInfo($data, $planId) {
		$condition = array (
				'id' => $planId 
		);
		
		if (\core\ActivityDao::GetInstance ()->Update ( $data, $condition ) == false) {
			return false;
		}
		// 记录操作日志
		$this->saveActionLog ( $planId, $data ['submitter'], $data ['status'] );
		return true;
	}
	
	/**
	 * *保存时段关联数据
	 */
	public function saveActivityTime($interval, $planId) {
		$arr = array ();
		$interval = json_decode ( $interval );
		foreach ( $interval as $value ) {
			$arr [] = array (
					'activity_id' => $planId,
					'begin_time' => strtotime ( $value->begin_time ) * 1000,
					'end_time' => strtotime ( $value->end_time ) * 1000 
			);
		}
		if (\core\ActivityTimeDao::GetInstance ()->InsertList ( $arr ) == false) {
			return false;
		}
		return true;
	}
	
	/**
	 * *更新时段关联数据
	 */
	public function updateActivityTime($interval, $planId) {
		$arr = array ();
		$info = $this->getInterval ( $planId );
		$interval = json_decode ( $interval );
		$info_count = count ( $info );
		foreach ( $interval as $key => $value ) {
			if ($key + 1 > $info_count) {
				$id = '';
			} else {
				$id = $info [$key] ['id'];
				unset ( $info [$key] );
			}
			$arr [] = array (
					'id' => $id,
					'activity_id' => $planId,
					'begin_time' => strtotime ( $value->begin_time ) * 1000,
					'end_time' => strtotime ( $value->end_time ) * 1000 
			);
		}
		if (\core\ActivityTimeDao::GetInstance ()->Sets ( $arr ) == false) {
			return false;
		}
		// 删掉多余数据
		if ($info) {
			foreach ( $info as $v ) {
				\core\ActivityTimeDao::GetInstance ()->Del ( $v ['id'] );
			}
		}
		return true;
	}
	
	/**
	 * *保存奖品数据
	 */
	public function saveActivityPrize($prize, $planId) {
		$prize = json_decode ( $prize );
		$day_type = 0;
		foreach ( $prize as $value ) {
			if ($value->day_num > 0)
				$day_type = 1;
			$arr = array (
					'name' => $value->name,
					'activity_id' => $planId,
					'piaza_id' => $value->piaza_id,
					'piaza_name' => $value->piaza_name,
					'price' => $value->price,
					'total_num' => $value->total_num,
					'total_margin' => $value->total_num,
					'day_type' => $day_type,
					'day_num' => $value->day_num,
					'day_margin' => $value->day_num,
					'win_rate' => $value->win_rate 
			);
			if (\core\ActivityPrizeDao::GetInstance ()->Insert ( $arr ) == false) {
				return false;
			} else {
				$prizeId = \core\ActivityPrizeDao::GetInstance ()->GetInsertID ();
				if ($value->coupon) {
					$list = array ();
					foreach ( $value->coupon as $v ) {
						$list [] = array (
								'activity_id' => $planId,
								'prize_id' => $prizeId,
								'no' => $v->no,
								'title' => $v->title,
								'piaza_id' => $value->piaza_id,
								'end_date' => $v->end_date,
								'reward_num' => $v->reward_num,
								'stock' => $v->stock 
						);
					}
					if (\core\ActivityCouponDao::GetInstance ()->InsertList ( $list ) == false) {
						return false;
					}
				}
			}
		}
		return true;
	}
	
	/**
	 * *更新奖品数据
	 */
	public function updateActivityPrize($prize, $planId) {
		$prize = json_decode ( $prize );
		$prize_info = $this->getPrize ( $planId );
		$info_count = count ( $prize_info );
		$day_type = 0;
		foreach ( $prize as $key => $value ) {
			$arr =array();
			if ($key + 1 > $info_count) {
				$prize_id = '';
			} else {
				$prize_id = $prize_info [$key] ['id'];
				unset ( $prize_info [$key] );
			}
			if ($value->day_num > 0) $day_type = 1;
			$arr[] = array (
				'id' => $prize_id,
				'name' => $value->name,
				'activity_id' => $planId,
				'piaza_id' => $value->piaza_id,
				'piaza_name' => $value->piaza_name,
				'price' => $value->price,
				'total_num' => $value->total_num,
				'total_margin' => $value->total_num,
				'day_type' => $day_type,
				'day_num' => $value->day_num,
				'day_margin' => $value->day_num,
				'win_rate' => $value->win_rate 
			);
			if (\core\ActivityPrizeDao::GetInstance ()->Sets ( $arr ) == false) {
				return false;
			}
			if ($value->coupon) {
				if (!$prize_id){
					$prize_id = \core\ActivityPrizeDao::GetInstance ()->GetInsertID ();
				}
				$coupon = $this->getCoupon ( $planId, $prize_id );
				$coupon_count = count ( $coupon );
				$list = array ();
				foreach ( $value->coupon as $k => $v ) {
					if ($k + 1 > $coupon_count) {
						$coupon_id = '';
					} else {
						$coupon_id = $coupon [$k] ['id'];
						unset ( $coupon [$k] );
					}
					$list [] = array (
							'id' => $coupon_id,
							'activity_id' => $planId,
							'prize_id' => $prize_id,
							'no' => $v->no,
							'title' => $v->title,
							'piaza_id' => $value->piaza_id,
							'end_date' => $v->end_date,
							'reward_num' => $v->reward_num,
							'stock' => $v->stock 
					);
				}
				if (\core\ActivityCouponDao::GetInstance ()->Sets ( $list ) == false) {
					return false;
				}
				// 先删除优惠券多余数据
				if ($coupon) {
					foreach ( $coupon as $v1 ) {
						\core\ActivityCouponDao::GetInstance ()->Del ( $v1 ['id'] );
					}
				}
			}
		}
		// 后删除掉多余奖品数据
		if ($prize_info) {
			foreach ( $prize_info as $v1 ) {
				\core\ActivityPrizeDao::GetInstance ()->Del ( $v1 ['id'] );
				// 删除关联的优惠券数据
				$coupon_del = $this->getCoupon ( $v1 ['activity_id'], $v1 ['id'] );
				if ($coupon_del) {
					foreach ( $coupon_del as $v2 ) {
						\core\ActivityCouponDao::GetInstance ()->Del ( $v2 ['id'] );
					}
				}
			}
		}
		return true;
	}
	
	/**
	 * *保存操作行为日志
	 */
	public function saveActionLog($planId, $submitter, $action) {
		switch ($action) {
			case '1' :
				$action = '保存';
				break;
			case '2' :
				$action = '提交';
				break;
			case '3' :
				$action = '同意';
				break;
			case '4' :
				$action = '停止';
				break;
			case '-1' :
				$action = '驳回';
				break;
			default :
				$action = '保存';
				break;
		}
		$data = array (
				'activity_id' => $planId,
				'time' => date ( 'Y-m-d H:i:s', time () ),
				'submitter' => $submitter,
				'action' => $action 
		);
		\core\ActivityActionLogDao::GetInstance ()->Insert ( $data );
	}
	
	/**
	 * *获取时段详情
	 */
	public function getInterval($planId) {
		$condition [] = "activity_id={$planId}";
		$info = \core\ActivityTimeDao::GetInstance ()->Query ( $condition, '*', NULL, NULL, NULL );
		if ($info) {
			foreach ( $info as $key => $value ) {
				$info [$key] ['begin_time'] = date ( 'Y-m-d H:i', $value ['begin_time'] / 1000 );
				$info [$key] ['end_time'] = date ( 'Y-m-d H:i', $value ['end_time'] / 1000 );
			}
		}
		return $info;
	}
	
	/**
	 * *获取奖品详情
	 */
	public function getPrize($planId) {
		$condition [] = "activity_id={$planId}";
		$info = \core\ActivityPrizeDao::GetInstance ()->Query ( $condition, '*', NULL, NULL, NULL );
		if ($info) {
			foreach ( $info as $key => $value ) {
				$info [$key] ['coupon'] = $this->getCoupon ( $planId, $value ['id'] );
			}
		}
		return $info;
	}
	
	/**
	 * *获取优惠券详情
	 */
	public function getCoupon($planId, $prizeId) {
		$condition [] = "activity_id={$planId}";
		$condition [] = "prize_id={$prizeId}";
		$info = \core\ActivityCouponDao::GetInstance ()->Query ( $condition, '*', NULL, NULL, NULL );
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
