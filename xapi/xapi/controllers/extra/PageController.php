<?php
/*
 * creator: hexuan
 * */
require_once(WEB_ROOT . 'controllers/extra/BaseController.php');
require_once(WEB_ROOT . 'views/PageView.php');
abstract class PageController extends BaseController
{
  protected $js_module = '';
  protected $title = '';
  protected $tpl = '';

  protected function Run()
  {
    $response = $this->GetResponse();
    
    $this->GetMeta($response);
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
    return new PageView($this->tpl);
  }

  protected function GetMeta(&$response)
  {
    $response->meta = array(
      'js_module' => $this->js_module,
      'title' => $this->title,
      );
  }

  abstract protected function GetResponse();
}

