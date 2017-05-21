<?php
class UserInfoController extends BaseController{
  public function __construct($default_database, $uri, $url = null) {
    parent::__construct($default_database, $uri, $url);
    $this->controller_class_name = str_replace('Controller', '', get_class($this));;
  }

  public function index() {
    $user_infos = new UserInfoModel($this->dbh);
    $limit = 10 * (isset($this->request['page']) ? $this->request['page'] : 1);
    $offset = 10 * (isset($this->request['page']) ? $this->request['page'] - 1 : 0);

    $datas = $user_infos->where('UserInfo.id', '>', 0)->limit($limit)->offset($offset)->find('all');

    $ref = isset($this->request['page']) ? $this->request['page'] : 0;
    $next = isset($this->request['page']) ? $this->request['page'] + 1 : 2;

    $this->set('Title', 'UserInfo List');
    $this->set('datas', $datas);
    $this->set('UserInfo', $datas);
    $this->debug->log("UsersController::index() datas:".print_r($datas,true));
    $this->set('ref', $ref);
    $this->set('next', $next);
  }

  public function show() {
    $datas = null;
    $this->debug->log("UserInfoController::show() request:" . print_r($this->request, true));
    $id = $this->request['id'];

    $user_infos = new UserInfoModel($this->dbh);
    $datas = $user_infos->where('UserInfo.id', '=', $id)->find('first');
    $this->debug->log("UsersController::index() datas:".print_r($datas,true));
    $this->set('Title', 'UserInfo Ditail');
    $this->set('UserInfo', $datas['UserInfo']);
    $this->set('datas', $datas);
  }

  public function create() {
    $this->debug->log("UserInfoController::create()");
  }

  public function save(){
    $this->debug->log("UserInfoController::save()");
    $this->debug->log("UserInfoController::create() request:" . print_r($this->request, true));
    try {
      echo "UserInfoController::create()<br>";
      $this->dbh->beginTransaction();
      $user_infos = new UserInfoModel($this->dbh);
      $user_infos->save($this->request);
      $this->dbh->commit();
      $url = BASE_URL . UserInfo . '/show/' . $user_infos->primary_key_value . '/';
      $this->redirect($url);
    } catch (Exception $e) {
      $this->debug->log("UserInfoController::create() error:" . $e->getMessage());
    }
  }

  public function edit() {
    $this->debug->log("UserInfoController::edit()");
    try {
      $datas = null;
      $id = $this->request['id'];

      $user_infos = new UserInfoModel($this->dbh);
      $datas = $user_infos->where('UserInfo.id', '=', $id)->find('first');
      $this->set('Title', 'UserInfo Edit');
      $this->set('datas', $datas);
    } catch (Exception $e) {
      $this->debug->log("UserInfoController::edit() error:" . $e->getMessage());
    }
  }

  public function delete() {
    try {
      $this->dbh->beginTransaction();
      $user_infos = new UserInfoModel($this->dbh);
      $user_infos->delete($this->request['id']);
      $this->dbh->commit();
      $url = BASE_URL . UserInfo . '/index/';
      $this->redirect($url);
    } catch (Exception $e) {
      $this->debug->log("UsersController::delete() error:" . $e->getMessage());
    }
  }
}