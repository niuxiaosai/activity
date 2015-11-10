<?php

/*
 * 获取后台配置的某个活动的券列表
 *
 * @author 邓帅峰 <dengshuaifeng@wanda.cn>
 * @copyright 2015 Wanda Group
 */

require_once(WEB_ROOT . 'controllers/extra/WebapiController.php');
require_once(WEB_ROOT . 'views/XapiView.php');
require_once(WEB_ROOT . 'models/v2/ActivityCouponModel.php');

class CouponListController extends WebapiController
{

    public function run()
    {
        $model = new ActivityCouponModel();
        $result = $model->DoSafeModel();
        $view = new XapiView();
        $view->SetData($result);
        $view->Display();
    }

}
