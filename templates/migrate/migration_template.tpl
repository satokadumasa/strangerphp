<?php
class <!----class_name----> {
  private $dbh = null;
  public function __construct($default_database) {
    $this->error_log = new Logger('ERROR');
    $this->info_log = new Logger('INFO');
    $this->debug = new Logger('DEBUG');

    $this->dbConnect = new DbConnect();
    $this->dbConnect->setConnectionInfo($default_database);
    $this->dbh = $this->dbConnect->createConnection();
  }

  public function up() {
    $this->dbh->beginTransaction();

    $sql = <<<EOM
CREATE TABLE <!----table_name----> (
  id mediumint(9) NOT NULL AUTO_INCREMENT,
  <!----columns---->
  created_at datetime NOT NULL,
  modified_at datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY index_<!----pk_name----> (<!----pk_name---->)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
EOM;
    
    $this->dbh->query($sql);
    $this->dbh->commit();
  }

  public function down(){
    $this->dbh->beginTransaction();

    $sql = "DROP TABLE <!----table_name---->";
    
    $this->dbh->query($sql);
    $this->dbh->commit();
  } 
}