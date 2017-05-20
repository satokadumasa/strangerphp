<?php
class UserDetailController extends BaseController{
  public function __construct($default_database, $uri, $url = null) {
    parent::__construct($default_database, $uri, $url);
    $this->controller_class_name = str_replace('Controller', '', get_class($this));;
  }

  public function index() {
    $user_details = new UserDetailModel($this->dbh);
    $limit = 10 * (isset($this->request['page']) ? $this->request['page'] : 1);
    $offset = 10 * (isset($this->request['page']) ? $this->request['page'] - 1 : 0);

    $datas = $user_details->where('UserDetail.id', '>', 0)->limit($limit)->offset($offset)->find('all');

    $ref = isset($this->request['page'] ? $this->request['page'] : 0;
    $next = isset($this->request['page'] ? $this->request['page'] + 1 : 2;

    $this->set('Title', 'UserDetail List');
    $this->set('datas', $datas);
    $this->set('ref', $ref);
    $this->set('next', $next);
    $thi
  }

  public function show() {
    $datas = null;
    $id = $this->request['id'];

    $user_details = new UserDetailModel($this->dbh);
    $datas = $user_details->where('UserDetail.id', '=', $id)->find('first');
    $this->set('Title', 'UserDetail Ditail');
    $this->set('datas', $datas);
  }

  public function create() {
    $this->debug->log("BooksController::create()");
  }

  public function edit() {
    $this->debug->log("BooksController::edit()");
  }

  public function delete() {
    try {
      $this->dbh->beginTransaction();
      $user_details = new UserDetail.Model($this->dbh);
      $user_details->delete($this->request['id']);
      $this->dbh->commit();
    } catch (Exception $e) {
      $this->debug->log("UsersController::delete() error:" . $e->getMessage());
    }
  }
}