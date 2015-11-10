<?php
/**
 * beacon营销_门店beacon信息控制器
 * @date 2015-08-14
 * @author llh
 */
require_once(WEB_ROOT . 'controllers/extra/WebapiController.php');
require_once(WEB_ROOT . 'views/XapiView.php');
require_once(WEB_ROOT . 'models/v1/ActivityBeaconStoreModel.php');

class ActivityBeaconStoreController extends WebapiController {
  public function __construct() {}


  public function Run() {
    $model = new ActivityBeaconStoreModel();
    $result = $model->DoSafeModel();
    if (empty($result->data)){
        $result->data = (object)array();
    }
    $view = new XapiView();
    $view->SetData($result);
    $view->Display();
  }
}
