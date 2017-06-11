<?php
class UserModel extends BaseModel {
  public $table_name  = 'users';
  public $model_name  = 'User';
  public $model_class_name  = 'UserModel';

  //  Relation
  public $belongthTo = null;
  public $has = null;
  public $has_many_and_belongs_to = null;

  public $columns = [
    'id' => ['type' => 'int', 'length' => 8, 'null' => false, 'key' => 'PRI', 'default' => null, ], 
    'username' => array('type' => 'string', 'length' => 64, 'null' => false, 'key' => '', 'default' => null, ), 
    'password' => array('type' => 'string', 'length' => 64, 'null' => false, 'key' => '', 'default' => null, ), 
    'role_id' => array('type' => 'int', 'length' => 64, 'null' => false, 'key' => '', 'default' => null, ), 
    'email' => array('type' => 'string', 'length' => 128, 'null' => false, 'key' => '', 'default' => null, ), 
    'notified_at' => array('type' => 'datetime', 'length' => 64, 'null' => false, 'key' => '', 'default' => null, ), 
    'authentication_key' => array('type' => 'string', 'length' => 128, 'null' => true, 'key' => '', 'default' => null, ), 
    'created_at' => array('type' => 'datetime', 'length' => 19, 'null' => false, 'key' => 'PRI', 'default' => null, ), 
    'modified_at' => array('type' => 'datetime', 'length' => 19, 'null' => false, 'key' => 'PRI', 'default' => null, ), 
  ];

  public function __construct(&$dbh) {
    parent::__construct($dbh);
  }

  public function save($form) {
    $form[$this->model_name]['password'] = md5($form[$this->model_name]['password'].SALT);
    $form[$this->model_name]['notified_at'] = null;
    $form[$this->model_name]['role_id'] = USER_ROLE_ID;
    $form[$this->model_name]['authentication_key'] = StringUtil::makeRandStr(16);
    parent::save($form);
    return $form;
  }

  public function update($form) {
    $session = Session::get();
    unset($form[$this->model_name]['password_confirm']);
    $form[$this->model_name]['password'] = md5($form[$this->model_name]['password'].SALT);
    $form[$this->model_name]['notified_at'] = date('Y-m-d H:i:s');
    unset($form[$this->model_name]['role_id']);
    $form[$this->model_name]['authentication_key'] = null;
    parent::save($form);
    return $form;
  }

  public function auth($form) {
    $form[$this->model_name]['password'] = md5($form[$this->model_name]['password'].SALT);
    $data = $this->where('User.username', '=', $form[$this->model_name]['username'])
                 ->where('User.password', '=', $form[$this->model_name]['password'])
                 ->where('User.authentication_key', 'IS NULL', null)
                 ->find('first');
    return $data;
  }
}
