<?php
class 20170514133301CreateUserInfo {
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
CREATE TABLE user_infos (
  id mediumint(9) NOT NULL AUTO_INCREMENT,
  name string(255) NOT NULL,
  address string(255) NOT NULL,
  created_at datetime NOT NULL,
  modified_at datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY index_user_infos_id (id)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
    <!----up_template---->
EOM;
    
    $this->dbh->query($sql);
    $this->dbh->commit();
  }

  public function down(){
    $this->dbh->beginTransaction();

    $sql = <<<EOM
  ALTER TABLE user_infos DROP COLUMN <!----column_name---->;
    <!----down_template---->
EOM;
    
    $this->dbh->query($sql);
    $this->dbh->commit();
  } 
}