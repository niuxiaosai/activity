<?php
/*
 * creator: hexuan
 * */
require_once(WEB_ROOT . 'views/extra/BaseView.php');
require_once(PHP_ROOT . 'thirdparty/Smarty/libs/Smarty.class.php');
require_once(PHP_ROOT . 'utils/Utilitys.php');
class PageView extends BaseView
{
  const LEFT_DELIMITER = '{=';
  const RIGHT_DELIMITER = '=}';

  const HEADER_TPL = 'header.tpl';
  const FOOTER_TPL = 'footer.tpl';
  const NAV_TPL = 'nav.tpl';

  private $smarty;
  private $data;
  private $tpl;

  public function __construct($tpl)
  {
    $this->tpl = $tpl;

    $this->smarty = new Smarty();
    $this->smarty->template_dir = WEB_ROOT . 'smarty/templates';
    $this->smarty->compile_dir = WEB_ROOT . 'smarty/templates_c';
    $this->smarty->config_dir = WEB_ROOT . 'smarty/configs';
    $this->smarty->cache_dir = WEB_ROOT . 'smarty/cache';
    $this->smarty->left_delimiter  = self::LEFT_DELIMITER;
    $this->smarty->right_delimiter = self::RIGHT_DELIMITER;

    if (defined('ENV') && ENV == 'DEBUG')
    {
      $this->smarty->debugging = true;
    }

    $this->RegisteSmartyFunction();
  }

  public function Display()
  {
    if ($this->data)
    {
      $this->smarty->assign('response', Utilitys::ObjectToArray($this->data));
    }
    $result = $this->smarty->fetch(self::HEADER_TPL);
    $result .= $this->smarty->fetch(self::NAV_TPL);
    $result .= $this->smarty->fetch($this->tpl);
    $result .= $this->smarty->fetch(self::FOOTER_TPL);
    echo $result;
  }

  public function SetData($data)
  {
    $this->data = $data;
  }

  private function RegisteSmartyFunction()
  {
    function Test($args)
    {
      extract($args);
    }

    function AddCss($args)
    {
      extract($args);
      return '<link type="text/css" rel="stylesheet" href="'. CSS_ROOT . $href . '" />';
    }

    $this->smarty->registerPlugin('function', 'AddCss', 'AddCss');
  }
}

