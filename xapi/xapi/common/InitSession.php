<?php
/*
 * creator: hexuan
 * */
/*
require_once(FFAN_ROOT . 'utils/Utilitys.php');
require_once(PHP_ROOT . 'libs/util/Cookie.php');
require_once(PHP_ROOT . 'libs/util/Session.php');
$session_id = Cookie::Get(SESSIONID);
if (empty($session_id))
{
  $session_id = Utilitys::GetSessionID();
  session_id($session_id);
  Cookie::Set(SESSIONID, $session_id);
}
Session::Init();
*/