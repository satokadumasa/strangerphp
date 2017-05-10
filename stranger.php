<?php
require_once dirname(__FILE__) . "/config/config.php";
require_once LIB_PATH . "/ClassLoader.php";

spl_autoload_register(array('ClassLoader', 'loadClass'));

require_once CONFIG_PATH . "/routes.php";

$stranger = new Stranger($argv);
$stranger->execute();
