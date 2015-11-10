<?php
require_once(PHP_ROOT . 'libs/util/MysqlClient.php');

//DB初始化
foreach ($g_mysql_masters as $master)
  MysqlClient::AddMaster($master[0], $master[1], $master[2], $master[3], $master[4], $master[5]);
foreach ($g_mysql_slaves as $slave)
  MysqlClient::AddSlave($slave[0], $slave[1], $slave[2], $slave[3], $slave[4], $slave[5]);
