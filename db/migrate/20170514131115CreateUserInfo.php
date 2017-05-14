<?php
class 20170514131115CreateUserInfo {
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
