<?php
/**
 * 所有controller的父类
 */
require_once(PHP_ROOT . 'libs/mvc/ControllerBase.php');

abstract class WebapiController extends ControllerBase {

  public function Setup() {
    $GLOBALS['HTTP_LOGGER_CLIENT']->Init();
    $GLOBALS['HTTP_LOGGER_CLIENT']->SetLog(LogKeys::PRODUCT_TYPE, WanhuiLogProductType::WANHUI_APP);
    parent::Setup();
  }

  public function TearDown() {
    $GLOBALS['HTTP_LOGGER_CLIENT']->FlushLog();
  }

}
