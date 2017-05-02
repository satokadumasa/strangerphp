<?php
class UserDvdModel extends BaseModel {
  public $table_name  = 'user_dvds';
  public $model_name  = 'UserDvd';
  public $model_class_name  = 'UserDvdModel';
  public $has = null;
  public $belongthTo = array(
    'User' => array(
      'JOIN_COND' => 'INNER', 
      'conditions' => array('User.id' => 'UserDvd.user_id'), 
    ), 
    'Dvd' => array(
      'JOIN_COND' => 'INNER', 
      'conditions' => array('Dvd.id' => 'UserDvd.dvd_id'), 
    ), 
  );
  public $has_many_and_belongs_to = null;

  public $columns = array(
    'id' => array('type' => 'int', 'length' => 8, 'null' => false, 'key' => 'PRI', 'default' => null, ), 
    'user_id' => array('type' => 'int', 'length' => 8, 'null' => false, 'key' => 'PRI', 'default' => null, ), 
    'dvd_id' => array('type' => 'int', 'length' => 8, 'null' => false, 'key' => 'PRI', 'default' => null, ), 
    'created_at' => array('type' => 'datetime', 'length' => 19, 'null' => false, 'key' => 'PRI', 'default' => null, ), 
    'modified_at' => array('type' => 'datetime', 'length' => 19, 'null' => false, 'key' => 'PRI', 'default' => null, ), 
  );

  public function __construct(&$dbh = null) {
    parent::__construct($dbh);
  }
}