<?php
class UserController extends BaseController{
  public function __construct($default_database, $uri, $url = null) {
    parent::__construct($default_database, $uri, $url);
    $this->controller_class_name = str_replace('Controller', '', get_class($this));;
  }

  public function index() {
    $users = new UserModel($this->dbh);
    $limit = 10 * (isset($this->request['page']) ? $this->request['page'] : 1);
    $offset = 10 * (isset($this->request['page']) ? $this->request['page'] - 1 : 0);

    $datas = $users->where('User.id', '>', 0)->limit($limit)->offset($offset)->find('all');

    $ref = isset($this->request['page']) ? $this->request['page'] : 0;
    $next = isset($this->request['page']) ? $this->request['page'] + 1 : 2;

    $this->set('Title', 'User List');
    $this->set('datas', $datas);
    $this->set('User', $datas);
    $this->set('ref', $ref);
    $this->set('next', $next);
  }

  public function show() {
    $datas = null;
    $id = $this->request['id'];

    $users = new UserModel($this->dbh);
    $datas = $users->where('User.id', '=', $id)->find('first');
    $this->set('Title', 'User Ditail');
    $this->set('User', $datas['User']);
    $this->set('datas', $datas);
  }

  public function create() {
    $this->debug->log("UserController::create()");
    $users = new UserModel($this->dbh);
    $form = $users->createForm();

    $prefs = [
      ['id' => '1','name' => '北海道'], 
      ['id' => '2','name' => '青森県'], 
      ['id' => '3','name' => '岩手県'], 
      ['id' => '4','name' => '宮城県'], 
      ['id' => '5','name' => '秋田県'], 
      ['id' => '6','name' => '山形県'], 
      ['id' => '7','name' => '福島県'], 
    ];
    $this->set('Title', 'User Create');
    $this->set('Prefecture', $prefs);
    $this->set('pref_id', array('0' => 3, ));
    $this->set('User', $form['User']);
  }

  public function save(){
    $this->debug->log("UserController::save()");
    try {
      $this->dbh->beginTransaction();
      $users = new UserModel($this->dbh);
      $users->save($this->request);
      $this->dbh->commit();
      $url = BASE_URL . User . '/show/' . $users->primary_key_value . '/';
      $this->redirect($url);
    } catch (Exception $e) {
      $this->debug->log("UserController::create() error:" . $e->getMessage());
    }
  }

  public function edit() {
    $this->debug->log("UserController::edit()");
    try {
      $datas = null;
      $id = $this->request['id'];

      $users = new UserModel($this->dbh);
      $datas = $users->where('User.id', '=', $id)->find('first');
      $this->set('Title', 'User Edit');
      $this->set('User', $datas['User']);
      $this->set('datas', $datas);
    } catch (Exception $e) {
      $this->debug->log("UserController::edit() error:" . $e->getMessage());
    }
  }

  public function delete() {
    try {
      $this->dbh->beginTransaction();
      $users = new UserModel($this->dbh);
      $users->delete($this->request['id']);
      $this->dbh->commit();
      $url = BASE_URL . User . '/index/';
    } catch (Exception $e) {
      $this->debug->log("UsersController::delete() error:" . $e->getMessage());
    }
  }


}