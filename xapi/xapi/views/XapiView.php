<?php
require_once PHP_ROOT . '/libs/mvc/JsonpView.php';
require_once PHP_ROOT . '/libs/util/Cookie.php';

class XapiView extends JsonpView {
  const SESSION_MAX_TIME = 31536000;
  public function Display() {
    $session_id = session_id();
    if (0 == SESSION_EXPIRE_TIME)
      Cookie::Set(SESSIONID, $session_id, time() + self::SESSION_MAX_TIME);
    else
      Cookie::Set(SESSIONID, $session_id, time() + SESSION_EXPIRE_TIME);
    if (property_exists($this->data_, "uid")) {
      if ($this->data_->uid) {
        if (0 == SESSION_EXPIRE_TIME)
          Cookie::Set(UID, $this->data_->uid, time() + self::SESSION_MAX_TIME);
        else
          Cookie::Set(UID, $this->data_->uid, time() + SESSION_EXPIRE_TIME);
      }
      unset($this->data_->uid);
    }

    parent::Display();
  }
}

