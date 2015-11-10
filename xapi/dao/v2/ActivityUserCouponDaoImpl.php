<?php

require_once(PHP_ROOT . 'libs/util/MysqlClient.php');
require_once(PHP_ROOT . 'libs/util/HttpHandlerCurl.php');
require_once(PHP_ROOT . 'libs/util/MemCachedClient.php');
require_once(FFAN_ROOT . 'utils/ToolUtil.php');

class ActivityUserCouponDaoImpl
{

    const DB_NAME = "ff_cloud_marketing_platform";
    const TABLE_NAME = 'activity_v2_user_coupon';
    const MC_GROUP = 'default';

    protected static $table_fields_map_ = array(
        'id' => 'id',
        'activityId' => 'activity_id',
        'uid' => 'uid',
        'couponNumber' => 'coupon_number',
        'getTime' => 'get_time',
    );

    // 券的基本信息
    private $coupon_info = array('title' => '', 'type' => '', 'unit' => '');

    /**
     * 用户获得一张券
     *
     * @param  int $activity_id
     * @param  int $uid
     * @param  int $coupon_number
     * @param  string $captcha_id
     * @param  string $captcha
     * @param  string $source
     * @param  int $plaza_id
     * @return int 具体状态码
     */
    public function userGetCoupon($activity_id, $uid, $coupon_number, $captcha_id = null, $captcha = null, $source = null, $plaza_id = null)
    {
        try {
            // 使用事务和悲观锁处理并发
            MysqlClient::ExecuteUpdate(self::DB_NAME, 'START TRANSACTION');
            $this->isLock($uid);
            $this->isTodayAlreadyHave($uid, $coupon_number, $activity_id);
            $this->isCouponExpire($coupon_number);
            $this->checkActivityLimit($activity_id, $uid, $captcha_id, $captcha);
            $this->checkCouponLimit($activity_id, $coupon_number);
            $this->record($activity_id, $uid, $coupon_number, $source, $plaza_id);
            $last_id = MysqlClient::GetInsertID(self::DB_NAME);
            $result = MysqlClient::ExecuteUpdate(self::DB_NAME, 'COMMIT');
            if ($result) {
                $allocate = $this->allocateCoupon($uid, $coupon_number);
                if ($allocate != OK) {
                    // 如果发券失败，则清理数据库
                    $sql = "DELETE FROM `activity_v2_user_coupon` WHERE (`id`='{$last_id}')";
                    MysqlClient::ExecuteUpdate(self::DB_NAME, $sql);
                    return $allocate;
                }
                $this->todayHave($uid, $coupon_number);
                return OK;
            } else {
                Log::Warn("DB Commit Fail! Param: {$activity_id}, {$uid}, {$coupon_number}, {$source}, {$plaza_id}.");
                return FAIL;
            }
        } catch (Exception $e) {
            MysqlClient::ExecuteUpdate(self::DB_NAME, 'ROLLBACK');
            return $e->getCode();
        }


    }

    // 判断并发，并加锁
    private function isLock($uid)
    {
        if (empty($uid)) {
            throw new Exception('已经领过', HAVE);
        }

        require_once(PHP_ROOT . 'libs/util/MemCachedClient.php');
        $key = "zzq_v2_locl_{$uid}";
        $mc = MemCachedClient::GetInstance('default');
        $cas = null;
        $lock = $mc->get($key, null, $cas);
        // 如果有锁
        if ($lock) {
            throw new Exception('已经领过', HAVE);
        }
        // 如果没有锁
        $isOk = $mc->add($key, 1, 1);
        if ($mc->getResultCode() == Memcached::RES_NOTSTORED) {
            throw new Exception('已经领过', HAVE);
        }
        if (!$isOk) {
            throw new Exception('已经领过', HAVE);
        }
    }

    /**
     * 检查今天是否已经获取了某个券
     * @param $uid
     * @param $coupon_number
     * @param $activity_id
     * @throws Exception
     */
    private function isTodayAlreadyHave($uid, $coupon_number, $activity_id)
    {
        $mc = MemCachedClient::GetInstance(self::MC_GROUP);
        if ($mc->get($uid . $coupon_number . $activity_id)) {
            throw new Exception('已经领过', HAVE);
        } else {
            $today = date('Ymd');
            $sql = sprintf(
                "SELECT `id` FROM `activity_v2_user_coupon`
                    WHERE `activity_id`=%d AND `uid`='%s' AND `coupon_number`='%s' AND `get_day`=%d LIMIT 1
                    FOR UPDATE",
                $activity_id,
                ToolUtil::escapeSQL($uid),
                ToolUtil::escapeSQL($coupon_number),
                $today
            );
            $have = MysqlClient::ExecuteQuery(self::DB_NAME, $sql, 0, 1, 0);
            if (!empty($have)) {
                throw new Exception('已经领过', HAVE);
            }
        }
    }

