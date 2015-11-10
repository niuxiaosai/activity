<?php
/*
 * creator: hexuan
 * */
require_once(WEB_ROOT . 'controllers/extra/ApiController.php');
require_once(WEB_ROOT . 'models/NotFoundModel.php');
class NotFoundController extends ApiController
{
  protected function GetResponse()
  {
    $model = new NotFoundModel();
    return $model->GetResponse();
  }
}

