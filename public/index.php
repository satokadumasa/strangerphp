<?php 
require_once dirname(dirname(__FILE__)) . "/config/config.php";
require_once LIB_PATH . "/ClassLoader.php";

spl_autoload_register(array('ClassLoader', 'loadClass'));

require_once CONFIG_PATH . "/routes.php";

$dispatcher = new Dispatcher($route, $default_database);
$dispatcher->dispatcheController();