    /**
     * 记录用户领取信息
     *
     * @param int $uid
     * @param int $coupon_number
     */
    private function todayHave($uid, $coupon_number)
    {
        $mc = MemCachedClient::GetInstance(self::MC_GROUP);
        $tomorrow = strtotime('tomorrow');
        $mc->set($uid . $coupon_number, 1, $tomorrow);
    }

    /**
     * 检查后台配置的活动限制
     *
     * @param  int $activity_id
     * @param  int $uid
     * @param  string $captcha_id
     * @param  string $captcha
     * @return int 状态码
     * @throws Exception
     */
    private function checkActivityLimit($activity_id, $uid, $captcha_id = null, $captcha = null)
    {
        // 活动起止时间、每天限制、活动期限制、验证码配置
        $activity_config_sql = sprintf(
            "SELECT `start_time`,`end_time`,`limit_per_day`,`limit_total`,`need_captcha`
                FROM `activity_v2_activity`
                WHERE `id`=%d LIMIT 1
                FOR UPDATE",
            $activity_id
        );
        $configs = MysqlClient::ExecuteQuery(self::DB_NAME, $activity_config_sql, 0, 1, 0);
        // 活动不存在
        if (!is_array($configs) || empty($configs[0])) {
            throw new Exception('活动不存在', NOT_START);
        }

        $activity_config = array_pop($configs);
        $current_time = time();
        // 未开始
        if ($activity_config['start_time'] > $current_time) {
            throw new Exception('活动未开始', NOT_START);
        }
        //已结束
        if ($activity_config['end_time'] < $current_time) {
            throw new Exception('活动已结束', EXPIRE);
        }

        // 用户活动期间获得的券以及用户当天获得券
        $user_have_sql = sprintf(
            "SELECT COUNT(`id`) AS `have`,`get_day`
                FROM `activity_v2_user_coupon`
                WHERE `activity_id`=%d AND `uid`='%s'
                GROUP BY `get_day`
                FOR UPDATE",
            $activity_id,
            ToolUtil::escapeSQL($uid)
        ); // TODO 使用活动起止时间优化索引
        $have = MysqlClient::ExecuteQuery(self::DB_NAME, $user_have_sql, 0, 1, 0);

        $user_have = array('total' => 0, 'day' => 0);
        foreach ((array)$have as $tmp) {
            $user_have['total'] += $tmp['have'];
            if ($tmp['get_day'] == date('Ymd')) {
                $user_have['day'] = $tmp['have'];
            }
        }
        // 达到了活动每天的限量或者达到了活动期间的限量
        if ((!empty($activity_config['limit_per_day']) && $activity_config['limit_per_day'] <= $user_have['day'])
            || (!empty($activity_config['limit_total']) && $activity_config['limit_total'] <= $user_have['total'])
        ) {
            throw new Exception('达到活动限制', CLOSED);
        }

        // 触发验证码
        if (!empty($activity_config['need_captcha']) && $activity_config['need_captcha'] <= ($user_have['day'] + 1)) {
            if (empty($captcha_id) || empty($captcha) || !$this->reCaptcha($captcha_id, $captcha)) {
                throw new Exception('需要验证码', NEED_CPATCHA);
            }
        }
    }

    /**
     * 检查后台配置的券的限制
     *
     * @param  int $activity_id
     * @param  int $coupon_number
     * @return int 状态码
     * @throws Exception
     */
    private function checkCouponLimit($activity_id, $coupon_number)
    {
        // 券的日限量、发放截止日期
        $coupon_limit_sql = sprintf(
            "SELECT `day_limit`,`end_time`,`unit`
                FROM `activity_v2_coupon`
                WHERE `activity_id`=%d AND `coupon_number`='%s' LIMIT 1
                FOR UPDATE",
            $activity_id,
            ToolUtil::escapeSQL($coupon_number)
        );
        $limits = MysqlClient::ExecuteQuery(self::DB_NAME, $coupon_limit_sql, 0, 1, 0);
        // 不存在
        if (empty($limits)) {
            throw new Exception('优惠券不存在', CLOSED);
        }
        $coupon_limit = array_pop($limits);
        // 已经过了发放日期
        if ($coupon_limit['end_time'] < time()) {
            throw new Exception('已经截止', EXPIRE);
        }
        $this->coupon_info['unit'] = $coupon_limit['unit'];

        // 没有限制日限量
        if (empty($coupon_limit['day_limit'])) {
            return OK;
        }
        // 当天发出的券
        $day_spend_sql = sprintf(
            "SELECT COUNT(`id`) AS `spend`
                FROM `activity_v2_user_coupon`
                WHERE `activity_id`=%d AND `coupon_number`='%s' AND `get_day`=%d
                FOR UPDATE",
            $activity_id,
            ToolUtil::escapeSQL($coupon_number),
            date('Ymd')
        );
        $spend = MysqlClient::ExecuteQuery(self::DB_NAME, $day_spend_sql, 0, 1, 0);
        if (empty($spend[0]['spend'])) {
            return OK;
        }
        $day_spend = $spend[0]['spend'];
        // 达到券的日限量
        if (($day_spend >= $coupon_limit['day_limit'])
            || ($day_spend + $coupon_limit['unit'] > $coupon_limit['day_limit'])
        ) {
            throw new Exception('达到日限量', OUT_OF_STOCK);
        }

        return OK;
    }

