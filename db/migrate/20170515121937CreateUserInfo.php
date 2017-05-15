<?php
class 20170515121937CreateUserInfo extends BaseMigrate{
  private $dbh = null;
  public function __construct($default_database) {
    parent::__construct($default_database);
  }

  public function up() {
    $sql = <<<EOM
CREATE TABLE user_infos (
  id mediumint(9) NOT NULL AUTO_INCREMENT,
  name string(255) NOT NULL,
  address string(255) NOT NULL,
  created_at datetime NOT NULL,
  modified_at datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY index_user_infos_id (id)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
EOM;
    parent::up($sql);
  }

  public function down(){
    $sql = <<<EOM
  ALTER TABLE user_infos DROP COLUMN <!----column_name---->;
EOM;
    parent::down($sql);
  } 
}