<?php
/*
 * creator: hexuan
 * */
require_once(WEB_ROOT . 'controllers/extra/BaseController.php');
require_once(WEB_ROOT . 'views/ApiView.php');
abstract class ApiController extends BaseController
{
  protected function Run()
  {
    $response = $this->GetResponse();
    $this->view = $this->GetView();
    $this->view->SetData($response);
  }

  protected function BeforeRun()
  {
    return true;
  }

  protected function AfterRun()
  {
    return true;
  }

  protected function GetView()
  {
    return new ApiView();
  }

  abstract protected function GetResponse();
}

