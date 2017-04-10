<?php
class DbConnect {
  protected $debug = null;

  public function __construct() {
  }

  public function setConnectionInfo($connection_info){
    $this->debug = new Logger('DEBUG');
    $this->rdb = $connection_info['rdb'];
    $this->host = $connection_info['host'];
    $this->dbname = $connection_info['dbname'];
    $this->charset = $connection_info['charset'];
    $this->username = $connection_info['username'];
    $this->password = $connection_info['password'];
  }

  public function createConnection() {
    try {
      $dsn = $this->rdb . ":host=" . $this->host . ";dbname=" . $this->dbname . ";charset=" . $this->charset;
      $this->debug->log("DbConnect::createConnection() dsn:".$dsn);
      $this->debug->log("DbConnect::createConnection() username:".$this->username);
      $this->debug->log("DbConnect::createConnection() password:".$this->password);

      $dbh = new PDO(
        $dsn,
        $this->username,
        $this->password,
        array(PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_FETCH_TABLE_NAMES => 1));

      return $dbh;
    } catch (PDOException $e) {
      throw new Exception('データベース接続失敗。'.$e->getMessage(), 1);
    }
  }
}