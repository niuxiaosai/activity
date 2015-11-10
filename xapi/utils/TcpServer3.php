<?php
/*
 * creator: hexuan
 * */

set_time_limit(0);
declare(ticks = 1);

abstract class TcpServer3
{
  const STOP = 'STOP';
  const RUNNING = 'RUNNING';

  protected $ip = false;
  protected $port = false;
  protected $black_list = false;
  protected $white_list = false;

  protected $server_handle = false;
  protected $back_log = 100;

  protected $process_num = 1;
  protected $cur_request_num = 0;
  protected $succ_request_num = 0;
  protected $succ_response_num = 0;
  protected $err_request_num = 0;
  protected $err_response_num = 0;
  protected $cur_connections = 0;

  protected $max_request_num = 10000000;
  protected $max_length = 1024000;

  private $children = array();
  private $status = self::RUNNING;

  private $pid_file = false;
  private $ev_base = false;
  private $timer = false;

  public function __construct($max_length = 10240, $black_list = false, $white_list = false, $back_log = 50)
  {
    $this->max_length = $max_length;
    $this->back_log = $back_log;
    $this->black_list = $black_list;
    $this->white_list = $white_list;
  }

  public function __destruct()
  {}

  public function run()
  {
    $param = $this->analyseParam();
    $this->ip = $param['h'];
    $this->port = $param['p'];
    $this->pid_file = '/tmp/PHP_TCP_SERVER_'.$this->ip.'.'.$this->port;
    switch ($param['cmd'])
    {
      case 'start':
        $this->start($param);
        break;
      case 'stop':
        $this->stop($param);
        break;
      default:
        Log::Error('unrecognized parameter');
        exit('unrecognized parameter');
    }
  }

  private function start($param)
  {
    $this->process_num = $param['n'];
    if (file_exists($this->pid_file))
    {
      $pid = file_get_contents($this->pid_file);
      if (file_exists("/proc/$pid"))
      {
        Log::Error('server is running');
        exit('server is running');
      }
      unlink($this->pid_file);
    }
    $this->init();
    $this->_monitor();
  }

  private function stop($param)
  {
    $pid = file_get_contents($this->pid_file);
    if ($pid === false)
    {
      Log::Error("stop fail. can not find pid file {$this->pid_file}");
      exit("stop fail. can not find pid file {$this->pid_file}");
    }

    $ret = posix_kill($pid, SIGUSR2);

    if ($ret)
    {
      Log::Info('stop server succ');
      exit("succ: stop server succ.\n");
    }

    Log::Info('stop server succ');
    exit("error: stop server succ.\n");
  }

  private function init()
  {
    $pid = pcntl_fork();

    if ($pid == -1)
    {
      Log::Error("start server fail. can not create process");
      exit("start server fail. can not create process");
    }
    else if ($pid > 0)
    {
      exit(0);
    }

    posix_setsid();
    $pid = pcntl_fork();
    if ($pid == -1)
    {
      Log::Error("start server fail. can not create process");
      exit("start server fail. can not create process");
    }
    else if ($pid > 0)
    {
      exit(0);
    }

    pcntl_signal(SIGHUP,  SIG_IGN);
    pcntl_signal(SIGINT,  SIG_IGN);
    pcntl_signal(SIGTTIN, SIG_IGN);
    pcntl_signal(SIGTTOU, SIG_IGN);
    pcntl_signal(SIGQUIT, SIG_IGN);


    $r = pcntl_signal(SIGUSR1, array($this, 'signalHandle'));
    if ($r == false)
    {
      Log::Error("install SIGUSR1 fail");
      exit("error: install SIGUSR1 fail.\n");
    }

    $r = pcntl_signal(SIGUSR2, array($this, 'signalHandle'));
    if ($r == false)
    {
      Log::Error("install SIGUSR2 fail");
      exit("error: install SIGUSR2 fail.\n");
    }

    $pid = posix_getpid();
    if (file_put_contents($this->pid_file, $pid) <= 0)
    {
      Log::Error("can not write pid file($this->pid_file)");
      exit("can not write pid file($this->pid_file)");
    }

    $this->ev_base = new EventBase();
    $this->server_handle = new EventListener($this->ev_base, array($this, 'acceptCB'), $this->ev_base, EventListener::OPT_CLOSE_ON_FREE | EventListener::OPT_REUSEABLE, $this->back_log, "$this->ip:$this->port");

    //for ($i == 0; $i < $this->process_num; $i++)
    //for ($i == 0; $i < 1; $i++)
    {
      $pid = pcntl_fork();
      if ($pid == -1)
      {
        Log::Error("can not create fork");
        exit("can not create fork");
      }
      else if ($pid == 0)
      {
        pcntl_signal(SIGUSR1, SIG_IGN);
        $this->children = false;
        $this->back_log = false;
        $this->pid_file = false;
        $this->process_num = false;
        $this->service();
        exit(0);
      }
      $this->children[$pid] = time();
    }

  }

