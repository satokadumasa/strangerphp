<?php
require_once dirname(__FILE__) . "/config/config.php";
require_once LIB_PATH . "/core/ClassLoader.php";
require_once VENDOR_PATH . "spyc/Spyc.php";

ini_set('error_reporting', 0);

putenv("ENVIRONMENT=development");

spl_autoload_register(array('ClassLoader', 'loadClass'));

echo "Init Stranger\n";
$stranger = new Stranger($argv);
$stranger->execute();
