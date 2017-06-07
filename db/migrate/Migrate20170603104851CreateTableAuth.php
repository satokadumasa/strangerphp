<?php
class Migrate20170603104851CreateTableAuth extends BaseMigrate{
  private $dbh = null;
  public function __construct($default_database) {
    parent::__construct($default_database);
  }

  public function up() {
    $sql = <<<EOM
CREATE TABLE auths (
  id int(9) NOT NULL AUTO_INCREMENT,
  username varchar(64) NOT NULL,
  password varchar(64) NOT NULL,
  role_id int(8) NOT NULL,
  email varchar(128) NOT NULL,
  notified_at datetime NOT NULL,
  authentication_key varchar(128) NOT NULL,
  created_at datetime NOT NULL,
  modified_at datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY index_auths_id (id)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
EOM;
    parent::up($sql);
  }

  public function down(){
    $sql = <<<EOM
DROP TABLE auths;
EOM;
    parent::down($sql);
  } 
}