<?php
class UsersController extends BaseController{
  public function __construct($default_database, $uri, $url = null) {
    parent::__construct($default_database, $uri, $url);
  }

  public function index() {
    echo "UsersController::index() dbh:".var_dump($this->dbh, true)."<br>";
    $user = new UserModel($this->dbh);
    $this->debug->log("UsersController::index() has_many_and_belongs_to:".print_r($user->has_many_and_belongs_to, true));
    $users = $user->where('User.id', '>', 1)->limit(10)->offset(0)->find('all');
    foreach ($users as $user) {
      echo "Users:".$user['User']['id'].":".$user['User']['name']."<br>";
    }

    $this->debug->log("UsersController::index() users:".print_r($users, true));
    echo "UsersController::index()<br>";
    exit();
  }

  public function show() {
    echo "UsersController::show()<br>";
    $this->debug->log("UsersController::index() requestValues".print_r($this->request, true));
  }

  public function create() {
    try {
      $this->dbh->beginTransaction();
      $user = new UserModel($this->dbh);
      $user->save($this->request);
      echo "UsersController::create()<br>";
      $this->dbh->commit();
    } catch (Exception $e) {
      $this->debug->log("UsersController::create() error:" . $e->getMessage());
    }
  }

  public function edit() {
    echo "UsersController::edit()<br>";
  }

  public function delete() {
    try {
      $this->dbh->beginTransaction();
      $user = new UserModel($this->dbh);
      $user->delete($this->request['id']);
      $this->dbh->commit();
      echo "UsersController::delete()<br>";
      $this->debug->log("UsersController::delete() request:".print_r($this->request, true));
    } catch (Exception $e) {
      $this->debug->log("UsersController::delete() error:" . $e->getMessage());
    }
  }
}
