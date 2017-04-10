<?php
class UserModel extends BaseModel {
  public $table_name  = 'users';
  public $model_name  = 'User';
  public $model_class_name  = 'UserModel';
  public $belongthTo  = null;
  public $has = array(
    'Book' => array(
      'foreign_key' => 'user_id', 
    ),
  );

  public $columns = array(
    'id' => array('type' => 'int', 'length' => 8, 'null' => false, 'key' => 'PRI', 'default' => null, ), 
    'name' => array('type' => 'varchar', 'length' => 32, 'null' => false, 'key' => '', 'default' => null, ), 
    'created_at' => array('type' => 'datetime', 'length' => 19, 'null' => false, 'key' => 'PRI', 'default' => null, ), 
    'modified_at' => array('type' => 'datetime', 'length' => 19, 'null' => false, 'key' => 'PRI', 'default' => null, ), 
  );

  public function __construct(&$dbh = null) {
    parent::__construct($dbh);
  }
}