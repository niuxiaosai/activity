<?php
/*
* creator: zhangyongwei1
* desc :周周抢2期活动相关控制器
* */
require_once(WEB_ROOT . 'controllers/extra/WebapiController.php');
require_once(WEB_ROOT . 'views/XapiView.php');
require_once(WEB_ROOT . 'models/v2/ActivityModel.php');
class ActivityController extends WebapiController
{

	public function __construct() {
	}

	public function Run() {
		$model = new ActivityModel();
		$result = $model->DoSafeModel();
		$view = new XapiView();
		$view->SetData($result);
		$view->Display();
	}
}

