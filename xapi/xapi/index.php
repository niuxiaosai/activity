<?php
error_reporting(E_ALL ^ E_DEPRECATED ^ E_NOTICE ^ E_WARNING);
date_default_timezone_set('Asia/Shanghai');
header('Content-type: text/html;charset=UTF-8');
// WEB_ROOT用于项目内部
define('WEB_ROOT', dirname(__FILE__) . '/');
// PHP_ROOT用于项目外部
define('PHP_ROOT', dirname(dirname(WEB_ROOT)) . "/");
// 飞凡根目录
define('FFAN_ROOT', dirname(WEB_ROOT) . "/");

require_once(FFAN_ROOT . 'utils/LibAutoLoader.php');
require_once(WEB_ROOT . 'Config.php');
require_once(WEB_ROOT . 'Global.php');
require_once(WEB_ROOT . 'common/ErrorMap.php');
require_once(WEB_ROOT . 'common/InitSession.php');
require_once(WEB_ROOT . 'common/InitMysql.php');
require_once(WEB_ROOT . 'common/InitRouter.php');

