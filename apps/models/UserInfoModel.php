<?php
class UserInfoModel extends BaseModel {
  public $table_name  = 'user_infos';
  public $model_name  = 'UserInfo';
  public $model_class_name  = 'UserInfoModel';

  //  Relation
  public $belongthTo = null;
  public $has = null;
  public $has_many_and_belongs_to = null;

  public $columns = array(
    <!----columns---->
    'created_at' => array('type' => 'datetime', 'length' => 19, 'null' => false, 'key' => 'PRI', 'default' => null, ), 
    'modified_at' => array('type' => 'datetime', 'length' => 19, 'null' => false, 'key' => 'PRI', 'default' => null, ), 
  );

  public function __construct(&$dbh) {
    parent::__construct($dbh);
  }
}
