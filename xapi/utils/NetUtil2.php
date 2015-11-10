<?php
/**
 * creator: hexuan
 */
abstract class NetUtil2
{
	/**
	 * 错误编码
	 */
	public static $errCode = 0;
	/**
	 * 错误信息,无错误为''
	 */
	public static $errMsg  = '';

	/**
	 * 清除错误信息,在每个函数的开始调用
	 */
	private static function clearError()
	{
		self::$errCode = 0;
		self::$errMsg	= '';
	}

  public static function streamRead(&$stream, $maxLength)
  {
    self::clearError();
    $len = @stream_socket_recvfrom($stream, 8);
    if ($len == false|| strlen($len) != 8)
    {
      self::$errCode = 10102;
      self::$errMsg = 'bad tcp bag';
      return false;
    }

    if (!is_numeric(trim($len)))
    {
      self::$errCode = 10104;
      self::$errMsg = 'bac tcp bag';
      return false;
    }
    $len = intval(trim($len));

    if ($len > $maxLength)
    {
      self::$errCode = 10105;
      self::$errMsg = 'tcp bag too big';
      return false;
    }

    $message = @stream_socket_recvfrom($stream, $len);
    if ($message == false)
    {
      self::$errCode = 10102;
      self::$errMsg = 'bad tcp bag';
      return false;
    }
    return $message;
  }

  public static function streamWrite(&$stream, $message)
  {
    self::clearError();
    $len     = strlen($message);
    $padStr  = str_pad($len, 8, ' ', STR_PAD_RIGHT);
    $message = $padStr.$message;
    $len = $len + 8;

    $ret = @stream_socket_sendto($stream, $message);
    if ($ret == false)
    {
      self::$errMsg = 'fail to send response';
      return false;
    }
    return true;
  }
}

//End of script

