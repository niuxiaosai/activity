<?php
/*
 * creator: hexuan
 * */
/*
require_once(WEB_ROOT . 'mvc/UrlRouter.php');
class WebRouter extends UrlRouter
{
  protected function GetNotFoundController()
  {
    return 'NotFoundController';
  }

  public function Route()
  {
    $ts = Utilitys::GetMicroTime();
    parent::Route();
    $ts = Utilitys::GetMicroTime() - $ts;
    Log::Info("request(" . $_SERVER['REQUEST_URI'] . ") time:".round($ts*1000, 2)."ms");
  }
}
*/
require_once(PHP_ROOT . '/libs/mvc/UrlRouter.php');
$url_router = new UrlRouter();
$mapping = array(
    //bp后台
    '/activity/saveinfo' => 'CreateActivityController',//新建
    '/activity/updateinfo' => 'UpdateActivityController',//保存
    '/activity/list' => 'GetActivityListController',//列表
    '/activity/baseinfo' =>'GetActivityDetailController',//详情
    '/activity/updatestatus' => 'UpdateActivityStatusController',//更新状态
    '/activity/actionlog' => 'GetActivityActionLogController',//操作日志
    //web前端
    '/activity/getactivity' => 'GetActivityController',//获取活动状态
    '/activity/lottery'=>'LotteryController',//抽奖
    //test
    '/test/test'=>'TestController',
    //计划任务
    '/activity/resetstock'=>'ResetStockController',

    //周周抢二期活动
    '/v2/activitylist'=>'v2/ActivityController',
    '/v2/activity'=>'v2/ActivityController', 
    //活动营销券
    '/v2/activitycouponlist'=>'v2/ActivityCouponController',
    '/v2/activitycoupon'=>'v2/ActivityCouponController',
    '/v2/activitycoupons'=>'v2/ActivityCouponController',
    '/v2/adminloglist'=>'v2/AdminLogController',
    '/v2/adminlog'=>'v2/AdminLogController',
    '/v2/getplazalist'=>'v2/ActivityCouponController',
    '/v2/activitycouponnumber'=>'v2/ActivityCouponController',
    //抢券
    '/v2/couponlist' => 'v2/CouponListController', // 券列表
    '/v2/grabcoupon' => 'v2/GrabCouponController', // 抢券
    //beacon营销
    '/v1/beaconStore' => 'v1/ActivityBeaconStoreController',//
    '/v1/beaconActivity' => 'v1/ActivityBeaconActivityController',//
    
    );

//$router = new WebRouter();
//$router->SetMapping($mapping);
//$router->SetControllerPath('controllers');
//$router->Route();
$url_router->SetMapping($mapping);
$url_router->SetControllerPath(WEB_ROOT . 'controllers');
$url_router->Route();