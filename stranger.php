<?php
require_once dirname(__FILE__) . "/config/config.php";
require_once LIB_PATH . "/core/ClassLoader.php";

ini_set('error_reporting', 0);

spl_autoload_register(array('ClassLoader', 'loadClass'));

$stranger = new Stranger($argv, $default_database);
$stranger->execute();
