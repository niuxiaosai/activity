<?php
function Php_LibAutoLoad($classname)
{
  if (file_exists(FFAN_ROOT.'utils/' . $classname . '.php'))
  {
    require_once(FFAN_ROOT.'utils/' . $classname . '.php');
    return true;
  }
  return false;
}

spl_autoload_register('Php_LibAutoLoad');
