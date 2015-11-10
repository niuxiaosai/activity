<?php
/**
 * beacon营销_beacon活动信息控制器
 * @date 2015-08-14
 * @author llh
 */
require_once(WEB_ROOT . 'controllers/extra/WebapiController.php');
require_once(WEB_ROOT . 'views/XapiView.php');
require_once(WEB_ROOT . 'models/v1/ActivityBeaconActivityModel.php');

class ActivityBeaconActivityController extends WebapiController {
  public function __construct() {}


  public function Run() {
    $model = new ActivityBeaconActivityModel();
    $result = $model->DoSafeModel();
    $view = new XapiView();
    $view->SetData($result);
    $view->Display();
  }
}
