<?php
class Migrate20200105094501CreateTableUserInfo extends BaseMigrate{
  private $dbh = null;
  public function __construct($default_database) {
    parent::__construct($default_database);
  }

  public function up() {
    $sql = <<<EOM
CREATE TABLE user_infos (
  id int(9) NOT NULL AUTO_INCREMENT,
  first_name varchar(64) NOT NULL,
  last_name varchar(64) NOT NULL,
  pref_id int(8) NOT NULL,
  city varchar(32) NOT NULL,
  address varchar(64) NOT NULL,
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
DROP TABLE user_infos;
EOM;
    parent::down($sql);
  } 
}