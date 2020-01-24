<?php
class AuthModel extends BaseModel {
  public $table_name  = 'users';
  public $model_name  = 'Auth';
  public $model_class_name  = 'AuthModel';

  //  Relation
  public $belongthTo = null;
  public $has = null;
  public $has_many_and_belongs_to = null;

  public function __construct(&$dbh) {
    parent::__construct($dbh);
  }

  public function save($form) {
    $form[$this->model_name]['password'] = isset($form[$this->model_name][$this->primary_key]) ? 
                                            $form[$this->model_name]['password'] : 
                                            md5($form[$this->model_name]['password'].SALT);
    $form[$this->model_name]['notified_at'] = date('Y-m-d H:i:s');
    $form[$this->model_name]['authentication_key'] = StringUtil::makeRandStr(16);
    parent::save($form);
    return $form;
  }

  public function auth($form) {
    $this->debug->log("AuthModel::auth() form:".print_r($form, true));
    $form[$this->model_name]['password'] = md5($form[$this->model_name]['password'].SALT);
    $data = $this->where('User.username', '=', $form[$this->model_name]['username'])
                 ->where('User.password', '=', $form[$this->model_name]['password'])
                 ->where('User.authentication_key', 'IS NULL', null)
                 ->find('first');
    return $data;
  }
}
