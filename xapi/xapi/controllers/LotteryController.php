<?php
/*
 * creator: hexuan
 * */
require_once(WEB_ROOT . 'controllers/extra/ApiController.php');
require_once(WEB_ROOT . 'models/ActivityWebModel.php');
class LotteryController extends ApiController
{
  protected function GetResponse()
  {
    $model = new ActivityWebModel();
    return $model->lottery();
  }
}

