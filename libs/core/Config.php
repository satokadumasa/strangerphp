<?php
class Config {
  public static function get($config) {
    require CONFIG_PATH.ENVIRONMENTS."/".$config.".php";
    echo "PATH:".CONFIG_PATH.ENVIRONMENTS."/".$config.".php\n";
    $vars = get_defined_vars();
    return $vars;
  }
}
