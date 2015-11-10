<?php
/**
 * creator: hexuan
 */
abstract class NetUtil3
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

  public static function BufferRead(&$event_buffer, $maxLength)
  {
    self::clearError();
    if ($event_buffer->length != 8)
    {
      self::$errCode = 10102;
      self::$errMsg = 'bad tcp bag';
      return false;
    }

    $len = $event_buffer->read(8);

    if (!is_numeric(trim($len)))
    {
      self::$errCode = 10104;
      self::$errMsg = 'bac tcp bag';
      return false;
    }
    $len = intval(trim($len));

    if ($len > $maxLength || $len != $event_buffer->length)
    {
      self::$errCode = 10105;
      self::$errMsg = 'tcp bag too big';
      return false;
    }

    $message = @$event_buffer->read($len);
    if ($message == false)
    {
      self::$errCode = 10102;
      self::$errMsg = 'bad tcp bag';
      return false;
    }
    return $message;
  }

  public static function BufferWrite(&$event_buffer, $message)
  {
    self::clearError();
    $len     = strlen($message);
    $padStr  = str_pad($len, 8, ' ', STR_PAD_RIGHT);
    $message = $padStr.$message;
    $len = $len + 8;

    $ret = @$event_buffer->add($message);
    if ($ret == false)
    {
      self::$errMsg = 'fail to send response';
      return false;
    }
    return true;
  }
}

//End of script