  private function _monitor()
  {
    while (true)
    {
      $pid = pcntl_wait($status, WNOHANG);
      if ($this->status === self::STOP)
      {
        if (count($this->children) <= 0)
        {
          unlink($this->pid_file);
          break;
        }

        if ($pid > 0 && array_key_exists($pid, $this->children))
        {
          unset($this->children[$pid]);
        }

        if (count($this->children) > 0)
        {
          $this->ping();
        }
      }
      else
      {
        if ($pid > 0 && array_key_exists($pid, $this->children))
        {
          unset($this->children[$pid]);
          $this->createProcess();
        }
        else
        {
          sleep(2);
          $this->monitor();
        }
      }
    }
  }

  private function service()
  {
    $this->taskBefore();
    $this->ev_base->reInit();
    $this->ev_base->dispatch();
    $this->taskAfter();
  }

  public function acceptCB($listener, $fd, $address, $ctx)
  {
    list($ip, $port) = $address;
    if (!empty($this->white_list) && !in_array($ip, $this->white_list))
    {
      Log::Info("{$this->client_ip} is not allowed");
      return;
    }

    if (!empty($this->black_list) && in_array($ip, $this->black_list))
    {
      Log::Info("{$this->client_ip} is not allowed");
      return;
    }

    $bev = new EventBufferEvent($this->ev_base, $fd, EventBufferEvent::OPT_CLOSE_ON_FREE);
    $bev->setCallbacks(array($this, 'readCB'), array($this, 'writeCB'), array($this, 'eventCB'), NULL);
    $bev->enable(Event::READ);
    $this->cur_connections++;
  }

  public function eventCB($bev, $events, $ctx)
  {
    if ($events & EventBufferEvent::ERROR)
    {
      Log::Info('Error from bufferevent');
    }

    if ($events & (EventBufferEvent::EOF | EventBufferEvent::ERROR))
    {
      $this->disconnect($bev);
    }
  }

  public function readCB($bev, $ctx)
  {
    $has_err = false;
    $err_msg = '';
    $message = '';
    $this->cur_request_num++;
    if ($this->cur_request_num > $this->max_request_num)
    {
      $this->ev_base->stop();
      return;
    }

    if (($message = NetUtil3::BufferRead($bev->input, $this->max_length)) == false)
    {
      $has_err = true;
      $err_msg = NetUtil3::$errMsg;
    }

    if ($has_err)
    {
      $this->err_request_num++;
    }else
    {
      $this->succ_request_num++;
    }

    $response = '';
    try
    {
      $ret = $this->task($message, !$has_err, $err_msg, $response);
      if ($ret == 0 )//0回复了以后关掉连接
      {
        $bev->disable(Event::READ);
      }else if ($ret < 0)//<0, 立即关闭
      {
        $this->err_response_num++;
        $this->disconnect($bev);
      }// > 0保持连接
      NetUtil3::BufferWrite($bev->output, $response);
    }catch (Exception $e)
    {
      $this->err_response_num++;
      Log::Error($e->getMessage());
    }
  }

