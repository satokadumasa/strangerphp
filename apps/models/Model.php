<?php
class Model extends BaseModel {
  public $table_name  = '';
  public $model_name  = '';
  public $model_class_name  = 'Model';

  //  Relation
  public $belongthTo = null;
  public $has = null;
  public $has_many_and_belongs_to = null;

  public $columns = array(
    'id' => array('type' => 'int', 'length' => 8, 'null' => false, 'key' => 'PRI', 'default' => null, ), 
  name varchar(255) NOT NULL,
  address varchar(255) NOT NULL,
    'created_at' => array('type' => 'datetime', 'length' => 19, 'null' => false, 'key' => 'PRI', 'default' => null, ), 
    'modified_at' => array('type' => 'datetime', 'length' => 19, 'null' => false, 'key' => 'PRI', 'default' => null, ), 
  );

  public function __construct(&$dbh) {
    parent::__construct($dbh);
  }
}
