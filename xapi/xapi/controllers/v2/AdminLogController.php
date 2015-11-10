<?php
/**
 * 旅游顾问相关的接口控制器
 * @since 2015-05-31
 */
require_once(WEB_ROOT . 'controllers/extra/WebapiController.php');
require_once(WEB_ROOT . 'views/XapiView.php');
require_once(WEB_ROOT . 'models/v2/AdminLogModel.php');



class AdminLogController extends WebapiController {
    public function __construct() {
    }

    public function Run() {
        $model = new AdminLogModel();
        $result = $model->DoSafeModel();
        $view = new XapiView();
        $view->SetData($result);
        $view->Display();
    }
}

