<?php
/**
 * create: hexuan
 * 封装一些常用函数
 */
abstract class ToolUtil
{
	/**
	 * 错误编码
	 */
	public static $errCode = 0;
	/**
	 * 错误信息,无错误为''
	 */
	public static $errMsg = '';

	/**
	 * 客户端IP，一次请求内，这个值不会变
	 * @var string
	 */
	private static $clientIP = false;

	/**
	 * 清除错误信息,在每个函数的开始调用
	 */
	private static function clearError()
	{
		self::$errCode = 0;
		self::$errMsg = '';
	}

	private static function setERR($code, $msg = '')
	{
		self::$errCode = $code;
		self::$errMsg = $msg;
	}

	/**
	 * 判断是否存在火星文
	 * 标点符号也被算作是火星文的一部分，如果想提出这块，需要自己根据这个函数再写一个具体的函数
	 *
	 * @param string $str
	 * @return 存在火星文返回true的数，否则返回false
	 */
	public static function hasMarsWord($str)
	{
		$b = 0;
		$count = strlen($str);
		for ($i = 0; $i < $count; $i++) {
			$cCode = ord($str[$i]);
			if (($cCode > 47 && $cCode < 58)) { // 0-9
				$b = 0;
			} else if (($cCode > 64 && $cCode < 91)) { // A-Z
				$b = 0;
			} else if (($cCode > 96 && $cCode < 123)) { // a-z
				$b = 0;
			} else if ($cCode == 32) { // 空格
				$b = 0;
			} else {
				if ($b == 1) { // 第1、3...位
					if ($cCode < 64 || $cCode > 254) return false;
				} else { // 第0、2、4...位
					if ($cCode < 128 || $cCode > 254) return false;
				}

				$b = $b == 0 ? 1 : 0;
			}
		}

		return $b;
	}

	/**
	 * 验证qq号码的合法性
	 * 32 bit systems have a maximum signed integer range of -2147483648 to 2147483647
	 * 64 bit systems have a maximum signed integer range of -9223372036854775808
	 * to 9223372036854775807
	 * So we can use intval() safely to convert qq number
	 *
	 * @author        hexuan
	 *
	 * @param        string    $uin, 用户qq号码
	 *
	 * @return         bool 正确返回true, 错误返回false
	 */
	public static function checkUin($uin)
	{
		self::clearError();

		if (!self::checkInt($uin)) {
			self::$errCode = 10601;
			self::$errMsg = 'uin is not integer';

			return false;
		}

		if ($uin > 10000) {
			return true;
		}

		self::$errCode = 10601;
		self::$errMsg = 'uin is not integer';

		return false;
	}

	/**
	 * 判断是为中文
	 * GBK一个汉字包含两个字节，其中：第一个字节                 第二个字节
	 *                       GBK      |  x81-0xFE（128-254）    |   0x40-0xFE（64-254）
	 * @param string $str
	 * @return
	 */
	public static function IsChineseWord($str)
	{
		$str = trim($str);
		$count = strlen($str);
		for ($i = 0; $i < $count; $i++) {
			$cCode = ord($str[$i]);
			if ($i & 0x1) { // 第1、3...位
				if ($cCode < 64 || $cCode > 254) return false;
			} else { // 第0、2、4...位
				if ($cCode < 128 || $cCode > 254) return false;
			}
		}
		return true;
	}

	/**
	 * 判断地址的合法性，不做长度校验
	 * 符合条件：中文，数字，英文，一部分符号
	 * 符号包括：. , () - _
	 * @param string $str
	 * @return
	 */
	public static function checkAddress($str)
	{
		$str = trim($str);
		$b = 0;
		$count = strlen($str);
		for ($i = 0; $i < $count; $i++) {
			$cCode = ord($str[$i]);
			if (($cCode > 47 && $cCode < 58)) { // 0-9
				$b = 0;
			} else if (($cCode > 64 && $cCode < 91)) { // A-Z
				$b = 0;
			} else if (($cCode > 96 && $cCode < 123)) { // a-z
				$b = 0;
			} else if ($cCode == 32 || $cCode == 46 || $cCode == 40 || $cCode == 41 || $cCode == 45) { // 空格.()-
				$b = 0;
			} else {
				if ($b == 1) { // 第1、3...位
					if ($cCode < 64 || $cCode > 254) return false;
				} else { // 第0、2、4...位
					if ($cCode < 128 || $cCode > 254) return false;
				}

				$b = $b == 0 ? 1 : 0;
			}
		}

		return true;
	}

	public static function checkValidCharset($str)
	{
		$str = trim($str);
		$b = 0;

		$count = strlen($str);
		$pChar = 0;
		for ($i = 0; $i < $count; $i++) {
			$cCode = ord($str[$i]);
			if (($cCode >= 0 && $cCode < 8) || ($cCode >= 11 && $cCode <= 12) || ($cCode >= 14 && $cCode <= 31) || $cCode == 127) {
				return false;
			} else if ($cCode < 128) {
				$b = 0;
			} else {
				if ($b == 1) { // 第1、3...位
					if ($pChar > 0xF7) {
						if ($cCode < 0x40 || $cCode > 0xA0 || $cCode == 0x7F) return false;
					} else {
						if ($cCode < 0x40 || $cCode > 0xFE || $cCode == 0x7F) return false;
					}
				} else { // 第0、2、4...位
					if ($cCode < 0x81 || $cCode > 0xFE) return false;
					$pChar = $cCode;
				}

				$b = $b == 0 ? 1 : 0;
			}
		}

		return true;
	}

	/**
	 * 判断n是否是整数
	 *
	 * @author        hexuan
	 *
	 * @param        int        $n
	 *
	 * @return        bool    正确返回true, 错误返回false
	 */
	public static function checkInt($n)
	{
		self::clearError();

		if (!is_numeric($n)) {
			self::$errCode = 10624;
			self::$errMsg = 'param $n must be a number str. $n=' . $n;
			return false;
		}
		$n = trim($n);

		if (preg_match('/^[1-9][0-9]*$/', $n) === 1) {
			return true;
		}

		self::$errCode = 10600;
		self::$errMsg = 'data is not int';

		return false;
	}

