<?php
class UserInfoController extends BaseController{
  public function __construct($uri, $url = null) {
    $conf = Config::get('database.config');
    $database = $conf['default_database'];
    parent::__construct($database, $uri, $url);
    $this->controller_class_name = str_replace('Controller', '', get_class($this));;
    //$this->role_ids = Config::get('acc/user_infos');
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
    $this->set('ref', $ref);
    $this->set('next', $next);
  }

  public function show() {
    $datas = null;
    $id = $this->request['id'];

    $user_infos = new UserInfoModel($this->dbh);
    $datas = $user_infos->where('UserInfo.id', '=', $id)->find('first');
    $this->set('Title', 'UserInfo Ditail');
    $this->set('UserInfo', $datas['UserInfo']);
    $this->set('datas', $datas);
  }

  public function create() {
    $this->debug->log("UserInfoController::create()");
    $user_infos = new UserInfoModel($this->dbh);
    $form = $user_infos->createForm();
    $this->set('Title', 'UserInfo Create');
    $this->set('UserInfo', $form['UserInfo']);
  }

  public function save(){
    $this->debug->log("UserInfoController::save()");
    try {
      $this->dbh->beginTransaction();
      $user_infos = new UserInfoModel($this->dbh);
      $user_infos->save($this->request);
      $this->dbh->commit();
      $url = BASE_URL . 'UserInfo' . '/show/' . $user_infos->primary_key_value . '/';
      $this->redirect($url);
    } catch (Exception $e) {
      $this->debug->log("UserInfoController::create() error:" . $e->getMessage());
      $this->set('Title', 'UserInfo Save Error');
      $this->set('error_message', '保存ができませんでした。');
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
      $this->set('UserInfo', $datas['UserInfo']);
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
      $url = BASE_URL . 'UserInfo' . '/index/';
    } catch (Exception $e) {
      $this->debug->log("UsersController::delete() error:" . $e->getMessage());
    }
  }


}