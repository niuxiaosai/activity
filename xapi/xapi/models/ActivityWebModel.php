<?php
/*
* creator: hexuan
*/
require_once (WEB_ROOT . 'models/extra/BaseModel.php');
require_once (FFAN_ROOT . 'dao/ActivityDao.php');
require_once (FFAN_ROOT . 'dao/ActivityActionLogDao.php');
require_once (FFAN_ROOT . 'dao/ActivityLogDao.php');
require_once (FFAN_ROOT . 'dao/ActivityWinnerDao.php');
require_once (FFAN_ROOT . 'dao/ActivityTimeDao.php');
require_once (FFAN_ROOT . 'dao/ActivityPrizeDao.php');
require_once (FFAN_ROOT . 'dao/ActivityCouponDao.php');
require_once(PHP_ROOT . '/libs/util/HttpHandlerCurl.php');

class ActivityWebModel extends BaseModel {
	const UCENTER_KEYWORD_TYPE = 0;
	const UCENTER_APPID = 'movie_backend';
	public function GetResponse() {
	}
	/**
	 * *获取详情
	 */
	public function getActivity() {
		$response = new Response ();
		$now = date ( 'Y-m-d H:i:s', time () );
		$alias = HttpRequestHelper::RequestParam ( 'alias', NULL );
		if ($alias == NULL) {
			$response->status = ErrorMap::E_INVALIDATE_INPUT;
			$response->msg = ErrorMap::GetErrorMsg ( $response->status );
			return $response;
		}
		$condition = array (
		"alias='{$alias}'"
		);
		$sort = 'ORDER BY update_time desc';
		$info = \core\ActivityDao::GetInstance ()->Query ( $condition, '*', $sort, 0, 1 );
		if ($info) {
			$info = $info ['0'];
			// 验证当前活动的状态
			$status1 = $this->checkStatus1 ( $info,$now );
			if ($status1 ['status'] != 6) {
				$response->data = $status1;
				return $response;
			}
			// 验证是否在可用时间范围内
			$status2 = $this->checkStatus2 ( $info, $now );
			if ($status2 ['status'] != 6) {
				$response->data = $status2;
				return $response;
			}
			// 验证总库存
			$status3 = $this->checkStatus3 ( $info ['id'], NULL );
			if ($status3 ['status'] != 6) {
				$response->data = $status3;
				return $response;
			}
			// 当前时间是否在具体哪个时间段内
			$status4 = $this->checkStatus4 ( $info ['id'], $now );
			if ($status4 ['status'] != 6) {
				unset ( $status4 ['intervalId'] );
				$response->data = $status4;
				return $response;
			}
			$status5 = $this->checkStatus5 ( $info ['id'], $status4 ['intervalId'], $now, NULL );
		} else {
			$response->status = ErrorMap::E_ACTIVITY_GET_ERROR;
			$response->msg = ErrorMap::GetErrorMsg ( $response->status );
			return $response;
		}
		$response->data = $status5;
		return $response;
	}

