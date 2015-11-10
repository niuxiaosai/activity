<?php
/*
 * creator: hexuan
 * */
define('ENV', 'DEBUG');
// 使用下面两行来禁用框架日志，同时可以选择自定义日志
$_ENV['DISABLE_LOG'] = true;
$GLOBALS['WD_LOGGER'] = $GLOBALS['HTTP_LOGGER_CLIENT'] = new Log();
//测试merge
ini_set('display_errors',1);
// =======================gateway配置=======================
define('GATEWAY_URL_ROOT', 'http://api.sit.ffan.com/');
define('GATEWAY_KEY', 'cceff567d5c806a369a8fc1a4b27a7d3');
define('GATEWAY_SECRET', '8b9d7d98e304477acbd5956fddbddc7c');

$g_memcached_servers = array(
    'session' => array(
        array('10.77.144.95', 11215),
        array('10.77.144.96', 11215)
    ),
    'dao' => array(
        array('10.77.144.95', 11215),
        array('10.77.144.96', 11215)
    ),
    'cache' => array(
        array('10.77.144.95', 11215),
        array('10.77.144.96', 11215)
    ),
    'default' => array(
        array('10.77.144.95', 11215),
        array('10.77.144.96', 11215)
    ),
);

// Mysql配置 (host, user, passwd, db_name, port)
$g_mysql_masters = array(
    array('10.77.144.189', 'marketing', 'marketing', 'ff_cloud_marketing_platform', 10189, 0),
);

$g_mysql_slaves = array(
    array('10.77.144.189', 'marketing', 'marketing', 'ff_cloud_marketing_platform', 10189, 0),
);

define('DIGITALRPC_SERVER_ADDR', 'http://api.test.ffan.com/ec/v1/');
define('UCENTER_ADDR', 'http://api.sit.ffan.com/ucenter/v2/users/');
define('WDAPI_SEARCH_SERVICE', 'http://api.sit.ffan.com/search/v1/list');
define('TRADE_ALLOCATE','http://api.sit.ffan.com/coupon/v1/trade/allocate');
define('ZZQ_BASE', 'http://www.sit.ffan.com/zzq');