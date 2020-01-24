<?php 
require_once 'vendor/spyc/Spyc.php';
$columns = [
    'version' => ['type' => 'int', 'length' => 11, 'null' => false, 'key' => 'PRI', 'default' => null, ], 
    'name' => ['type' => 'varchar', 'length' => 32, 'null' => false, 'key' => '', 'default' => null,] , 
    'created_at' => ['type' => 'datetime', 'length' => 19, 'null' => false, 'key' => 'PRI', 'default' => null,] , 
    'modified_at' => ['type' => 'datetime', 'length' => 19, 'null' => false, 'key' => 'PRI', 'default' => null,] , 
  ];

echo print_r(Spyc::YAMLLoad("db/schema/users.yaml")['users'], true)."\n";