	/**
	 * *抽奖
	 */
	public function lottery() {
		$response = new Response ();

		if($this->isLock(intval(HttpRequestHelper::RequestParam ( 'uid', NULL )))){
			$res = array (
				'status' => 8,
				'nextStartTime' => 0,
				'diffTime'=>0,
				'source'=>HttpRequestHelper::RequestParam ( 'sourse', NULL ) ,
			);
			$response->data ['isWinner'] = 0;
			$response->data ['prize'] = array ();
			$response->data ['status'] = $res;
			return $response;
		}

		$now = date ( 'Y-m-d H:i:s', time () );
		$date = date ( 'Y-m-d', time () );
		$winNum = 1;
		$alias = HttpRequestHelper::RequestParam ( 'alias', NULL );
		$uid = HttpRequestHelper::RequestParam ( 'uid', NULL );
		$piazaId = HttpRequestHelper::RequestParam ( 'piazaId', NULL );
		$sourse = HttpRequestHelper::RequestParam ( 'sourse', NULL );
		$signIn = HttpRequestHelper::RequestParam ( 'signIn', NULL );
		// 过滤参数
		$alias = $this->msEscapeStr ( $alias );
		$uid = $this->msEscapeInt ( $uid );
		$piazaId = $this->msEscapeInt ( $piazaId );
		$sourse = $this->msEscapeInt ( $sourse );
		$signIn = $this->msEscapeInt ( $signIn );
		if ($alias == NULL || $uid == NULL || $piazaId == NULL || $sourse == NULL || $signIn == NULL) {
			$response->status = ErrorMap::E_INVALIDATE_INPUT;
			$response->msg = ErrorMap::GetErrorMsg ( $response->status );
			return $response;
		}
		$condition = array (
		"alias='{$alias}'"
		);
		$sort = 'ORDER BY update_time desc';
		$info = \core\ActivityDao::GetInstance ()->Query ( $condition, '*', $sort, 0, 1 );
		if ($info) {
			$info = $info ['0'];
			// 验证当前活动的状态
			$status1 = $this->checkStatus1 ( $info, $now);
			if ($status1 ['status'] != 6) {
				$response->data ['isWinner'] = 0;
				$response->data ['prize'] = array ();
				$response->data ['status'] = $status1;
				return $response;
			}
			// 验证是否在可用时间范围内
			$status2 = $this->checkStatus2 ( $info, $now );
			if ($status2 ['status'] != 6) {
				$response->data ['isWinner'] = 0;
				$response->data ['prize'] = array ();
				$response->data ['status'] = $status2;
				return $response;
			}
			// 验证总库存
			$status3 = $this->checkStatus3 ( $info ['id'], $piazaId );
			if ($status3 ['status'] != 6) {
				$response->data ['isWinner'] = 0;
				$response->data ['prize'] = array ();
				$response->data ['status'] = $status3;
				return $response;
			}
			// 当前时间是否在具体哪个时间段内
			$status4 = $this->checkStatus4 ( $info ['id'], $now );
			if ($status4 ['status'] != 6) {
				unset ( $status4 ['intervalId'] );
				$response->data ['isWinner'] = 0;
				$response->data ['prize'] = array ();
				$response->data ['status'] = $status4;
				return $response;
			}
			// 验证用户达到每周可抢次数限制，或达到每个周期可参加活动次数限制
			$topLimit = $this->topLimit ( $info ['id'], $uid, $status4 ['intervalId'] );
			if ($topLimit['status'] == '7') {
				$response->data ['isWinner'] = 0;
				$response->data ['prize'] = array ();
				$response->data ['status'] = $topLimit;
				return $response;
			} elseif ($topLimit['status'] == '8') {
				$response->data ['isWinner'] = 0;
				$response->data ['prize'] = array ();
				$response->data ['status'] = $topLimit;
				return $response;
			}
			// 验证是否还有每日余量，决定最终状态及是否可抽奖
			$status5 = $this->checkStatus5 ( $info ['id'], $status4 ['intervalId'], $now, $piazaId );
			if ($status5 ['status'] != 6) {
				$response->data ['isWinner'] = 0;
				$response->data ['prize'] = array ();
				$response->data ['status'] = $status5;
				return $response;
			}

			//add by zyw begin
			//判断活动时间段内可以中几次奖：1根据时间段和 log里的日志 2然后当前时间段去做判断
			$intervalId = $status4 ['intervalId'] ;//当前时间段内的时间id
			$activity_id = $info['id'];
			$curr_can_win_num = \core\ActivityTimeDao::GetInstance ()->Query (array ( "activity_id={$activity_id}"  ), 'count(id) as count', NULL, NULL,1 );
			$curr_user_win_num = \core\ActivityLogDao::GetInstance ()->Query ( array ( "activity_id='{$activity_id}'", "user_id ='{$uid}'", "status =1" ), 'count(id) as count', NULL, NULL,1 );

			$curr_can_win_num = $curr_can_win_num['0']['count'];
			$curr_user_win_num = $curr_user_win_num['0']['count'];
			//用户中过一次且用户中奖大于等于当前最大中奖次数
			if($curr_user_win_num>=$curr_can_win_num && $curr_user_win_num>0)
			{
				$res = array();
				$res['status'] =7;
				$res['nextStartTime'] =0;
				$res['diffTime']= 0 ;
				$res['source']= "byzyw" ;
				$response->data ['isWinner'] = 0;
				$response->data ['prize'] = array ();
				$response->data ['status'] = $res;
				return $response;
			}


			$interval_info = \core\ActivityTimeDao::GetInstance ()->Get ( $intervalId );
			$interval_info = $interval_info ['0'];
			$condition = array ( "activity_id={$activity_id}", "user_id ='{$uid}'", "time>='{$interval_info['begin_time']}'", "time<='{$interval_info['end_time']}'");
			// 每个周期只允许中奖1次--特殊逻辑（强制性）
			$winInfo_week = \core\ActivityWinnerDao::GetInstance ()->Query ( $condition, '*', NULL, NULL, NULL );
			if (count ( $winInfo_week ) >= 1) {
				$res = array (
				'status' => 8,
				'nextStartTime' => 0,
				'diffTime'=>0,
				'source'=>"byzyw" ,
				);
				$response->data ['isWinner'] = 0;
				$response->data ['prize'] = array ();
				$response->data ['status'] = $res;
				return $response;
			}
			//add by zyw end

			// 调用抽奖方法
			$result = $this->useLottery ( $info ['id'], $uid, $piazaId, $sourse, $signIn );
		} else {
			$response->status = ErrorMap::E_ACTIVITY_GET_ERROR;
			$response->msg = ErrorMap::GetErrorMsg ( $response->status );
			return $response;
		}
		return $result;
	}

