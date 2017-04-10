<?php
class BookModel extends BaseModel {
  public $table_name  = 'books';
  public $model_name  = 'Book';
  public $model_class_name  = 'BookModel';

  //  Relation
  public $belongthTo = array(
    'User' => array(
      'JOIN_COND' => 'INNER', 
      'conditions' => array('User.id' => 'Book.user_id'), 
    ), 
  );

  public $has = null;

  public $columns = array(
    'id' => array('type' => 'int', 'length' => 8, 'null' => false, 'key' => 'PRI', 'default' => null, ), 
    'user_id' => array('type' => 'int', 'length' => 8, 'null' => false, 'key' => 'PRI', 'default' => null, ), 
    'name' => array('type' => 'varchar', 'length' => 32, 'null' => false, 'key' => '', 'default' => null, ), 
    'created_at' => array('type' => 'datetime', 'length' => 19, 'null' => false, 'key' => 'PRI', 'default' => null, ), 
    'modified_at' => array('type' => 'datetime', 'length' => 19, 'null' => false, 'key' => 'PRI', 'default' => null, ), 
  );

  public function __construct(&$dbh) {
    parent::__construct($dbh);
  }
}
