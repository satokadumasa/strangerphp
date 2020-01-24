<?php
class RoleModel extends BaseModel {
  public $table_name  = 'roles';
  public $model_name  = 'Role';
  public $model_class_name  = 'RoleModel';

  //  Relation
  public $belongthTo = null;
  public $has = null;
  public $has_many_and_belongs_to = null;

  public function __construct(&$dbh) {
    parent::__construct($dbh);
  }
}