	// 判断并发，并加锁
	private function isLock($uid)
	{
		if(empty($uid)){
			return true;
		}

		require_once(PHP_ROOT . 'libs/util/MemCachedClient.php');
		$key = "zzq_v1_locl_{$uid}";
		$mc = MemCachedClient::GetInstance('default');
		$cas = null;
		$lock = $mc->get($key, null, $cas);
		// 如果有锁
		if ($lock) {
			return true;
		}
		// 如果没有锁
		$mc->add($key, 1, 1);
		if ($mc->getResultCode() == Memcached::RES_NOTSTORED) {
			return true;
		} else {
			return false;
		}
	}

	public function useLottery($planId, $uid, $piazaId, $sourse, $signIn) {
		$response = new Response ();
		$rand = mt_rand ( 1, 100 );
		$i = - 1;
		// 查找活动对应奖品，有余量的奖品
		$prize_info = $this->getPrize ( $planId, 'total', $piazaId );
		foreach ( $prize_info as $key => $value ) {
			if ($value ['day_type'] == '1') {
				if ($value ['day_margin'] <= '0')
				unset ( $prize_info [$key] );
			}
		}
		foreach ( $prize_info as $k => $v ) {
			if ($rand <= $v ['win_rate'])
			$i = $k;
		}
		$userInfo = $this->getUserInfo ( $uid );
		$this->saveLog ( $planId, $userInfo, $piazaId, $sourse, $signIn, $i, $prize_info, $uid );
		if ($i > - 1) { // 中奖更新库存，记录中奖流水
			$res = $this->updateMargin ( $prize_info [$i] );
			if ($res == true) {
				// 推送优惠券
				$result=$this->pushAllocates ( $planId, $prize_info [$i], $uid );
				if(!$result){
					$response->data ['isWinner'] = 0;
					$response->data ['prize'] = array ();
				}else{
					$this->saveWinner ( $planId, $userInfo, $piazaId, $sourse, $prize_info [$i], $uid );
					$response->data ['isWinner'] = 1;
					$response->data ['prize'] = array (
					'name' => $prize_info [$i] ['name'],
					'price' => $prize_info [$i] ['price']
					);
				}
				$response->data ['status'] = array('status'=>0,'nextStartTime'=>0,'diffTime'=>0);
			}
		} else {
			$response->data ['isWinner'] = 0;
			$response->data ['prize'] = array ();
			$response->data ['status'] = array('status'=>0,'nextStartTime'=>0,'diffTime'=>0);
		}
		return $response;
	}

