<?php
class BbseController extends BaseController{
  public function __construct($uri, $url = null) {
    $conf = Config::get('database.config');
    $database = $conf['default_database'];
    parent::__construct($database, $uri, $url);
    $this->controller_class_name = str_replace('Controller', '', get_class($this));;
    //$this->role_ids = Config::get('acc/bbses');
  }

  public function index() {
    $bbses = new Bbse($this->dbh);
    $limit = 10 * (isset($this->request['page']) ? $this->request['page'] : 1);
    $offset = 10 * (isset($this->request['page']) ? $this->request['page'] - 1 : 0);

    $datas = $bbses->where('Bbse.id', '>', 0)->limit($limit)->offset($offset)->find('all');

    $ref = isset($this->request['page']) ? $this->request['page'] : 0;
    $next = isset($this->request['page']) ? $this->request['page'] + 1 : 2;

    $this->set('Title', 'Bbse List');
    $this->set('datas', $datas);
    $this->set('Bbse', $datas);
    $this->set('ref', $ref);
    $this->set('next', $next);
  }

  public function show() {
    $datas = null;
    $id = $this->request['id'];

    $bbses = new Bbse($this->dbh);
    $datas = $bbses->where('Bbse.id', '=', $id)->find('first');
    $this->set('Title', 'Bbse Ditail');
    $this->set('Bbse', $datas['Bbse']);
    $this->set('datas', $datas);
  }

  public function create() {
    $this->debug->log("BbseController::create()");
    $bbses = new Bbse($this->dbh);
    $form = $bbses->createForm();
    $this->set('Title', 'Bbse Create');
    $this->set('Bbse', $form['Bbse']);
  }

  public function save(){
    $this->debug->log("BbseController::save()");
    try {
      $this->dbh->beginTransaction();
      $bbses = new Bbse($this->dbh);
      $bbses->save($this->request);
      $this->dbh->commit();
      $url = BASE_URL . 'Bbse' . '/show/' . $bbses->primary_key_value . '/';
      $this->redirect($url);
    } catch (Exception $e) {
      $this->debug->log("BbseController::create() error:" . $e->getMessage());
      $this->set('Title', 'Bbse Save Error');
      $this->set('error_message', '保存ができませんでした。');
    }
  }

  public function edit() {
    $this->debug->log("BbseController::edit()");
    try {
      $datas = null;
      $id = $this->request['id'];

      $bbses = new Bbse($this->dbh);
      $datas = $bbses->where('Bbse.id', '=', $id)->find('first');
      $this->set('Title', 'Bbse Edit');
      $this->set('Bbse', $datas['Bbse']);
      $this->set('datas', $datas);
    } catch (Exception $e) {
      $this->debug->log("BbseController::edit() error:" . $e->getMessage());
    }
  }

  public function delete() {
    try {
      $this->dbh->beginTransaction();
      $bbses = new Bbse($this->dbh);
      $bbses->delete($this->request['id']);
      $this->dbh->commit();
      $url = BASE_URL . 'Bbse' . '/index/';
    } catch (Exception $e) {
      $this->debug->log("UsersController::delete() error:" . $e->getMessage());
    }
  }


}