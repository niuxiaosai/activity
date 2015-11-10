<?php
/*
 * creator: hexuan
 * */
require_once(WEB_ROOT . 'controllers/extra/ApiController.php');
require_once(WEB_ROOT . 'models/CronJobModel.php');
class ResetStockController extends ApiController
{
  protected function GetResponse()
  {
    $model = new CronJobModel();
    return $model->resetStock();
  }
}