	/**
	 * *保存Log
	 */
	public function saveLog($planId, $userInfo, $piazaId, $sourse, $signIn, $i, $prize, $uid) {
		$arr = array ();
		if ($i > - 1) {
			$arr ['status'] = 1;
			$arr ['prize_id'] = $prize [$i] ['id'];
			// 加入coupon_no,便于大数据做统计
			$coupon_list = $this->getCoupon ( $planId, $prize [$i] ['id'] );
			if ($coupon_list) {
				$coupon_no = '';
				foreach ( $coupon_list as $value ) {
					$coupon_no .= $value ['no'] . ',';
				}
				$coupon_no = substr ( $coupon_no, 0, - 1 );
				$arr ['coupon_no'] = $coupon_no;
			}
		} else {
			$arr ['status'] = 0;
		}
		if ($userInfo) {
			$arr ['user_id'] = $userInfo ['uid'];
			$arr ['user_name'] = $userInfo ['nickName'];
			$arr ['user_mobile'] = $userInfo ['mobile'];
		}else{
			$arr ['user_id'] = $uid;
		}

		if ($signIn)
		$arr ['channel'] = 1;
		$arr ['time'] = time () * 1000;
		$arr ['activity_id'] = $planId;
		$arr ['sourse'] = $sourse;
		$arr ['piaza_id'] = $piazaId;
		\core\ActivityLogDao::GetInstance ()->Insert ( $arr );
	}

	/**
	 * *保存Log
	 */
	public function saveWinner($planId, $userInfo, $piazaId, $sourse, $prize, $uid) {
		$arr = array ();
		$arr ['prize_id'] = $prize ['id'];
		$arr ['prize_name'] = $prize ['name'];
		if ($userInfo) {
			$arr ['user_id'] = $userInfo ['uid'];
			$arr ['user_name'] = $userInfo ['nickName'];
			$arr ['user_mobile'] = $userInfo ['mobile'];
		}else{
			$arr ['user_id'] = $uid;
		}
		$arr ['time'] = time () * 1000;
		$arr ['activity_id'] = $planId;
		$arr ['sourse'] = $sourse;
		$arr ['piaza_id'] = $piazaId;
		\core\ActivityWinnerDao::GetInstance ()->Insert ( $arr );
	}

	/**
	 * *更新库存
	 */
	public function updateMargin($prize) {
		// 是否每日限量 限量更新总量及每日库存，不限量更新总库存
		if ($prize ['day_type'] == '1') {
			$sql = "update activity_prize set total_margin = total_margin-1,day_margin = day_margin-1 where id = '{$prize['id']}' and total_margin>0 and day_margin>0";
		} else {
			$sql = "update activity_prize set total_margin = total_margin-1 where id = '{$prize['id']}' and total_margin>0";
		}
		if (\core\ActivityPrizeDao::GetInstance ()->UpdateBySql ( $sql ) == false) {
			return false;
		}

		return true;
	}

	/**
	 * *获取会员信息
	 */
	public function getUserInfo($userId) {
		$url = UCENTER_ADDR . $userId;
		$params = array (
		'keywordType' => self::UCENTER_KEYWORD_TYPE,
		'appid' => self::UCENTER_APPID
		);
		$curl = new HttpHandlerCurl ();
		$content = $curl->get ( $url . '?' . http_build_query ( $params ) );
		Log::Info ( var_export ( "会员信息>>>" . $content, true ) );
		if (! $content)
		return false;
		$user_info = json_decode ( $content, true );
		if ($user_info ['status'] != 0)
		return false;
		$user_info = $user_info ['data'];
		return $user_info;
	}

	/**
	 * *推送优惠券
	 */
	public function pushAllocates($planId, $prize, $userId) {
		$result = $this->getCoupon ( $planId, $prize ['id'] );
		if ($result) {
			$i=0;
			$count = count($result);
			foreach ( $result as $key => $value ) {
				$res=$this->pushAllocate ( $value ['no'], $userId, $value ['reward_num'] );
				if(!$res) $i++;
			}
			if($i>=$count) return false;
		}else{
			return false;
		}
		return true;
	}

	/**
	 * *调用优惠券接口
	 */
	public function pushAllocate($productNo, $userId, $reward_num) {
		$url = TRADE_ALLOCATE;
		$params = array (
		'productNo' => $productNo,
		'memberId' => $userId,
		'count' => $reward_num
		);
		$curl = new HttpHandlerCurl ();
		$content = $curl->post ( $url, $params );
		Log::Info ( var_export ( "调用优惠券接口入参>>>" . "productNo" . $productNo . "userId" . $userId, true ) );
		Log::Info ( var_export ( "调用优惠券接口返回结果>>>" . $content, true ) );
		if (! $content)
		return false;
		$info = json_decode ( $content, true );
		if ($info ['status'] != 200)
		return false;
		return true;
	}

