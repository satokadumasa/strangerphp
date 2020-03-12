<?php
class PageController extends BaseController{
  public function __construct($uri, $url = null) {
    $conf = Config::get('database.config');
    $database = $conf['default_database'];
    parent::__construct($database, $uri, $url);
    $this->controller_class_name = str_replace('Controller', '', get_class($this));;
    //$this->role_ids = Config::get('acc/pages');
  }

  public function index() {
    $pages = new Page($this->dbh);
    $limit = 10 * (isset($this->request['page']) ? $this->request['page'] : 1);
    $offset = 10 * (isset($this->request['page']) ? $this->request['page'] - 1 : 0);

    $datas = $pages->where('Page.id', '>', 0)->limit($limit)->offset($offset)->find('all');

    $ref = isset($this->request['page']) ? $this->request['page'] : 0;
    $next = isset($this->request['page']) ? $this->request['page'] + 1 : 2;

    $this->set('Title', 'Page List');
    $this->set('datas', $datas);
    $this->set('Page', $datas);
    $this->set('ref', $ref);
    $this->set('next', $next);
  }

  public function show() {
    $datas = null;
    $id = $this->request['id'];

    $pages = new Page($this->dbh);
    $datas = $pages->where('Page.id', '=', $id)->find('first');
    $this->set('Title', 'Page Ditail');
    $this->set('Page', $datas['Page']);
    $this->set('datas', $datas);
  }

  public function create() {
    $this->debug->log("PageController::create()");
    $pages = new Page($this->dbh);
    $form = $pages->createForm();
    $this->set('Title', 'Page Create');
    $this->set('Page', $form['Page']);
  }

  public function save(){
    $this->debug->log("PageController::save()");
    try {
      $this->dbh->beginTransaction();
      $pages = new Page($this->dbh);
      $pages->save($this->request);
      $this->dbh->commit();
      $url = BASE_URL . 'Page' . '/show/' . $pages->primary_key_value . '/';
      $this->redirect($url);
    } catch (Exception $e) {
      $this->debug->log("PageController::create() error:" . $e->getMessage());
      $this->set('Title', 'Page Save Error');
      $this->set('error_message', '保存ができませんでした。');
    }
  }

  public function edit() {
    $this->debug->log("PageController::edit()");
    try {
      $datas = null;
      $id = $this->request['id'];

      $pages = new Page($this->dbh);
      $datas = $pages->where('Page.id', '=', $id)->find('first');
      $this->set('Title', 'Page Edit');
      $this->set('Page', $datas['Page']);
      $this->set('datas', $datas);
    } catch (Exception $e) {
      $this->debug->log("PageController::edit() error:" . $e->getMessage());
    }
  }

  public function delete() {
    try {
      $this->dbh->beginTransaction();
      $pages = new Page($this->dbh);
      $pages->delete($this->request['id']);
      $this->dbh->commit();
      $url = BASE_URL . 'Page' . '/index/';
    } catch (Exception $e) {
      $this->debug->log("UsersController::delete() error:" . $e->getMessage());
    }
  }


}