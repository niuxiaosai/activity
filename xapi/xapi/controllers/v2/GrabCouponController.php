<?php

/*
 * 用户抢券
 *
 * @author 邓帅峰 <dengshuaifeng@wanda.cn>
 * @copyright 2015 Wanda Group
 */

require_once(WEB_ROOT . 'controllers/extra/WebapiController.php');
require_once(WEB_ROOT . 'views/XapiView.php');
require_once(WEB_ROOT . 'models/v2/UserCouponModel.php');

class GrabCouponController extends WebapiController
{

    public function run()
    {
        $model = new UserCouponModel();
        $result = $model->DoSafeModel();
        $view = new XapiView();
        $view->SetData($result);
        $view->Display();
    }

}
