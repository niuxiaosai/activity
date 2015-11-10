<?php

require_once(WEB_ROOT . 'models/extra/AbstractSafeModel.php');
require_once(WEB_ROOT . 'models/extra/Response.php');
require_once (FFAN_ROOT . 'dao/v2/ActivityUserCouponDao.php');
require_once(PHP_ROOT . 'activity/utils/ToolUtil.php');
require_once(PHP_ROOT . 'libs/util/HttpRequestHelper.php');

class UserCouponModel extends AbstractSafeModel
{

    public function GetResponse()
    {
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
        $path_info = trim($_SERVER["PATH_INFO"], "/");
        $path_info_array = explode("/", $path_info);
        if ($path_info_array[1] == 'grabcoupon') {
            return $this->userGetCoupon();
        } else {
            $response = new Response();
            $response->status = "500";
            $response->msg = "请求方式或请求参数不正确";
            return $response;
        }
    }

    // 用户获得一张券
    public function userGetCoupon()
    {
        $response = new Response();
        $activity_id = intval(HttpRequestHelper::GetRequestParam('activityId'));
        $uid = ToolUtil::escapeSQL(HttpRequestHelper::GetRequestParam('userId'));
        $coupon_number = ToolUtil::escapeSQL(HttpRequestHelper::GetRequestParam('couponNumber'));
        $captcha_id = HttpRequestHelper::GetRequestParam('captchaId');
        $captcha = HttpRequestHelper::GetRequestParam('captcha');
        $source = ToolUtil::escapeSQL(HttpRequestHelper::GetRequestParam('source'));
        $plaza_id = intval(HttpRequestHelper::GetRequestParam('plazaId'));
        if (empty($activity_id) || empty($uid) || empty($coupon_number)) {
            $response->status = 400;
            return $response;
        }

        $status = ActivityUserCouponDao::userGetCoupon($activity_id, $uid, $coupon_number, $captcha_id, $captcha, $source, $plaza_id);
        $response->status = $status;
        return $response;
    }

}
