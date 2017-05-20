<?php
class Controller extends BaseController{
  public function __construct($default_database, $uri, $url = null) {
    parent::__construct($default_database, $uri, $url);
    $this->controller_class_name = str_replace('Controller', '', get_class($this));;
  }

  public function index() {
    $ = new Model($this->dbh);
    $limit = 10 * (isset($this->request['page']) ? $this->request['page'] : 1);
    $offset = 10 * (isset($this->request['page']) ? $this->request['page'] - 1 : 0);

    $datas = $->where('.id', '>', 0)->limit($limit)->offset($offset)->find('all');

    $ref = isset($this->request['page'] ? $this->request['page'] : 0;
    $next = isset($this->request['page'] ? $this->request['page'] + 1 : 2;

    $this->set('Title', ' List');
    $this->set('datas', $datas);
    $this->set('ref', $ref);
    $this->set('next', $next);
    $thi
  }

  public function show() {
    $datas = null;
    $id = $this->request['id'];

    $ = new Model($this->dbh);
    $datas = $->where('.id', '=', $id)->find('first');
    $this->set('Title', ' Ditail');
    $this->set('datas', $datas);
  }

  public function create() {
    $this->debug->log("Controller::create()");
  }

  public function save(){
    $this->debug->log("Controller::save()");
    try {
      echo "Controller::create()<br>";
      $this->dbh->beginTransaction();
      $ = new Model($this->dbh);
      $->save($this->request);
      $->this->dbh->commit();
      primary_key_value
      $url = BASE_URL .  . '/show/' . $->primary_key_value;
      this->redirect($url);
    } catch (Exception $e) {
      $this->debug->log("Controller::create() error:" . $e->getMessage());
    }
  }

  public function edit() {
    $this->debug->log("Controller::edit()");
    try {
      $datas = null;
      $id = $this->request['id'];

      $ = new Model($this->dbh);
      $datas = $->where('.id', '=', $id)->find('first');
      $this->set('Title', ' Edit');
      $this->set('datas', $datas);
    } catch (Exception $e) {
      $this->debug->log("Controller::edit() error:" . $e->getMessage());
    }
  }

  public function delete() {
    try {
      $this->dbh->beginTransaction();
      $ = new .Model($this->dbh);
      $->delete($this->request['id']);
      $this->dbh->commit();
    } catch (Exception $e) {
      $this->debug->log("UsersController::delete() error:" . $e->getMessage());
    }
  }
}