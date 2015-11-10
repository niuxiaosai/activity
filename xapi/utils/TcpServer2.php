<?php
/*
 * creator: hexuan
 * */

set_time_limit(0);
declare(ticks = 1);

abstract class TcpServer2
{
  const STOP = 'STOP';
  const RUNNING = 'RUNNING';

  protected $ip = false;
  protected $port = false;
  protected $black_list = false;
  protected $white_list = false;

  protected $server_handle = false;
  protected $back_log = 100;

  protected $process_num = 5;
  protected $cur_request_num = 0;
  protected $succ_request_num = 0;
  protected $succ_response_num = 0;
  protected $err_request_num = 0;
  protected $err_response_num = 0;

  protected $max_request_num = 100000;
  protected $max_length = 1024000;

  protected $client_ip = false;

  private $children = array();
  private $status = self::RUNNING;

  private $pid_file = false;

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

    $this->server_handle = stream_socket_server("tcp://$this->ip:$this->port", $err_no, $err_msg);
    if ($this->server_handle == false)
    {
      Log::Error("socket create fail.{$err_msg}");
      exit("socket create fail.{$err_msg}");
    }

    $pid = posix_getpid();
    $r = file_put_contents($this->pid_file, $pid);
    if ($r <= 0)
    {
      Log::Error("can not write pid file($this->pid_file)");
      exit("can not write pid file($this->pid_file)");
    }

    for ($i == 0; $i < $this->process_num; $i++)
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
          Log::Info("$pid restart");
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
    while ($this->status == self::RUNNING)
    {
      if ($this->cur_request_num > $this->max_request_num)
      {
        break;
      }
      $this->cur_request_num++;

      $socket = @stream_socket_accept($this->server_handle);
      if (!$socket)
      {
        continue;
      }

      $has_err = false;
      $err_msg = '';

      $this->client_ip = @stream_socket_get_name($socket, true);

      if ($has_err === false && !empty($this->white_list) && !in_array($ip, $this->white_list))
      {
        $has_err = true;
        $err_msg = "{$this->client_ip} is not allowed";
      }

      if ($has_err === false && !empty($this->black_list) && in_array($ip, $this->black_list))
      {
        $has_err = true;
        $err_msg = "{$this->client_ip} is not allowed";
      }

      @stream_set_timeout($socket, 3);
      $message = '';
      if ($has_err === false)
      {
        $message = NetUtil2::streamRead($socket, $this->max_length);
        if ($message === false)
        {
          $has_err = true;
          $err_msg = NetUtil2::$errMsg;
        }
      }

      if ($has_err === true)
      {
        $this->err_request_num++;
      }
      else
      {
        $this->succ_request_num++;
      }

      $response = '';
      try
      {
        $this->task($message, !$has_err, $err_msg, $response);
      }catch (Exception $e)
      {
        Log::Error($e->getMessage());
      }

      $ret = NetUtil2::streamWrite($socket, $response);
      if ($ret == false)
      {
        Log::Error(NetUtil2::$errMsg);
        $this->err_response_num++;
      }
      else
      {
        $this->succ_response_num++;
      }
      stream_socket_shutdown($socket, STREAM_SHUT_WR);
      unset($socket);
    }
    $this->taskAfter();
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
    //$opts = getopt('h:p:n:c:f');
    //
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
