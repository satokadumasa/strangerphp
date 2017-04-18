<?php
class UsersController extends BaseController{
  public function __construct($default_database, $uri, $url = null) {
    parent::__construct($default_database, $uri, $url);
  }

  public function index() {
    echo "UsersController::index() dbh:".var_dump($this->dbh, true)."<br>";
    $user = new UserModel($this->dbh);
    $users = $user->where('User.id', '>', 1)->limit(10)->offset(0)->find();
    foreach ($users as $user) {
      echo "Users:".$user['User']['id'].":".$user['User']['name']."<br>";
    }

    echo "UsersController::index()<br>";
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
