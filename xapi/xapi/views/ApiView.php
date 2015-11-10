<?php
/*
 * creator: hexuan
 * */
require_once(WEB_ROOT . 'views/extra/BaseView.php');
class ApiView extends BaseView
{
  public function Display()
  {
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($this->data);
  }

  public function SetData($data)
  {
    $this->data = $data;
  }
}

