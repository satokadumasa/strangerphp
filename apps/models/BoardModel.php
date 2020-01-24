<?php
class BoardModel extends BaseModel {
  public $table_name  = 'boards';
  public $model_name  = 'Board';
  public $model_class_name  = 'BoardModel';

  //  Relation
  public $belongthTo = null;
  public $has = null;
  public $has_many_and_belongs_to = null;

  public function __construct(&$dbh) {
    parent::__construct($dbh);
  }
}
