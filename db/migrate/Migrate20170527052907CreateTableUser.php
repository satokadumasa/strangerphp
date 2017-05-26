<?php
class Migrate20170527052907CreateTableUser extends BaseMigrate{
  private $dbh = null;
  public function __construct($default_database) {
    parent::__construct($default_database);
  }

  public function up() {
    $sql = <<<EOM
CREATE TABLE users (
  id int(9) NOT NULL AUTO_INCREMENT,
  user_name varchar(64) NOT NULL,
  password varchar(64) NOT NULL,
  role_id int(8) NOT NULL,
  delete_flag tinyint(1) ,
  created_at datetime NOT NULL,
  modified_at datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY index_users_id (id)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
EOM;
    parent::up($sql);
  }

  public function down(){
    $sql = <<<EOM
DROP TABLE users;
EOM;
    parent::down($sql);
  } 
}