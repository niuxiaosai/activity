<?php
/*
 * creator: hexuan
 * */
require_once(PHP_ROOT . 'mvc/Response.php');
abstract class BaseController
{
  protected $view;

  public function Execute()
  {
    if ($this->BeforeRun() == false)
    {
      $this->view->Display();
      return ;
    }

    $this->Run();

    if ($this->AfterRun() == false)
    {
      $this->view->Display();
      return ;
    }

    $this->view->Display();
  }

  abstract protected function BeforeRun();
  abstract protected function Run();
  abstract protected function AfterRun();
}

