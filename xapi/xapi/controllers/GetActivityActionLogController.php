<?php
/*
 * creator: hexuan
 * */
require_once(WEB_ROOT . 'controllers/extra/ApiController.php');
require_once(WEB_ROOT . 'models/ActivityModel.php');
class GetActivityActionLogController extends ApiController
{
  protected function GetResponse()
  {
    $model = new ActivityModel();
    return $model->getActionLog();
  }
}

