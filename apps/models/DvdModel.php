<?php
class DvdModel extends BaseModel {
  public $table_name  = 'dvds';
  public $model_name  = 'Dvd';
  public $model_class_name  = 'DvdModel';
  public $belongthTo  = null;
  public $has = null;

  public $has_many_and_belongs_to = array(
    'User' => array(
      'through' => 'UserDvd',
      'foreign_key' => 'dvd_id',
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