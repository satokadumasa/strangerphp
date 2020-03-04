<?php
class BoardController extends BaseController{
  public function __construct($uri, $url = null) {
    $conf = Config::get('database.config');
    $database = $conf['default_database'];
    parent::__construct($database, $uri, $url);
    $this->controller_class_name = str_replace('Controller', '', get_class($this));;
    //$this->role_ids = Config::get('acc/boards');
  }

  public function index() {
    $boards = new Board($this->dbh);
    $limit = 10 * (isset($this->request['page']) ? $this->request['page'] : 1);
    $offset = 10 * (isset($this->request['page']) ? $this->request['page'] - 1 : 0);

    $datas = $boards->where('Board.id', '>', 0)->limit($limit)->offset($offset)->find('all');

    $ref = isset($this->request['page']) ? $this->request['page'] : 0;
    $next = isset($this->request['page']) ? $this->request['page'] + 1 : 2;

    $this->set('Title', 'Board List');
    $this->set('datas', $datas);
    $this->set('Board', $datas);
    $this->set('ref', $ref);
    $this->set('next', $next);
  }

  public function show() {
    $datas = null;
    $id = $this->request['id'];

    $boards = new Board($this->dbh);
    $datas = $boards->where('Board.id', '=', $id)->find('first');
    $this->set('Title', 'Board Ditail');
    $this->set('Board', $datas['Board']);
    $this->set('datas', $datas);
  }

  public function create() {
    $this->debug->log("BoardController::create()");
    $boards = new Board($this->dbh);
    $form = $boards->createForm();
    $this->set('Title', 'Board Create');
    $this->set('Board', $form['Board']);
  }

  public function save(){
    $this->debug->log("BoardController::save()");
    try {
      $this->dbh->beginTransaction();
      $boards = new Board($this->dbh);
      $boards->save($this->request);
      $this->dbh->commit();
      $url = BASE_URL . 'Board' . '/show/' . $boards->primary_key_value . '/';
      $this->redirect($url);
    } catch (Exception $e) {
      $this->debug->log("BoardController::create() error:" . $e->getMessage());
      $this->set('Title', 'Board Save Error');
      $this->set('error_message', '保存ができませんでした。');
    }
  }

  public function edit() {
    $this->debug->log("BoardController::edit()");
    try {
      $datas = null;
      $id = $this->request['id'];

      $boards = new Board($this->dbh);
      $datas = $boards->where('Board.id', '=', $id)->find('first');
      $this->set('Title', 'Board Edit');
      $this->set('Board', $datas['Board']);
      $this->set('datas', $datas);
    } catch (Exception $e) {
      $this->debug->log("BoardController::edit() error:" . $e->getMessage());
    }
  }

  public function delete() {
    try {
      $this->dbh->beginTransaction();
      $boards = new Board($this->dbh);
      $boards->delete($this->request['id']);
      $this->dbh->commit();
      $url = BASE_URL . 'Board' . '/index/';
    } catch (Exception $e) {
      $this->debug->log("UsersController::delete() error:" . $e->getMessage());
    }
  }


}
