<?php
class Config {
  public static function get($config) {
    require_once CONFIG_PATH.ENVIRONMENTS."/".$config.".php";
    $vars = get_defined_vars();
    return $vars;
  }
}