	/**
	 * 判断日期的格式是否是正确的
	 * 日期的格式为:YYYY-MM-DD
	 *          or:YYYYMMDD
	 * @author        hexuan
	 *
	 * @param        string    $d, 时间字符串
	 *
	 * @return        bool    正确返回true, 错误返回false
	 *
	 * @ preg_match modified by clydechang
	 */
	public static function checkDate($d)
	{
		self::clearError();

		$d = trim($d);
		$regs = array();

		//if ( preg_match('/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})$/', $d, $regs) !== 1 )
		if (preg_match('/^([0-9]{4})[-]*([0-9]{1,2})[-]*([0-9]{1,2})$/', $d, $regs) !== 1) {
			self::$errCode = 10603;
			self::$errMsg = 'date is invalid';

			return false;
		}

		if (checkdate($regs[2], $regs[3], $regs[1])) {
			return true;
		}

		self::$errCode = 10603;
		self::$errMsg = 'date is invalid';

		return false;
	}

	/**
	 * 判断日期时间的格式是否是正确的
	 * 日期的格式为:YYYY-MM-DD HH:mm:ss
	 *
	 * @author        hexuan
	 *
	 * @param        string    $t, 时间字符串
	 *
	 * @return        bool    正确返回true, 错误返回false
	 */
	public static function checkDateTime($t)
	{
		self::clearError();

		$t = trim($t);
		$regs = array();

		if (preg_match('/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})$/', $t, $regs) !== 1) {
			self::$errCode = 10604;
			self::$errMsg = 'date time is invalid';

			return false;
		}

		if (checkdate($regs[2], $regs[3], $regs[1]) === false) {
			self::$errCode = 10604;
			self::$errMsg = 'date time is invalid';

			return false;
		}

		if ($regs[4] < 0 || $regs[4] > 23 || $regs[5] < 0 || $regs[5] > 59 || $regs[6] < 0 || $regs[6] > 59) {
			self::$errCode = 10604;
			self::$errMsg = 'date time is invalid';

			return false;
		}

		return true;
	}


	/**
	 * 根据出生月日计算星座
	 *
	 * @param        int        $birth_month, 出生月
	 * @param        int        $birth_day, 出生日
	 *
	 * @return        int        $astro, 星座对应的数字(1至12), 出错返回0
	 */
	public static function genAstro($birth_month, $birth_day)
	{
		$astro_start_array = array(
			1 => 19, 2 => 18, 3 => 20, 4 => 20, 5 => 20, 6 => 21,
			7 => 22, 8 => 22, 9 => 22, 10 => 22, 11 => 21, 12 => 21
		);

		$birth_month = intval($birth_month);
		if ($birth_month < 1 || $birth_month > 12) {
			return 0;
		}

		$birth_day = intval($birth_day);
		if ($birth_day < 1 || $birth_day > 31) {
			return 0;
		}

		if ($birth_day > $astro_start_array[$birth_month]) {
			$astro = $birth_month - 2;
		} else {
			$astro = $birth_month - 3;
		}

		$astro = $astro > 0 ? $astro : $astro + 12;

		return $astro;
	}

	/**
	 * 转换天数格式
	 *
	 * @param int $date:    代表天数的数字
	 * @return array($id=>$detail)
	 */
	public static function changeDate($date)
	{
		$date = intval($date);
		$j = 1;
		$weekArray = array('一', '二', '三', '四', '五', '六', '天');
		$days = array();
		for ($i = 0; $i < 7; $i = $i + 1) {
			if ($date & $j) {
				$days[$i + 1] = '星期' . $weekArray[$i];
			}
			$j = $j * 2;
		}
		return $days;
	}

	/**
	 * 验证用户的email信息, 正确返回true, 否则返回false
	 *
	 * @author        hexuan
	 * @modifier    peterdu
	 *
	 * @param        string    $email, 用户email
	 *
	 * @return        bool    正确返回true, 错误返回false
	 */
	public static function checkEmail($email)
	{
		self::clearError();

		$email = trim($email);
		$pt = '/^[a-z0-9_\-]+(\.[_a-z0-9\-]+)*@([_a-z0-9\-]+\.)+([a-z]{2}|aero|arpa|biz|com|coop|edu|gov|info|int|jobs|mil|museum|name|nato|net|org|pro|travel)$/i';

		if (preg_match($pt, $email) === 1) {
			return true;
		}

		self::$errCode = 10623;
		self::$errMsg = 'email is invalid';

		return false;
	}

	/**
	 * 验证邮编
	 *
	 * @author        hexuan
	 *
	 * @param        string    $zip, 验证邮编
	 *
	 * @return        bool    正确返回true, 错误返回false
	 */
	public static function checkZip($zip)
	{
		self::clearError();

		if (preg_match('/^[1-9]\d{5}$/', trim($zip))) {
			return true;
		}

		self::$errCode = 10605;
		self::$errMsg = 'zip code is invalid';

		return false;
	}

	/**
	 * 验证身份证号码
	 *
	 * @author        hexuan
	 *
	 * @param        string    $id, 身份证号码
	 *
	 * @return        bool    正确返回true, 错误返回false
	 */
	public static function checkIDCard($id)
	{
		self::clearError();

		if (preg_match('/^([0-9]{15}|[0-9]{17}[0-9a-z])$/i', trim($id))) {
			return true;
		}

		self::$errCode = 10606;
		self::$errMsg = 'IDCard number is invalid';

		return false;
	}

