<?php
class MigrationModel extends BaseModel {
  public $table_name  = 'migrations';
  public $model_name  = 'Migration';
  public $model_class_name  = 'MigrationModel';
  public $primary_key = 'version';

  public $belongthTo  = null;
  public $has = null;
  public $has_many_and_belongs_to = null;

  public $columns = array(
    'version' => array('type' => 'int', 'length' => 11, 'null' => false, 'key' => 'PRI', 'default' => null, ), 
    'name' => array('type' => 'varchar', 'length' => 32, 'null' => false, 'key' => '', 'default' => null, ), 
    'created_at' => array('type' => 'datetime', 'length' => 19, 'null' => false, 'key' => 'PRI', 'default' => null, ), 
    'modified_at' => array('type' => 'datetime', 'length' => 19, 'null' => false, 'key' => 'PRI', 'default' => null, ), 
  );

  public function __construct(&$dbh = null) {
    parent::__construct($dbh);
  }

  public function insert($data) {
    $sql = <<<EOM
INSERT INTO $this->table_name (
  version,
  name,
  created_at,
  modified_at
) VALUES (
  :version,
  :name,
  now(),
  now()
);
EOM;
    $stmt = $this->dbh->prepare($sql);
    $stmt->bindValue('version', $data[$this->model_name]['version'], PDO::PARAM_INT);
    $stmt->bindValue('name', $data[$this->model_name]['name'], PDO::PARAM_STR);
    $stmt->execute();
  }
}