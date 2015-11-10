<?php

define('UID', 'UID');
define('SESSIONID', 'SESSIONID');
define('SESSION_EXPIRE_TIME', 0);
define('HOST_NAME', $_SERVER['SERVER_NAME']);

// 错误码定义
define('OK', 200); // 成功
define('CLOSED', 401); // 活动已结束
define('OUT_OF_STOCK', 402); // 库存不足，或达到日限量
define('NEED_CPATCHA', 403); // 需要验证码
define('NOT_START', 404); // 活动未开始
define('HAVE', 405); // 今天已经领过了
define('EXPIRE', 406); // 领券时发现券有效期已过
define('LIMIT', 407); // 达到限制
define('FAIL', 500); // 失败