	/**
	 * *验证用户达到每周可抢次数限制，或达到每个周期可参加活动次数限制
	 * *回参status：1-正常，7-已达到次数限制,8-此周期已经抢到过一次
	 */
	public function topLimit($planId, $uid, $intervalId) {
		$status = 1;
		$nextStartTime = 0;
		$diffTime = 0;
		$info = \core\ActivityDao::GetInstance ()->Get ( $planId );
		$info = $info ['0'];
		$interval_info = \core\ActivityTimeDao::GetInstance ()->Get ( $intervalId );
		$interval_info = $interval_info ['0'];
		$condition = array (
		"activity_id={$planId}",
		"user_id ='{$uid}'",
		"time>='{$interval_info['begin_time']}'",
		"time<='{$interval_info['end_time']}'"
		);
		if ($info ['hit_count']) {
			// 验证可点击次数
			$LogInfo = \core\ActivityLogDao::GetInstance ()->Query ( $condition, '*', NULL, NULL, NULL );
			if (count ( $LogInfo ) >= $info ['hit_count']) {
				$status = 7;
				$res = array (
				'status' => $status,
				'nextStartTime' => $nextStartTime,
				'diffTime'=>$diffTime
				);
				return $res;
			}
		}
		if ($info ['win_count']) {
			$map = $condition;
			unset ( $map ['2'], $map ['3'] );
			$winInfo_all = \core\ActivityWinnerDao::GetInstance ()->Query ( $map, '*', NULL, NULL, NULL );
			if (count ( $winInfo_all ) >= $info ['win_count']) {
				$status = 7;
				$res = array (
				'status' => $status,
				'nextStartTime' => $nextStartTime,
				'diffTime'=>$diffTime
				);
				return $res;
			}
		}
		// 每个周期只允许中奖1次--特殊逻辑（强制性）
		$winInfo_week = \core\ActivityWinnerDao::GetInstance ()->Query ( $condition, '*', NULL, NULL, NULL );
		if (count ( $winInfo_week ) >= 1) {
			$status = 8;
			$res = array (
			'status' => $status,
			'nextStartTime' => $nextStartTime,
			'diffTime'=>$diffTime
			);
			return $res;
		}

		$res = array (
		'status' => $status,
		'nextStartTime' => $nextStartTime,
		'diffTime'=>$diffTime
		);
		return $res;
	}

	/**
	 * *验证活动状态1--当前状态
	 */
	public function checkStatus1($data,$now) {
		$status = 6;
		$nextStartTime = 0;
		$diffTime = 0;
		if ($data ['status'] < 3) {
			$status = 1;
			$interval = $this->getInterval ( $data ['id'] );
			if ($interval) $nextStartTime = floatval($interval ['0'] ['begin_time']);
			$diffTime = $nextStartTime-strtotime($now)*1000;
		} elseif ($data ['status'] > 3) {
			$status = 2;
		}
		$res = array (
		'status' => $status,
		'nextStartTime' => $nextStartTime,
		'diffTime'=>$diffTime
		);
		return $res;
	}

