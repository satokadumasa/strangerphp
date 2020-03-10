<?php
class Migrate20200310231529CreateTableBoard extends BaseMigrate{
  private $dbh = null;
  public function __construct($default_database) {
    parent::__construct($default_database);
  }

  public function up() {
    $sql = <<<EOM
CREATE TABLE board (
  id int(9) NOT NULL AUTO_INCREMENT,
  user_id int(8) ,
  title varchar(128) NOT NULL,
  body text NOT NULL,
  created_at datetime NOT NULL,
  modified_at datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY index_board_id (id)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
EOM;
    parent::up($sql);
  }

  public function down(){
    $sql = <<<EOM
DROP TABLE board;
EOM;
    parent::down($sql);
  } 
}