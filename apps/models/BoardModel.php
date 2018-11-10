<?php
class BoardModel extends BaseModel {
  public $table_name  = 'boards';
  public $model_name  = 'Board';
  public $model_class_name  = 'BoardModel';

  //  Relation
  public $belongthTo = null;
  public $has = null;
  public $has_many_and_belongs_to = null;

  public $columns = [
    'id' => array('type' => 'int', 'length' => 8, 'null' => false, 'key' => 'PRI', 'default' => null, ), 
    'title' => array('type' => 'string', 'length' => 128, 'null' => false, 'key' => '', 'default' => null, ), 
    'body' => array('type' => 'text', 'length' => 3000, 'null' => false, 'key' => '', 'default' => null, ), 
    'created_at' => array('type' => 'datetime', 'length' => 19, 'null' => false, 'key' => 'PRI', 'default' => null, ), 
    'modified_at' => array('type' => 'datetime', 'length' => 19, 'null' => false, 'key' => 'PRI', 'default' => null, ), 
  ];

  public function __construct(&$dbh) {
    parent::__construct($dbh);
  }
}
