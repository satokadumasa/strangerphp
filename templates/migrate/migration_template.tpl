<?php
class <!----migration_class_name----> extends BaseMigrate{
  private $dbh = null;
  public function __construct($default_database) {
    parent::__construct($default_database);
  }

  public function up() {
    $sql = <<<EOM
    <!----up_template---->
EOM;
    parent::up($sql);
  }

  public function down(){
    $sql = <<<EOM
    <!----down_template---->
EOM;
    parent::down($sql);
  } 
}