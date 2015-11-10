<?php
/*
 * creator: hexuan
 * */

require_once(WEB_ROOT . 'views/extra/BaseView.php');
class JsonpView extends BaseView
{
  public function SetData($data)
  {
    $this->data = $data;
  }

  public function Display()
  {
    $callback_name = @$_GET['callback'];
    if(!empty($callback_name))
    {
      $result = json_encode($this->data);
      header("Content-Type: text/javascript; charset=UTF-8");
      $result = $callback_name . "(" . $result . ")";
    }
    else
    {
      $result = json_encode($this->data);
      header("Content-Type: application/json; charset=UTF-8");
    }
    echo $result;
  }
}
