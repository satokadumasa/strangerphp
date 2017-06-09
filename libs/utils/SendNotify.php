<?php

class SendNotify {
  //  ログ関連
  public $error_log;
  public $info_log;
  public $debug;
  protected $argv = [];
  protected $dbh = null;

  public function __construct() {
    $this->error_log = new Logger('ERROR');
    $this->info_log = new Logger('INFO');
    $this->debug = new Logger('DEBUG');
    $conf = Config::get('database.config');
    $database = $conf['default_database'];
    $dbConnect = new DbConnect();
    $dbConnect->setConnectionInfo($database);
    $this->dbh = $dbConnect->createConnection();
  }

  public function sendNotify(){
    $user = new UserModel($this->dbh);
    $users = $user->where('User.notified_at', 'IS NULL', '')->find('all');
    $this->debug->log("SendNotify::sendNotify() users".print_r($users, true));
    foreach ($users as $key => $form) {
      $body = null;
      $user = null;
      $notification = new Notification();
      $body = $notification->geterateRegistNotifyMessage($form, 'Mailer', 'regist_notify');
      $notification->sendRegistNotify($form, $body, '登録確認メール');
      $form['User']['notified_at'] = date('Y-m-d H:i:s');
      $this->debug->log("SendNotify::sendNotify() form".print_r($form, true));
      $user = new UserModel($this->dbh);
      $user->save($form);
    }
  }

}