	/**
	 * 验证手机号码
	 *
	 * @author        hexuan
	 *
	 * @param        phone    $phone, 电话号码
	 * @param         string        $type, CHN中国大陆电话号码, INT国际电话号码
	 *
	 * @return        bool    正确返回true, 错误返回false
	 */
	public static function checkMobilePhone($phone, $type = 'CHN')
	{
		self::clearError();
		$ret = false;
		switch ($type) {
			case "CHN":
				$ret = (preg_match("/^((\(\d{3}\))|(\d{3}\-))?1\d{10}$/", trim($phone)) ? true : false);
				break;
			case "INT":
				$ret = (preg_match("/^((\(\d{3}\))|(\d{3}\-))?\d{6,20}$/", trim($phone)) ? true : false);
				break;
		}

		if ($ret === false) {
			self::$errCode = 10607;
			self::$errMsg = 'Mobile Phone is not illege.';
			return false;
		}

		return true;
	}

	/**
	 * 验证电话号码
	 *
	 * @author        hexuan
	 *
	 * @param        string        $phone, 电话号码
	 * @param         string        $type, CHN中国大陆电话号码, INT国际电话号码
	 *
	 * @return        bool        正确返回true, 错误返回false
	 */
	public static function checkPhone($phone, $type = 'CHN')
	{
		self::clearError();

		$ret = false;

		switch ($type) {
			case 'CHN':
				$ret = preg_match('/^([0-9]{3}|0[0-9]{3})-[1-9][0-9]{6,7}(-[0-9]{1,6})?$/', trim($phone)) ? true : false;
				break;
			case 'INT':
				$ret = preg_match('/^[0-9]{4}-([0-9]{3}|0[0-9]{3})-[0-9]{7,8}$/', trim($phone)) ? true : false;
		}

		if ($ret === false) {
			self::$errCode = 10608;
			self::$errMsg = 'Phone is invalid';

			return false;
		}

		return true;
	}

	/**
	 * 验证ip地址
	 *
	 * @author        hexuan
	 *
	 * @param        string    $ip, ip地址
	 *
	 * @return        bool    正确返回true, 否则返回false
	 */
	public static function checkIP($ip)
	{
		self::clearError();

		$ip = trim($ip);
		$pt = '/^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/';

		if (preg_match($pt, $ip) === 1) {
			return true;
		}

		self::$errCode = 10609;
		self::$errMsg = 'IP is invalid.';

		return false;
	}

	/**
	 * 验证url地址
	 *
	 * @author        hexuan
	 *
	 * @param        string    $url, url地址
	 * @return        bool    正确返回true, 否则返回false
	 */
	public static function checkURL($url)
	{
		self::clearError();

		$pt = '/^(https?:\/\/)?([a-z]([a-z0-9\-]*\.)+([a-z]{2}|aero|arpa|biz|com|coop|edu|gov|info|int|jobs|mil|museum|name|nato|net|org|pro|travel)|(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]))(\/[a-z0-9_\-\.~]+)*(\/([a-z0-9_\-\.]*)(\?[a-z0-9+_\-\.%=&amp;\/]*)?)?(#[a-z][a-z0-9_]*)?$/';

		if (preg_match($pt, $url) === 1) {
			return true;
		}

		self::$errCode = 10610;
		self::$errMsg = 'url is invalid';

		return false;
	}

	/**
	 * 重定向页面，防止意外缓存
	 * @param string $url 目标url
	 */
	public static function redirect($url)
	{
		self::noCacheHeader();
		header("location: " . $url);
		exit;
	}

	/**
	 * 强制无缓存
	 * @param string $url 目标url
	 */
	public static function noCacheHeader()
	{
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		header('Cache-Control: max-age=0');
		header('Cache-Control: no-store, no-cache, must-revalidate');
		header('Cache-Control: post-check=0, pre-check=0', false);
		header('Pragma: no-cache');
	}

	/**
	 * 判断字符串是否只包含英文字符
	 *
	 * @author        hexuan
	 *
	 * @param        string    $s, 字符串
	 *
	 * @return        bool
	 */
	public static function isAlpha($s)
	{
		self::clearError();

		if (preg_match('/^[A-Za-z]+$/', $s)) {
			return true;
		}

		self::$errCode = 10611;
		self::$errMsg = 'analphabetic character is detected';

		return false;
	}

	/**
	 * 获取服务器端指定索引IP(或列表)
	 *
	 * @param int $index    需要获取的IP索引 eth(index)
	 *
	 * @author    bennylin
	 * @return    mix    [ip1, ip2, ..]/ip
	 */
	public static function getLocalIp($index = -1)
	{
		static $local_ip_list;

		if (!$local_ip_list) {
			//read from file cache
			$local_ip_list = @file_get_contents('/tmp/local_ip_list.txt');
			if ($local_ip_list) {
				$local_ip_list = explode("\n", $local_ip_list);
			} else {
				//get by exec ip cmd
				$out = array();
				$stats = array();
				exec('ip addr', $out, $stats);
				//var_dump($out, $stats);

				$local_ip_list = array();
				foreach ($out as $info) {
					//inet 192.168.2.35/24 brd 192.168.2.255 scope global eth0
					$info = trim($info);
					$m = null;
					if (strpos($info, 'inet') === 0 && preg_match('/inet (\d+\.\d+\.\d+\.\d+).*eth(\d+)/i', $info, $m)) {
						$local_ip_list[] = trim($m[1]);
					}
				}

				//cache to file
				file_put_contents('/tmp/local_ip_list.txt', implode("\n", $local_ip_list));
			}
		}

		//get one ip
		if ($index >= 0) {
			return empty($local_ip_list[$index]) ? '0.0.0.0' : $local_ip_list[$index];
		}

		//return all
		return $local_ip_list;
	}


