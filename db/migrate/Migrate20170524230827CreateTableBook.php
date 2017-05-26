<?php
class Migrate20170524230827CreateTableBook extends BaseMigrate{
  private $dbh = null;
  public function __construct($default_database) {
    echo "Migrate20170524230827CreateTableBook\n";
    parent::__construct($default_database);
  }

  public function up() {
    $sql = <<<EOM
CREATE TABLE books (
  id int(9) NOT NULL AUTO_INCREMENT,
  name varchar(255) NOT NULL,
  passwd varchar(255) NOT NULL,
  created_at datetime NOT NULL,
  modified_at datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY index_books_id (id)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
EOM;
    parent::up($sql);
  }

  public function down(){
    $sql = <<<EOM
DROP TABLE books;
EOM;
    parent::down($sql);
  } 
}