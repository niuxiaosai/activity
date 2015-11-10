<?php
/*
 * creator: hexuan
 * */
require_once(WEB_ROOT . 'controllers/extra/ApiController.php');
require_once(WEB_ROOT . 'models/ActivityWebModel.php');
class GetActivityController extends ApiController
{
  protected function GetResponse()
  {
    $model = new ActivityWebModel();
    return $model->getActivity();
  }
}