	/**
	 * 获取客户端IP
	 *
	 * @author        mangoguo
	 * @modifier    kulin
	 *
	 * @return        string    $ip, IP地址串
	 */
	public static function getClientIP($recalc = false)
	{
		self::clearError();

		if (!$recalc && self::$clientIP !== false) {
			return self::$clientIP;
		}

		if (isset($_SERVER['HTTP_QVIA'])) {
			$ip = self::qvia2ip($_SERVER['HTTP_QVIA']);

			if ($ip) {
				return self::$clientIP = $ip;
			}
		}

		if (isset($_SERVER['HTTP_CLIENT_IP']) && !empty($_SERVER['HTTP_CLIENT_IP'])) {
			return self::$clientIP = self::checkIP($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : '0.0.0.0';
		}

		if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip = strtok($_SERVER['HTTP_X_FORWARDED_FOR'], ',');
			do {
				$tmpIp = explode('.', $ip);
				//-------------------
				// skip private ip ranges
				//-------------------
				// 10.0.0.0 - 10.255.255.255
				// 172.16.0.0 - 172.31.255.255
				// 192.168.0.0 - 192.168.255.255
				// 127.0.0.1, 255.255.255.255, 0.0.0.0
				//-------------------
				if (is_array($tmpIp) && count($tmpIp) == 4) {
					if (($tmpIp[0] != 10) && ($tmpIp[0] != 172) && ($tmpIp[0] != 192) && ($tmpIp[0] != 127) && ($tmpIp[0] != 255) && ($tmpIp[0] != 0)) {
						return self::$clientIP = $ip;
					}
					if (($tmpIp[0] == 172) && ($tmpIp[1] < 16 || $tmpIp[1] > 31)) {
						return self::$clientIP = $ip;
					}
					if (($tmpIp[0] == 192) && ($tmpIp[1] != 168)) {
						return self::$clientIP = $ip;
					}
					if (($tmpIp[0] == 127) && ($ip != '127.0.0.1')) {
						return self::$clientIP = $ip;
					}
					if ($tmpIp[0] == 255 && ($ip != '255.255.255.255')) {
						return self::$clientIP = $ip;
					}
					if ($tmpIp[0] == 0 && ($ip != '0.0.0.0')) {
						return self::$clientIP = $ip;
					}
				}
			} while ($ip = strtok(','));
		}

		if (isset($_SERVER['HTTP_PROXY_USER']) && !empty($_SERVER['HTTP_PROXY_USER'])) {
			return self::$clientIP = self::checkIP($_SERVER['HTTP_PROXY_USER']) ? $_SERVER['HTTP_PROXY_USER'] : '0.0.0.0';
		}

		if (isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR'])) {
			return self::$clientIP = self::checkIP($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
		} else {
			return self::$clientIP = '0.0.0.0';
		}
	}

	/**
	 * 获取网通代理或教育网代理带过来的客户端IP
	 *
	 * @author        kulin
	 *
	 * @return        string/flase    IP串或false
	 */
	public static function qvia2ip($qvia)
	{
		self::clearError();

		if (strlen($qvia) != 40) {
			return false;
		}

		$ips = array(hexdec(substr($qvia, 0, 2)), hexdec(substr($qvia, 2, 2)), hexdec(substr($qvia, 4, 2)), hexdec(substr($qvia, 6, 2)));
		$ipbin = pack('CCCC', $ips[0], $ips[1], $ips[2], $ips[3]);
		$m = md5('QV^10#Prefix' . $ipbin . 'QV10$Suffix%');

		if ($m == substr($qvia, 8)) {
			return implode('.', $ips);
		} else {
			return false;
		}
	}

	public static function gbJsonDecode($data)
	{
		$data = str_replace("\r\n", "", $data);
		$data = str_replace("\t", "", $data);
		$data = mb_convert_encoding($data, 'UTF-8', 'GBK');
		$data = json_decode($data, true);
		return empty($data) ? "" : self::_gbJsonDecode($data);
	}

	private static function _gbJsonDecode($data)
	{
		if (is_array($data)) {
			$res = array();

			foreach ($data as $key => $value)
				$res[$key] = self::_gbJsonDecode($value);
			return $res;
		} else
			return mb_convert_encoding($data, 'GBK', 'UTF-8');
	}

	/**
	 * 自定义输出 php 数据成 json 的函数(支持 gb2312, utf-8 请用 php 内置 json_encode)
	 * 注：原有的方法处理如“蔡?”的中文会有问题
	 * @author        hexuan
	 *
	 * @param        mix        $data        需要转换的数组
	 */
	public static function gbJsonEncode($data)
	{
		self::clearError();

		if (is_object($data)) {
			$data = get_object_vars($data);
		}

		if (is_array($data)) {
			$data = self::_gbkToUtf8($data);
			$data = json_encode($data);
		} else if (is_string($data)) {
			$data = json_encode(iconv("GBK", "UTF-8", $data));
		} else {
			return json_encode($data);
		}

		return preg_replace_callback('/\\\\u([0-9a-f]{4})/i',
			create_function(
				'$matches',
				'return iconv("UCS-2BE", "GBK//IGNORE", pack("H*", $matches[1]));'
			),
			$data);
	}

	/**
	 * 自定义输出 php 数据成 urlencode数组 的函数(避免出现问题)
	 * 注：原有的方法处理如“蔡?”的中文会有问题
	 * @author        steptian
	 *
	 * @param        mix        $data        需要转换的数组
	 */
	public static function urlencodeArray($data)
	{
		if(is_array($data)){
			foreach($data as $key=>$val){
				$data[$key] = self::urlencodeArray($val);
			}
			return $data;
		}
		else{
			return urlencode($data);
		}
	}


	private static function _gbkToUtf8($data)
	{
		if (is_object($data)) {
			$data = get_object_vars($data);
		}

		if (is_array($data)) {
			$res = array();

			foreach ($data as $key => $val) {
				$key = iconv('GBK', 'UTF-8', $key);
				$res[$key] = self::_gbkToUtf8($val);
			}

			return $res;
		} else if (is_string($data)) {
			return iconv('GBK', 'UTF-8', $data);
		} else {
			return $data;
		}
	}

	/**
	 * utf-8 截取函数
	 *
	 * @param        string        $str, 源字符串
	 * @param        int            $len, 截取的视觉长度: 英文算半个, 中文算一个; 截取后结尾为半个中文时直接去掉
	 *
	 * @return        string        $tmp_str, 截取后的字符串
	 */
	public static function utfSubstr($str, $len)
	{
		self::clearError();

		$len = intval($len);

		if ($len < 1) {
			return $str;
		}

		$o_len = mb_strlen($str, 'utf8');

		if ($o_len <= $len) {
			return $str;
		}

		$temp_len = 0;
		$tmp_str = '';

		for ($i = 0; $i < $o_len && $temp_len < $len; $i++) {
			$char = mb_substr($str, $i, 1, 'utf8');
			$temp_len += (ord($char[0]) > 127) ? 1 : 0.5;
			$tmp_str .= $char;
		}

		if ($temp_len > $len) {
			$tmp_str = rtrim($tmp_str, $char);
		}

		return $tmp_str;
	}

	/**
	 * xml数据 gb 编码转 utf8
	 *
	 * @author        bennylin
	 *
	 * @param        string        $xmldata
	 * @return        string        $xmldata
	 */
	public static function xmlGB2UTF($xmldata)
	{
		self::clearError();

		if (substr($xmldata, 0, 6) == '<?xml ') {
			$p = strpos($xmldata, "\n");
			if ($p === FALSE) {
				return $xmldata;
			}
			$xml_header = substr($xmldata, 0, $p);
			if (stripos($xml_header, 'gbk')) {
				$s = str_ireplace('gbk', 'utf-8', $xml_header);
			} else {
				$s = str_ireplace('gb2312', 'utf-8', $xml_header);
			}
			return iconv('gb18030', 'utf-8//IGNORE', $s . substr($xmldata, $p));
		}

		return $xmldata;
	}

	/**
	 * 过滤用户输入字符中的不合法字符
	 *
	 * 目前主要直接把一些特殊字符替换成空格，并去掉前后空格
	 *
	 * @param    string        $str    用户输入的字符
	 * @return    string        过滤后输出的字符
	 */
	public static function filterInput($str)
	{
		if (empty($str)) return '';
		// 需要替换的特殊字符
		$specialStr = array('\\', '\'', '"', '`', '&', '/', '<', '>');
		$str = str_replace($specialStr, '', $str);

		// 超过一定字符集范围的也需要替换成空格
		$str = trim($str);
		$asciiCode = '/[\x00-\x1f\x7f]/is';
		$str = preg_replace($asciiCode, '', $str);

		return $str;
	}

	/**
	 * 过滤不可见字符,主要是XML认为无效的字符
	 *
	 * @param    string        $str, 源字符串
	 *
	 * @return    string        $str, 过滤结果
	 */
	public static function filterUnvisibleChar($str)
	{
		if (!strlen($str)) {
			return '';
		}
		return preg_replace('/[\x00-\x08\x0b\x0c\x0e-\x1f\x7f]/', '', $str);
	}

	/**
	 * 判断字符串是否合法
	 *
	 * @param string $str        :输入的字符串
	 * @param int     $length     :字符串的最大允许长度
	 * @param Boolean $filter    :是否过滤特殊字符
	 * @param Boolean $dirty    :是检查该字符串是否包括脏字还是对脏字进行替换(false:替换，true:检查)
	 * @return Boolean|String false:不合法；其他：脏话过滤的结果
	 */
	public static function checkInput($str, $length, $filter = true, $dirty = false)
	{
		self::clearError();
		if (!is_numeric($length)) {
			self::$errCode = 10616;
			self::$errMsg = 'the length err';
			return false;
		}

		if (empty ($str) || !is_string($str) || strlen($str) > $length) {
			self::$errCode = 10617;
			self::$errMsg = 'the input string is empty or too long';
			return false;
		}

		if ($filter) {
			$str = self::filterInput($str);
		}
		if (empty($str)) {
			self::$errCode = 10620;
			self::$errMsg = 'the string is empty after filterInput';
			return false;
		}

		require_once PHPLIB_ROOT . 'lib/Dirty.php';

		if ($dirty) {
			$isDirty = Dirty::hasDirty($str);
			if ($isDirty) {
				self::$errCode = 10619;
				self::$errMsg = 'the string has dirty. dirty word is ' . $isDirty;
				return false;
			}
			return $str;
		}
		$str = Dirty::replaceDirty($str);
		if (empty ($str)) {
			self::$errCode = 10618;
			self::$errMsg = 'the string is empty after dirtyreplace';
			return false;
		}
		return $str;
	}

	/**
	 * 根据传入的时间戳计算出此时间戳所在的年月日等详细信息
	 * 格式如下:
	 * array( 'year'    => 2009,
	 *           'month'    => 1,
	 *           'day'        => 12,
	 *           'hour'    => 12,
	 *           'minute'    => 2,
	 *           'sec'     => 59,
	 *           'week'     => 1
	 * )
	 *
	 * @param int t 需要解析的时间
	 *
	 * @return array
	 */
	public static function getDetailDateTime($t)
	{
		$data = array();

		$data['year'] = date('Y', $t);
		$data['month'] = date('m', $t);
		$data['day'] = date('d', $t);
		$data['hour'] = date('H', $t);
		$data['minute'] = date('i', $t);
		$data['sec'] = date('s', $t);
		$data['week'] = date('N', $t);
		return $data;
	}


	/**
	 * 过滤UBB编码，会把UBB中的一些标签去掉，也会把一些是文本的内容去掉
	 * 如:em|video|flash|audio|vphoto|quote|ffg|url|marque|email
	 * 使用此函数的时候要注意，此函数只用于分析UBB的文本内容，不能用于UBB的
	 * 转换与反转换
	 *
	 * @param string $content
	 * @return string
	 */
	public static function filterUBB($content)
	{
		$tmp = htmlspecialchars($content);
		$tmp = stripslashes($tmp);

		$tmp = ereg_replace("\r\n", "", $tmp);
		$tmp = ereg_replace("<br/>", "", $tmp);
		$tmp = ereg_replace("<br>", "", $tmp);
		$tmp = ereg_replace("\r", "", $tmp);
		$tmp = preg_replace("/\\t/is", "", $tmp);
		$tmp = preg_replace("/\[ft([^\[\]]*?)\]\[\/ft\]/is", " ", $tmp);
		while (preg_match("/\[ft([^\[\]]*?)\](.*?)\[\/ft\]/i", $tmp)) {
			$tmp = preg_replace("/\[ft([^\[\]]*?)\](.*?)\[\/ft\]/is", "\\2", $tmp);
		}
		$tmp = preg_replace("/\[ft([^\[\]]*?)\](.*?)\[\/ft\]/is", "\\2", $tmp);
		$tmp = preg_replace("/\[ffg([^\[\]]*?)\](.*?)\[\/ffg\]/is", "\\2", $tmp);
		$tmp = preg_replace("/\[U\](.*?)\[\/U\]/is", "\\1", $tmp);
		$tmp = preg_replace("/\[U\]\[\/U\]/is", "", $tmp);
		$tmp = preg_replace("/\[M\](.*?)\[\/M\]/is", "\\1", $tmp);
		$tmp = preg_replace("/\[M\]\[\/M\]/is", "", $tmp);
		$tmp = preg_replace("/\[R\](.*?)\[\/R\]/is", "\\1", $tmp);
		$tmp = preg_replace("/\[R\]\[\/R\]/is", "", $tmp);
		$tmp = preg_replace("/\[B\](.*?)\[\/B\]/is", "\\1", $tmp);
		$tmp = preg_replace("/\[B\]\[\/B\]/is", "", $tmp);
		$tmp = preg_replace("/\[I\](.*?)\[\/I\]/is", "\\1", $tmp);
		$tmp = preg_replace("/\[I\]\[\/I\]/is", "", $tmp);
		$tmp = preg_replace("/\[em\](.*?)\[\/em\]/is", "\\1", $tmp);
		$tmp = preg_replace("/\[em\]\[\/em\]/is", "", $tmp);
		$tmp = preg_replace("/\[img([^\[\]]*?)\](.*?)\[\/img\]/is", "", $tmp);
		$tmp = preg_replace("/\[card([^\[\]]+?)\](.*?)\[\/card\]/is", "", $tmp);
		$tmp = preg_replace("/\[qqshow([^\[\]]+?)\](.*?)\[\/qqshow\]/is", "", $tmp);
		$tmp = preg_replace("/\[quote([^\[\]]+?)\](.*?)\[\/quote\]/is", "", $tmp);
		$tmp = preg_replace("/\[qqVideo([^\[\]\/]+?)\](.*?)\[\/qqVideo\]/is", "", $tmp);
		$tmp = preg_replace("/\[video([^\[\]]+?)\](.*?)\[\/video\]/is", "", $tmp);
		$tmp = preg_replace("/\[flash([^\[\]]+?)\](.*?)\[\/flash\]/is", "", $tmp);
		$tmp = preg_replace("/\[audio([^\[\]]+?)\](.*?)\[\/audio\]/is", "", $tmp);
		$tmp = preg_replace("/\[vphoto([^\[\]]+?)\](.*?)\[\/vphoto\]/is", "", $tmp);
		$tmp = preg_replace("/\[marque([^\[\]]+?)\](.*?)\[\/marque\]/is", "", $tmp);
		$tmp = preg_replace("/\[ppk_url\](http:\/\/.+?)\[\/ppk_url\]/is", "", $tmp);
		$tmp = preg_replace("/\[url\](.*?)\[\/url\]/is", "", $tmp);
		$tmp = preg_replace("/\[url=([^\[\]]*?)\](.*?)\[\/url\]/is", "\\2", $tmp);
		$tmp = preg_replace("/\[email\](.*?)\[\/email\]/is", "\\1", $tmp);
		$tmp = preg_replace("/\[email\]\[\/email\]/is", "", $tmp);
		$tmp = ereg_replace("\[hr\]", "", $tmp);
		return $tmp;
	}

	/**
	 * 生成校验串的通用hash函数
	 *
	 * @param    mix        $seed1, 生成校验串的种子变量(支持数组,对象等类型), 可接受多个seed, 最多支持10个
	 * @param    mix        $seed2, 生成校验串的种子变量(可选参数)
	 *
	 * @return    string        $hash
	 */
	public static function commHash($seed)
	{
		$enckey = '!@#123QWe';
		$numargs = func_num_args();
		$arg_list = func_get_args();
		$numargs = $numargs > 10 ? 10 : $numargs;
		$ostr = $enckey;
		for ($i = 0; $i < $numargs; $i++) {
			$temp = print_r($arg_list[$i], true);
			$ostr .= $temp;
		}
		$encstr = md5($ostr);
		return $encstr;
	}


	/**
	 * 转换比较容易产品xss的几个编码
	 * '&' (ampersand) becomes '&amp;'
	 * '"' (double quote) becomes '&quot;' when ENT_NOQUOTES is not set.
	 * ''' (single quote) becomes '&#039;' only when ENT_QUOTES is set.
	 * '<' (less than) becomes '&lt;'
	 * '>' (greater than) becomes '&gt;'
	 * @param string $str
	 * @return string
	 */
	public static function transXSSContent($str)
	{
		$str = htmlspecialchars($str, ENT_QUOTES);
		return $str;
	}


	/**
	 * 反转换比较容易产品xss的几个编码
	 * '&' (ampersand) becomes '&amp;'
	 * '"' (double quote) becomes '&quot;' when ENT_NOQUOTES is not set.
	 * ''' (single quote) becomes '&#039;' only when ENT_QUOTES is set.
	 * '<' (less than) becomes '&lt;'
	 * '>' (greater than) becomes '&gt;'
	 * @param string $str
	 * @return string
	 */
	public static function unTransXSSContent($str)
	{
		$str = htmlspecialchars_decode($str, ENT_QUOTES);
		return $str;
	}

	/**
	 * 二维数组排序
	 * @param Array $multi_array 待排序数组
	 * @param Array $sort_key  排序字段
	 * @return string $sort 排序类型：SORT_ASC / SORT_DESC
	 */
	public static function multi_array_sort($multi_array, $sort_key, $sort = SORT_ASC)
	{
		if (is_array($multi_array) and !empty($multi_array)) {
			foreach ($multi_array as $row_array) {
				if (is_array($row_array)) {
					$key_array[] = $row_array[$sort_key];
				} else {
					return -1;
				}
			}
			array_multisort($key_array, $sort, $multi_array);
		}

		return $multi_array;
	}

	public static function escapeTransStr($str_)
	{
		$search = array(":", "&", "\n", "\r");
		$replace = array("%8A", "%8B", "%8C", "%8D");

		return str_replace($search, $replace, $str_);
	}

	/**
	 * 通过城市名查询基本信息
	 * @param string $cityName
	 * @return mixed array 查询成功; false 失败
	 */
	public static function getLocInfoByCityName($cityName)
	{
		if (empty($cityName)) {
			return false;
		}
		global $_District, $_City, $_Province;

		$cityId = false;
		foreach ($_City as $idx => &$item) {
			if (false !== strpos($item['name'], $cityName)) {
				$cityId = $item['id'];
				break;
			}
		}
		return (false === $cityId) ? false : self::getLocInfo($cityId);
	}

	public static function getLocInfo($id)
	{
		global $_District, $_City, $_Province;

		if (!isset($_District[$id])) {
			if (!isset($_City[$id])) {
				if (!isset($_Province[$id])) {
					return false;
				}

				// 是一个省的名字
				return array(
					"province_id" => $id,
					"province_name" => $_Province[$id],
					"full_name" => $_Province[$id],
				);
			}

			$prov_id = $_City[$id]['province_id'];
			if (!isset($_Province[$prov_id])) {
				return false;
			}

			return array(
				"province_id" => $prov_id,
				"province_name" => $_Province[$prov_id],
				"city_id" => $id,
				"city_name" => $_City[$id]['name'],
				"full_name" => ($_Province[$prov_id] == $_City[$id]['name'] ? "" : $_Province[$prov_id]) . $_City[$id]['name'],
			);
		}

		$addr = $_District[$id];
		if (!isset($_City[$addr['city_id']])) { // 市级无效
			return false;
		}

		$addr['city_name'] = $_City[$addr['city_id']]['name'];

		if (!isset($_Province[$addr['province_id']])) { // 省级无效
			return false;
		}

		$addr['province_name'] = $_Province[$addr['province_id']];
		$addr['full_name'] = ($addr['province_name'] == $addr['city_name'] ? "" : $addr['province_name']) . $addr['city_name'] . $addr['name'];
		return $addr;
	}

	/**
	 * PHP 版本escape，CPS功能时引入
	 * @param string $str
	 */
	public static function escape($str)
	{
		preg_match_all("/[\x80-\xff].|[\x01-\x7f]+/", $str, $r);
		$ar = $r[0];
		foreach ($ar as $k => $v) {
			if (ord($v[0]) < 128) {
				$ar[$k] = rawurlencode($v);
			} else {
				$ar[$k] = "%u" . bin2hex(mb_convert_encoding($v, 'UCS-2', 'GBK'));
			}
		}
		return join('', $ar);
	}

	/**
	 *
	 * @param $str
	 * @param $start
	 * @param $len
	 */
	public static function msubstr($str, $start, $len)
	{
		$tmpstr = "";
		$strlen = $start + $len;
		for ($i = 0; $i < $strlen; $i++) {
			if (ord(substr($str, $i, 1)) > 0xa0) {
				$tmpstr .= substr($str, $i, 2);
				$i++;
			} else
				$tmpstr .= substr($str, $i, 1);
		}
		return $tmpstr;
	}

	//计算一个字符串的hash整数值，取值返回为[0-5614657)
	public static function  string2IntHash($str)
	{
		$hash = 0;
		$n = strlen($str);
		for ($i = 0; $i < $n; $i++) {
			$hash ^= (ord($str[$i]) << ($i & 0x0f));
		}
		return $hash % 5614657;
	}

	public static function isRequestFromMobile()
	{
		if (!isset($_SERVER['HTTP_USER_AGENT'])) return false;

		$userAgent = $_SERVER['HTTP_USER_AGENT'];
		$AGENTSCONFIG = Array("240x320", "acer", "acoon", "acs-", "abacho", "ahong", "airness", "alcatel", "amoi", "android", "anywhereyougo.com", "applewebkit/525", "applewebkit/532", "asus", "audio", "au-mic", "avantogo", "becker", "benq", "bilbo", "bird", "blackberry", "blazer", "bleu", "cdm-", "compal", "coolpad", "danger", "dbtel", "dopod", "elaine", "eric", "etouch", "fly ", "fly_", "fly-", "go.web", "goodaccess", "gradiente", "grundig", "haier", "hedy", "hitachi", "htc", "huawei", "hutchison", "inno", "ipad", "ipaq", "ipod", "jbrowser", "kddi", "kgt", "kwc", "lenovo", "lg ", "lg2", "lg3", "lg4", "lg5", "lg7", "lg8", "lg9", "lg-", "lge-", "lge9", "longcos", "maemo", "mercator", "meridian", "micromax", "midp", "mini", "mitsu", "mmm", "mmp", "mobi", "mot-", "moto", "nec-", "netfront", "newgen", "nexian", "nf-browser", "nintendo", "nitro", "nokia", "nook", "novarra", "obigo", "palm", "panasonic", "pantech", "philips", "phone", "pg-", "playstation", "pocket", "pt-", "qc-", "qtek", "rover", "sagem", "sama", "samu", "sanyo", "samsung", "sch-", "scooter", "sec-", "sendo", "sgh-", "sharp", "siemens", "sie-", "softbank", "sony", "spice", "sprint", "spv", "symbian", "talkabout", "tcl-", "teleca", "telit", "tianyu", "tim-", "toshiba", "tsm", "up.browser", "utec", "utstar", "verykool", "virgin", "vk-", "voda", "voxtel", "vx", "wap", "wellco", "wig browser", "wii", "windows ce", "wireless", "xda", "xde", "zte");
		foreach ($AGENTSCONFIG as $device) {
			if (stristr($userAgent, $device)) {
				return true;
			}
		}

		return false;
	}

	public static function isRequestFromAndroid()
	{
		if (!isset($_SERVER['HTTP_USER_AGENT'])) return false;

		return stristr($_SERVER['HTTP_USER_AGENT'], "android") ? true : false;
	}

	/*
	  * 获取文件后缀
	  *
	  * @Param	string	$fileName	the current file name
	  * @Return string
	  * @Created 19:29 2012/07/06
	  * @Author EdisonTsai
	  */
	public static function fileExt($fileName)
	{
		if (empty($fileName)) {
			return '';
		}
		return trim(strtolower(substr(strrchr($fileName, '.'), 1)));
	}

	/**
	 * 判断是否关联数组
	 * @param $obj 目标数组
	 */
	public static function is_assoc_array($obj)
	{
		if (is_array($obj)) {
			return array_keys($obj) !== range(0, count($obj) - 1);
		} else {
			throw new BaseException(101, 'Parameter 1 is not an array.');
		}
	}

	/**
	 * 从目标数组中提取指定值，只能做一维配置
	 * @param $obj 目标数组
	 * @param $filter 提起配置
	 * @example
	 * $params = ToolUtil::array_fetch($_GET, array(
	 *         'id' => array(
	 *             'name' => 'product_id' // 用于指定目标数组中的索引，若省略则与配置索引值相同
	 *             'default' => '' // 设置默认值，可省略，默认为''
	 *             'allowEmpty' => false // 指定该属性能够为空，可省略，默认为true，标识可以为空
	 *             'secureType' => 'int' // 设置安全限制，可以为'int'或'string'，若省略则不做安全限制
	 *         )
	 *         ...
	 * ));
	 */
	public static function array_fetch($obj, $filter)
	{
		if (!is_array($obj)) {
			throw new BaseException(101, 'Parameter 1 is not an array.');
		}
		if (!is_array($filter)) {
			throw new BaseException(102, 'Parameter 2 is not an array.');
		}
		$out = array();
		foreach ($filter as $name => $config) {
			$pname = (isset($config['name']) && !empty($config['name'])) ? $config['name'] : $name;
			if (isset($config['default']) && (!isset($obj[$pname]) || empty($obj[$pname])))
				$obj[$pname] = $config['default'];

			if (isset($config['allowEmpty']) && !$config['allowEmpty'] && (!isset($obj[$pname]) || empty($obj[$pname])))
				throw new BaseException(103, "Property $pname should not be empty.");

			if (isset($obj[$pname])) {
				if (isset($config['secureType'])) {
					if ($config['secureType'] == 'int') {
						$out[$name] = intval($obj[$pname]);
					} else if ($config['secureType'] == 'string') {
						$out[$name] = ToolUtil::transXSSContent($obj[$pname]);
					} else
						throw new BaseException(104, "Undefined secure type {$config['secureType']}.");
				} else
					$out[$name] = $obj[$pname];
			} else
				$out[$name] = '';
		}
		return $out;
	}

	/**
	 * 判断请求是否来自移动设备
	 */
	public static function is_mobile()
	{
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		$mobile_agents = Array("240x320", "acer", "acoon", "acs-", "abacho", "ahong", "airness", "alcatel", "amoi", "android", "anywhereyougo.com", "applewebkit/525", "applewebkit/532", "asus", "audio", "au-mic", "avantogo", "becker", "benq", "bilbo", "bird", "blackberry", "blazer", "bleu", "cdm-", "compal", "coolpad", "danger", "dbtel", "dopod", "elaine", "eric", "etouch", "fly ", "fly_", "fly-", "go.web", "goodaccess", "gradiente", "grundig", "haier", "hedy", "hitachi", "htc", "huawei", "hutchison", "inno", "ipad", "ipaq", "ipod", "jbrowser", "kddi", "kgt", "kwc", "lenovo", "lg ", "lg2", "lg3", "lg4", "lg5", "lg7", "lg8", "lg9", "lg-", "lge-", "lge9", "longcos", "maemo", "mercator", "meridian", "micromax", "midp", "mini", "mitsu", "mmm", "mmp", "mobi", "mot-", "moto", "nec-", "netfront", "newgen", "nexian", "nf-browser", "nintendo", "nitro", "nokia", "nook", "novarra", "obigo", "palm", "panasonic", "pantech", "philips", "phone", "pg-", "playstation", "pocket", "pt-", "qc-", "qtek", "rover", "sagem", "sama", "samu", "sanyo", "samsung", "sch-", "scooter", "sec-", "sendo", "sgh-", "sharp", "siemens", "sie-", "softbank", "sony", "spice", "sprint", "spv", "symbian", "tablet", "talkabout", "tcl-", "teleca", "telit", "tianyu", "tim-", "toshiba", "tsm", "up.browser", "utec", "utstar", "verykool", "virgin", "vk-", "voda", "voxtel", "vx", "wap", "wellco", "wig browser", "wii", "windows ce", "wireless", "xda", "xde", "zte");
		$is_mobile = false;
		foreach ($mobile_agents as $device) {
			if (stristr($user_agent, $device)) {
				$is_mobile = true;
				break;
			}
		}
		return $is_mobile;
	}

    /**
     * 如果没有数据库的prepare方法，可以使用这里的方法来阻止常见的mysql注入
     *
     * @param string $string
     * @return string
     */
    public static function escapeSQL($string)
    {
        $unsafe_chars = array(';', "\n", "\r", '\\', '\'', '"', '--', '/', '%', ' ');
        if (get_magic_quotes_gpc()) {
            return str_replace($unsafe_chars, '', trim($string));
        } else {
            return addslashes(str_replace($unsafe_chars, '', trim($string)));
        }
    }

}

