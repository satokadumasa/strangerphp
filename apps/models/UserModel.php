<?php
class UserModel extends BaseModel {
  public $table_name  = 'users';
  public $model_name  = 'User';
  public $model_class_name  = 'UserModel';

  //  Relation
  public $belongthTo = null;
  public $has = null;
  public $has_many_and_belongs_to = null;

  public $columns = array(
    'id' => array('type' => 'int', 'length' => 8, 'null' => false, 'key' => 'PRI', 'default' => null), 
    'user_name' => array('type' => 'string', 'length' => 64, 'null' => false, 'key' => '', 'default' => null), 
    'password' => array('type' => 'string', 'length' => 64, 'null' => false, 'key' => '', 'default' => null ), 
    'role_id' => array('type' => 'int', 'length' => 8, 'null' => false, 'key' => '', 'default' => null ), 
    'delete_flag' => array('type' => 'tinyint', 'length' => 1, 'null' => false, 'key' => '', 'default' => null ), 
    'created_at' => array('type' => 'datetime', 'length' => 19, 'null' => false, 'key' => 'PRI', 'default' => null ), 
    'modified_at' => array('type' => 'datetime', 'length' => 19, 'null' => false, 'key' => 'PRI', 'default' => null ), 
  );

  public function __construct(&$dbh) {
    parent::__construct($dbh);
  }

  public function save($data) {
    $data['User']['password'] = md5($data['password'].'');
    parent::save($data);
  }

  public function checkAuth($user_name, $password) {
    return $this->where('User.user_name', '=', $user_name)
                ->where('User.password', '=', md5($password . SALT))
                ->find('first');
  }
}
