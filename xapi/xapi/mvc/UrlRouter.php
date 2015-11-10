<?php
/*
 * creator: hexuan
 * */
abstract class UrlRouter
{
  protected $mapping_ = array();
  protected $controller_path_ = '';

  public function SetMapping($mapping)
  {
    $this->mapping_ = array_merge($this->mapping_, $mapping);
  }

  public function SetControllerPath($controller_path)
  {
    $this->controller_path_ = $controller_path;
  }

  public function Route()
  {
    $uri = isset($_SERVER['PATH_INFO']) ? str_replace('index.php', '', $_SERVER['PATH_INFO']) : $_SERVER['REQUEST_URI'];
    $controller_name = $this->Match($uri);
    if ($controller_name == false)
    {
      $controller_name = $this->GetNotFoundController();
    }
    require_once($this->controller_path_.'/'.$controller_name.'.php');
    $controller_name = basename($controller_name);
    $controller = new $controller_name();
    $controller->Execute();
  }

  protected function Match($uri)
  {
    $position = strpos($uri, '?');
    if ($position) $uri = substr($uri, 0, $position);
    while(true)
    {
      if (isset($this->mapping_[$uri]))
        return $this->mapping_[$uri];
      $position = strrpos($uri, '/');
      if ($position)
        $uri = substr($uri, 0, $position);
      else
        break;
    }
    return false;
  }

  protected abstract function GetNotFoundController();
}
