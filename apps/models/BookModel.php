<?php
class BookModel extends BaseModel {
  public $table_name  = 'books';
  public $model_name  = 'Book';
  public $model_class_name  = 'BookModel';

  //  Relation
  public $belongthTo = null;
  public $has = null;
  public $has_many_and_belongs_to = null;

  public $columns = array(
    'id' => array('type' => 'int', 'length' => 8, 'null' => false, 'key' => 'PRI', 'default' => null, ), 
    'name' => array('type' => 'string', 'length' => 255, 'null' => false, 'key' => '', 'default' => null, ), 
    'outline' => array('type' => 'text', 'length' => 255, 'null' => false, 'key' => '', 'default' => null, ), 
    'detail' => array('type' => 'text', 'length' => 255, 'null' => false, 'key' => '', 'default' => null, ), 
    'created_at' => array('type' => 'datetime', 'length' => 19, 'null' => false, 'key' => 'PRI', 'default' => null, ), 
    'modified_at' => array('type' => 'datetime', 'length' => 19, 'null' => false, 'key' => 'PRI', 'default' => null, ), 
  );

  public function __construct(&$dbh) {
    parent::__construct($dbh);
  }
}
