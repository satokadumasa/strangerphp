<?php
class Migrate20170601153806CreateTableUser extends BaseMigrate{
  private $dbh = null;
  public function __construct($default_database) {
    parent::__construct($default_database);
  }

  public function up() {
    $sql = <<<EOM
CREATE TABLE users (
  id int(9) NOT NULL AUTO_INCREMENT,
  username varchar(64) unique NOT NULL,
  password varchar(64) NOT NULL,
  role_id int(8) NOT NULL,
  email varchar(128) NOT NULL,
  notified_at datetime default null,
  authentication_key varchar(128),
  created_at datetime NOT NULL,
  modified_at datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY index_email (email)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
EOM;
    parent::up($sql);

    $sql = <<<EOM
INSERT INTO users (username,password,role_id,email,notified_at,created_at,modified_at) VALUES ('administrator','269cb049e5460a656fecd6fff86df3d6',1,'administrator@example.com',now(),now(),now())
EOM;
    parent::up($sql);
    
    $sql = <<<EOM
INSERT INTO users (username,password,role_id,email,notified_at,created_at,modified_at) VALUES ('operator1','269cb049e5460a656fecd6fff86df3d6',2,'administrator@example.com',now(),now(),now())
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