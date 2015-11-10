<?php

final class ModelExceptionStatus {
  const PARAMETER_EXCEPTION = 10100;     // 参数异常
  const DB_EXCEPTION = 10200;            // 数据库异常
  const RPC_EXCEPTION = 10300;           // RPC异常
  const RPC_YAZUO_EXCEPTION = 10301;
  const RPC_STORE_EXCEPTION = 10302;
  const RPC_PRODUCT_EXCEPTION = 10303;
  const RPC_USERSERVER_EXCEPTION = 10304;
  const RPC_RECOMMEND_EXCEPTION = 10305;
  const RPC_TRADE_EXCEPTION = 10306;
  const RPC_PAY_EXCEPTION = 10307;
  const RPC_BIZCENTER_EXCEPTION = 10308;

  const DIRTYDATA_TRADE_EXCEPTION = 10506;  // 交易系统脏数据异常
  const DIRTYDATA_PAY_EXCEPTION = 10507;

  const UNKONOWN_EXCEPTION = 11000;     // 未知异常
  const SUFFERABLE_EXCEPTION = 0;      // 可容忍的异常（前端仍认为 Model 执行成功）
}

class ModelException extends Exception {

  private $_response;
  private $_debugData;

  /**
   * 构造异常信息
   * @param string $debugMessage 异常信息。DEBUG 模式返回具体错误信息到前端以便快速调试，RELEASE 模式返回统一报错文案，如需返回友好错误信息的 DoModel 函数中自己 catch
   * @param int $statusCode 默认1。非关键异常，可允许仍返回 code=0
   * @param Object $response 默认NULL，返回空 Response。非关键异常，可允许仍返回 Response，在此提供
   * @param array $debugData 对于频繁出问题的服务，可增加 debugData 参数便于调试和快速定位异常来源
   */
  public function __construct($debugMessage, $statusCode, $response = NULL, $debugData = array()) {
    parent::__construct($debugMessage, $statusCode);
    $this->_response = $response;
    $this->_debugData = $debugData;
  }

  public function getResponse() {
    return $this->_response;
  }

  public function getDebugData() {
    return $this->_debugData;
  }

}

?>
