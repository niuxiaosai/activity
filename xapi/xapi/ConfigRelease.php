<?php
/*
 * creator: hexuan
 * */
// 使用下面两行来禁用框架日志，同时可以选择自定义日志
$_ENV['DISABLE_LOG'] = true;
$GLOBALS['WD_LOGGER'] = $GLOBALS['HTTP_LOGGER_CLIENT'] = new Log();

// =======================gateway配置=======================
define('GATEWAY_URL_ROOT', 'http://api.ffan.com/');
define('GATEWAY_KEY', '6be55f3281c0f9bf7d3e313318d8381f');
define('GATEWAY_SECRET', '0c781c566cca5c94f508b95799f04379');

$g_memcached_servers = array(
    'default' => array(
        array('10.77.135.93', 11215),
        array('10.77.135.108', 11215),
    ),
    'session' => array(
        array('10.77.135.93', 11216),
        array('10.77.135.108', 11216),
    ),
);

// Mysql配置 (host, user, passwd, db_name, port)
$g_mysql_masters = array(
    array('m3329.mysql.wanhui.cn', 'ff_cloud', '234fd$@2', 'ff_cloud_marketing_platform', 3329, 0),
);

$g_mysql_slaves = array(
    array('m3329.mysql.wanhui.cn', 'ff_cloud', '234fd$@2', 'ff_cloud_marketing_platform', 3329, 0),
);

define('DIGITALRPC_SERVER_ADDR', 'http://api.ffan.com/ec/v1/');
define('UCENTER_ADDR', 'http://api.ffan.com/ucenter/v2/users/');
define('WDAPI_SEARCH_SERVICE', 'http://api.ffan.com/search/v1/list');
define('TRADE_ALLOCATE', 'http://api.ffan.com/coupon/v1/trade/allocate');
define('ZZQ_BASE', 'http://www.ffan.com/zzq');