  public function writeCB($bev, $ctx)
  {
    if (!($bev->getEnabled() & Event::READ))
    {
      $this->disconnect($bev);
    }
    $this->succ_response_num++;
  }

  private function disconnect(&$bev)
  {
    @$bev->close();
    @$bev->free();
    $this->cur_connections--;
  }

  public function taskBefore()
  {}

  public function taskAfter()
  {}

  private function analyseParam()
  {
    $tip = "usage: %server% [start | stop] -h ip -p port -n process_num [-c cfg_id] [-f]\n";

    $param['cmd'] = isset($_SERVER['argv'][1]) ? strtolower($_SERVER['argv'][1]) : 'start';

    if ( $param['cmd'] != 'stop' && $param['cmd'] != 'start' )
    {
      exit($tip);
    }

    $argv = $_SERVER['argv'];
    array_splice($argv, 0, 2);
    $opts = array();
    foreach ($argv as $var)
    {
      if (strpos($var, '-') === 0)
      {
        $key = substr($var, 1);
      }
      else
      {
        if (!empty($key))
        {
          $opts[$key] = $var;
        }
      }
    }
    if ( !isset($opts['h']) || !isset($opts['p']) || !isset($opts['n']) )
    {
      exit($tip);
    }

    foreach ($opts as $key => $val)
    {
      $val = trim($val, '=');
      switch ($key)
      {
      case 'h':
        if (!ToolUtil::checkIP($val))
        {
          Log::Error("ip {$val} is invalid");
          exit("ip {$val} is invalid");
        }
        $param['h'] = $val;
        break;
      case 'p':
        $val = intval($val);
        if ($val > 65535 || $val < 1025)
        {
          Logger:err("port[-p] {$val} must between 1025 and 65535");
          exit("port[-p] {$val} must between 1025 and 65535");
        }
        $param['p'] = $val;
        break;
      case 'n':
        $val = intval($val);
        if ($val > 500 || $val < 1)
        {
          Log::Error("process num[-n] {$val} must between 1 and 500");
          exit("process num[-n] {$val} must between 1 and 500");
        }
        $param['n'] = $val;
        break;
      case 'c':
        $val = intval($val);
        if ($val < 0)
        {
          Log::Error("config id[-c] {$val} can not be less than 0");
          exit("config id[-c] {$val} can not be less than 0");
        }
        $param['c'] = $val;
        break;
      case 'f':
        if ($val === false)
        {
          $param['method'] = '-f';
        }
        break;
      }
    }
    return $param;
  }

  private function ping()
  {
    if (file_exists($this->pid_file))
    {
      NetUtil::tcpCmd($this->ip, $this->port, '');
      usleep(100);
    }
  }

  private function createProcess()
  {
    $pid = pcntl_fork();
    if ($pid == -1)
    {
      Log::Error('cannot create process');
    }
    else if ($pid == 0)
    {
      pcntl_signal(SIGUSR1, SIG_IGN);
      $this->service();
      exit(0);
    }
    $this->children[$pid] = time();
  }

  public function signalHandle($sign_no)
  {
    switch ($sign_no)
    {
      case SIGUSR1:
        $this->status = self::STOP;
        if (empty($this->children)) 
          break;
        foreach ($this->children as $k => $v)
        {
          posix_kill($k, SIGKILL);
        }
        break;
      case SIGUSR2:
        $this->status = self::STOP;
        if (empty($this->children)) 
          break;
        foreach ($this->children as $k => $v)
        {
          posix_kill($k, SIGKILL);
        }
        break;
    }
  }

  abstract public function monitor();

  abstract public function task($message, $goodBag = true, $errMsg = '', &$response);

}