	/**
	 * *验证活动状态2--是否在可用时间范围内
	 */
	public function checkStatus2($data, $now) {
		$status = 6;
		$nextStartTime = 0;
		$diffTime = 0;
		if ($now < $data ['begin_time']) {
			$status = 1;
			$interval = $this->getInterval ( $data ['id'] );
			if ($interval) $nextStartTime = floatval($interval ['0'] ['begin_time']);
			$diffTime = $nextStartTime-strtotime($now)*1000;
		} elseif ($now > $data ['end_time']) {
			$status = 2;
		}
		$res = array (
		'status' => $status,
		'nextStartTime' => $nextStartTime,
		'diffTime' => $diffTime
		);
		return $res;
	}
	/**
	 * *验证活动状态3--验证总库存
	 */
	public function checkStatus3($planId, $piazaId) {
		$status = 6;
		$nextStartTime = 0;
		$diffTime = 0;
		$info = $this->getPrize ( $planId, 'total', $piazaId );
		if (! $info) {
			$status = 2;
		}
		$res = array (
		'status' => $status,
		'nextStartTime' => $nextStartTime,
		'diffTime' =>$diffTime
		);
		return $res;
	}
	/**
	 * *验证活动状态4--当前时间是否在具体哪个时间段内
	 * *返回参数里面增加intervalId字段，用于处理后续判断逻辑
	 */
	public function checkStatus4($planId, $now) {
		$status = 6;
		$nextStartTime = 0;
		$diffTime = 0;
		$now = strtotime ( $now ) * 1000;
		$interval = $this->getInterval ( $planId );
		$count = count ( $interval );
		$intervalId = $i = 0;
		foreach ( $interval as $key => $value ) {
			if ($i == 1)
			continue;
			if ($key == 0) {
				if ($now < $value ['begin_time']) {
					$status = 1;
					$nextStartTime = floatval ( $value ['begin_time'] );
					$diffTime = $nextStartTime-$now;
					$i = 1;
					continue;
				}
			} elseif ($key == $count - 1) {
				if ($now > $value ['end_time']) {
					$status = 2;
					$i = 1;
					continue;
				}
			}
			if ($now >= $value ['begin_time'] && $now <= $value ['end_time']) {
				$intervalId = ( int ) $value ['id'];
				$i = 1;
				continue;
			}
		}
		if ($i == 0 && $intervalId == 0) {
			$status = 3;
			if(count($interval)<=1){
				$status = 2;
			}else{
				foreach ( $interval as $k => $v ) {
					if ($i == 1)
					continue;
					if ($now > $v ['end_time'] && $now < $interval [$k + 1] ['begin_time']) {
						$nextStartTime = floatval ( $interval [$k + 1] ['begin_time'] );
						$diffTime = $nextStartTime-$now;
						$i = 1;
						continue;
					}
				}
			}

		}
		$res = array (
		'status' => $status,
		'nextStartTime' => $nextStartTime,
		'diffTime' =>$diffTime,
		'intervalId' => $intervalId
		);
		return $res;
	}
	/**
	 * *验证活动状态5--验证当前日期是否还有每日余量
	 */
	public function checkStatus5($planId, $intervalId, $now, $piazaId) {
		$now = date ( 'Y-m-d', strtotime ( $now ) );
		$status = 6;
		$nextStartTime = 0;
		$diffTime = 0;
		$i = 0;
		$info = $this->getPrize ( $planId, 'total', $piazaId );
		foreach ( $info as $key => $value ) {
			if ($i == 1)
			continue;
			if ($value ['day_type'] == '0') {
				$i = 1;
				continue;
			} else {
				if ($value ['day_margin'] > 0) {
					$i = 1;
					continue;
				}
			}
		}
		if ($i == 0) {
			$interval_info = \core\ActivityTimeDao::GetInstance ()->Get ( $intervalId );
			if ($interval_info) {
				$end_date = date ( 'Y-m-d', $interval_info ['0'] ['end_time'] / 1000 );
				if ($now == $end_date) {
					$condition = array (
					"activity_id={$planId}",
					"begin_time>'{$interval_info['0']['end_time']}'"
					);
					$sort = 'ORDER BY begin_time';
					$interval_next = \core\ActivityTimeDao::GetInstance ()->Query ( $condition, '*', $sort, 0, 1 );
					if ($interval_next) {
						$status = 5;
						$nextStartTime = floatval($interval_next ['0'] ['begin_time']);
						$diffTime = $nextStartTime-time()*1000;
					} else {
						$status = 2;
					}
				} else {
					$status = 4;
				}
			}
		}
		$res = array (
		'status' => $status,
		'nextStartTime' => $nextStartTime,
		'diffTime' =>$diffTime
		);
		return $res;
	}

	/**
	 * *获取时段详情
	 */
	public function getInterval($planId) {
		$condition [] = "activity_id={$planId}";
		$sort = 'ORDER BY begin_time';
		$info = \core\ActivityTimeDao::GetInstance ()->Query ( $condition, '*', $sort, NULL, NULL );
		return $info;
	}

	/**
	 * *获取奖品详情
	 */
	public function getPrize($planId, $where, $piazaId) {
		$condition = array ();
		$condition [] = "activity_id={$planId}";
		if ($piazaId)
		$condition [] = "piaza_id='{$piazaId}'";
		if ($where == 'total')
		$condition [] = "total_margin>0";
		$sort = "ORDER BY win_rate";
		$info = \core\ActivityPrizeDao::GetInstance ()->Query ( $condition, '*', $sort, NULL, NULL );
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
