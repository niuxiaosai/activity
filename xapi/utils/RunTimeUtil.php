<?php
/**
 *  时间差工具类
 *  @author xielei14
 * 
 **/
class RunTimeUtil 
{
  private $_startTime = 0;
  private $_stopTime = 0;
  private $_message = '';
  
  public function __construct($message = '')
  {
    $this->message = $message;
    $this->start();
  }

  public function __destruct()
  {
    $this->stop();
    $time = round(($this->_stopTime - $this->_startTime) * 1000, 2);
    if ($this->message != '')
      Log::Info($this->message . ', spend(' . $time.'ms)');
  }

  public function start()
  {
    $this->_startTime= Utilitys::GetMicroTime();
  }

  public function stop()
  {
    $this->_stopTime = Utilitys::GetMicroTime();
  }

  //返回时间差  ms
  public function spent()
  {
    $this->stop();
    return round(($this->_stopTime - $this->_startTime) * 1000, 2);
  }

}