    private function isCouponExpire($coupon_number)
    {
        $http = new HttpHandlerCurl('UTF-8', 1);
        $url = GATEWAY_URL_ROOT . 'coupon/v2/products/' . $coupon_number . '?source=4';
        $result = json_decode($http->get($url), true);
        if (empty($result) || empty($result['data'])) {
            throw new Exception('优惠券已过期', EXPIRE); //券接口失败，认为已过期
        }

        $this->coupon_info['type'] = empty($result['data']['vendibility']) ? 2 : $result['data']['vendibility'];
        $this->coupon_info['title'] = $result['data']['title'];
        if (empty($result['data']['validPeriodType']) && !empty($result['data']['validEndTime'])) {
            if (time() > $result['data']['validEndTime']) {
                throw new Exception('优惠券已过期', EXPIRE);
            }
        }
    }

    // 发商品券
    private function allocateSpecialCoupon($title, $uid, $coupon_number)
    {
        $http = new HttpHandlerCurl('UTF-8', 1);
        $url = GATEWAY_URL_ROOT . 'trade/orders';
        $query = array(
            'memberId' => $uid,
            'totalPrice' => 0,
            'realPay' => 0,
            'productInfos' => json_encode(array(
                array(
                    'count' => $this->coupon_info['unit'],
                    'productInfo' => array(
                        'price' => 0.0,
                        'title' => $title,
                    ),
                    'productId' => $coupon_number
                )
            )),
            'tradeCode' => '7010',
        );
        $result = json_decode($http->post($url, $query), true);
        // 达到购买限制
        if (mb_strpos($result['message'], '上限')) {
            return LIMIT;
        }
        if (empty($result['status']) && !empty($result['orderNo'])) {
            return OK;
        }
        return FAIL;
    }

    // 通过券接口，给用户发券
    private function allocateCoupon($uid, $coupon_number)
    {
        if (empty($this->coupon_info['unit'])) {
            return FAIL;
        }

        if (empty($this->coupon_info['type'])) {
            return FAIL;
        }

        if ($this->coupon_info['type'] == 1) {
            return $this->allocateSpecialCoupon($this->coupon_info['title'], $uid, $coupon_number);
        }

        $http = new HttpHandlerCurl('UTF-8', 1);
        $url = GATEWAY_URL_ROOT . 'coupon/v1/trade/allocate';
        $query = array(
            'productNo' => $coupon_number,
            'memberId' => $uid,
            'count' => $this->coupon_info['unit'],
            'isSendSms' => 0 // 接口现在默认发短信，如果传1反而不发短信
        );
        $result = json_decode($http->post($url, $query), true);
        Log::Info("AllocateCoupon. Query:" . json_encode($query) . ". Result:" . json_encode($result));
        if ($result['status'] == 500 && $result['errorCode'] == 2108) {
            return OUT_OF_STOCK;
        }
        // 达到购买限制
        if($result['errorCode'] == 2901 || $result['errorCode'] == 2902){
            return LIMIT;
        }
        if ($result['status'] != 200) {
            return FAIL;
        }
        return OK;
    }

    // 数据库记录更新
    private function record($activity_id, $uid, $coupon_number, $source, $plaza_id)
    {
        if (empty($this->coupon_info['unit'])) {
            throw new Exception('无法获取发放单位', FAIL);
        }

        $values = '';
        $value = sprintf(
            '(%d, %s, %s, %d, %d, \'%s\', %d)',
            $activity_id,
            ToolUtil::escapeSQL($uid),
            ToolUtil::escapeSQL($coupon_number),
            time(),
            date('Ymd'),
            $source,
            $plaza_id
        );
        for ($i = 0; $i < $this->coupon_info['unit']; $i++) {
            $values .= "{$value},";
        }
        $log_user_sql = substr(
            "INSERT INTO `" . self::TABLE_NAME .
            "` (`activity_id`, `uid`, `coupon_number`, `get_time`, `get_day`, `source`,`plaza_id`) VALUES {$values}",
            0,
            -1
        );

        $result = MysqlClient::ExecuteUpdate(self::DB_NAME, $log_user_sql);
        if (empty($result)) {
            throw new Exception('数据库执行失败', FAIL);
        }
        return OK;
    }

    // 验证验证码是否正确
    private function reCaptcha($captcha_id, $captcha)
    {
        $http = new HttpHandlerCurl();
        $url = ZZQ_BASE . '/verify_captcha';
        $query = array(
            'id' => $captcha_id,
            'captcha' => $captcha
        );
        $result = json_decode($http->post($url, $query, array(), array(CURLOPT_TIMEOUT => 1)), true);
        return $result['status'] == 200;
    }
}
