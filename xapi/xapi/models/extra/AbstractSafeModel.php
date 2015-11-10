<?php

require_once(WEB_ROOT . 'models/extra/Response.php');
require_once(FFAN_ROOT . '/common/exception/ModelException.php');
require_once(PHP_ROOT . 'libs/util/Log.php');

abstract class AbstractSafeModel {

  /**
   * 安全执行 DoModel，Controller 层调用此方法
   * @return Response
   */
  public function DoSafeModel() {
    try {
      $response = $this->DoModel();
      return $response;
    } catch (Exception $e) {    // any Exception
      $message = "[EXCEPTION] Code:" . $e->getCode() . " Msg:" . $e->getMessage();
      $message .= (!is_a($e, 'ModelException') ? '' : (" Data:" . var_export($e->getDebugData(), true)));
      Log::Error($message, false);

      // DEBUG 模式
      if (defined('OUTPUT_EXCEPTION') && OUTPUT_EXCEPTION) {
        header("Content-type: text/plain");
        print_r("=================== DEBUG MODE =====================\n");
        print_r("[StatusCode] " . $e->getCode());
        print_r("\n[DebugMessage] " . $e->getMessage());
        if (is_a($e, 'ModelException')) {
          print_r("\n[DebugData]\n" . var_export($e->getDebugData(), true));
        }
        print_r("\n=================== DEBUG MODE =====================\n\n");
        throw $e;
      }

      // RELEASE 模式
      $response = new Response();
      if (is_a($e, 'ModelException') && $e->getResponse() != NULL) {
        $response = $e->getResponse();
      }

      $response->status = $e->getCode();
      $response->msg = '操作未成功，请稍候重试';  // $e->getMessage();  // TODO getMessage()信息对用户不可见，上线之前关闭
      return $response;
    }
  }

  /**
   * 非安全 DoModel，Model 层实现此方法，允许抛出异常，DoModelSafe() 方法中捕获，也可自行捕获
   * @return Response
   */
  abstract protected function DoModel();
}
