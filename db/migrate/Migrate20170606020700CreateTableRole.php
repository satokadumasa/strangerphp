<?php
class Migrate20170606020700CreateTableRole extends BaseMigrate{
  private $dbh = null;
  public function __construct($default_database) {
    parent::__construct($default_database);
  }

  public function up() {
    $sql = <<<EOM
CREATE TABLE roles (
  id int(9) NOT NULL AUTO_INCREMENT,
  name varchar(64) NOT NULL,
  created_at datetime NOT NULL,
  modified_at datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY index_roles_id (id)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
EOM;
    parent::up($sql);

    $sql = <<<EOM
INSERT INTO roles (name,created_at,modified_at) VALUES ('administrators',now(),now())
EOM;
    parent::up($sql);

    $sql = <<<EOM
INSERT INTO roles (name,created_at,modified_at) VALUES ('operators', now(), now())
EOM;
    parent::up($sql);

    $sql = <<<EOM
INSERT INTO roles (name,created_at,modified_at) VALUES ('users', now(), now())
EOM;
    parent::up($sql);
  }


  public function down(){
    $sql = <<<EOM
DROP TABLE roles;
EOM;
    parent::down($sql);
  } 
}