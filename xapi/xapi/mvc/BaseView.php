<?php
/*
 * creator: hexuan
 * */
abstract class BaseView
{
  private $data;
  abstract public function Display();
  abstract public function SetData($data);
}
