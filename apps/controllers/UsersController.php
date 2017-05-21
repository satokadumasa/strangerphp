<?php
class UsersController extends BaseController{
  public function __construct($default_database, $uri, $url = null) {
    parent::__construct($default_database, $uri, $url);
    $this->controller_class_name = str_replace('Controller', '', get_class($this));;
  }

  public function index() {
    $user = new UserModel($this->dbh);
    $limit = 10 * (isset($this->request['page']) ? $this->request['page'] : 1);
    $offset = 10 * (isset($this->request['page']) ? $this->request['page'] - 1 : 0);
    $users = $user->where('User.id', '>', 0)->limit($limit)->offset($offset)->find('all');

    $this->set('Title', 'ユーザー情報詳細');
    $this->set('User', $users);


    $this->debug->log("UsersController::index() users:".print_r($users, true));
  }

  public function show() {
    $this->debug->log("UsersController::index() requestValues".print_r($this->request, true));
    $id = $this->request['id'];
    $user = new UserModel($this->dbh);
    $datas = $user->where('User.id', '=', $id)->find('first');
    $this->set('Title', 'ユーザー情報詳細');
    $this->set('User', $datas['User']);
  }

  public function create() {
    try {
      echo "UsersController::create()<br>";
      $this->debug->log("UsersController::create() request:" . print_r($this->request, true));
      $this->dbh->beginTransaction();
      $user = new UserModel($this->dbh);
      $user->save($this->request);
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
