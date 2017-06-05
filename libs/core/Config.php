<?php
class Config {
  public static function get($config, $key = null) {
    require_once CONFIG_PATH.getenv('ENVIRONMENT')."/".$config.".php";
    $vars = get_defined_vars();
    if ($key) $vars = $vars[$key];
    return $vars;
  }
}