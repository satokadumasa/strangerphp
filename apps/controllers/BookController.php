<?php
class BookController extends BaseController{
  public function __construct($default_database, $uri, $url = null) {
    parent::__construct($default_database, $uri, $url);
    $this->controller_class_name = str_replace('Controller', '', get_class($this));;
  }

  public function index() {
    $books = new BookModel($this->dbh);
    $limit = 10 * (isset($this->request['page']) ? $this->request['page'] : 1);
    $offset = 10 * (isset($this->request['page']) ? $this->request['page'] - 1 : 0);

    $datas = $books->where('Book.id', '>', 0)->limit($limit)->offset($offset)->find('all');

    $ref = isset($this->request['page']) ? $this->request['page'] : 0;
    $next = isset($this->request['page']) ? $this->request['page'] + 1 : 2;

    $this->set('Title', 'Book List');
    $this->set('datas', $datas);
    $this->set('Book', $datas);
    $this->set('ref', $ref);
    $this->set('next', $next);
  }

  public function show() {
    $datas = null;
    $id = $this->request['id'];

    $books = new BookModel($this->dbh);
    $datas = $books->where('Book.id', '=', $id)->find('first');
    $this->set('Title', 'Book Ditail');
    $this->set('Book', $datas['Book']);
    $this->set('datas', $datas);
  }

  public function create() {
    $this->debug->log("BookController::create()");
    $books = new BookModel($this->dbh);
    $form = $books->createForm();
    $this->set('Title', 'Book Create');
    $this->set('Book', $form['Book']);
  }

  public function save(){
    $this->debug->log("BookController::save()");
    try {
      echo "BookController::create()<br>";
      $this->dbh->beginTransaction();
      $books = new BookModel($this->dbh);
      $books->save($this->request);
      $this->dbh->commit();
      $url = BASE_URL . Book . '/show/' . $books->primary_key_value . '/';
      $this->redirect($url);
    } catch (Exception $e) {
      $this->debug->log("BookController::create() error:" . $e->getMessage());
    }
  }

  public function edit() {
    $this->debug->log("BookController::edit()");
    try {
      $datas = null;
      $id = $this->request['id'];

      $books = new BookModel($this->dbh);
      $datas = $books->where('Book.id', '=', $id)->find('first');
      $this->set('Title', 'Book Edit');
      $this->set('Book', $datas['Book']);
      $this->set('datas', $datas);
    } catch (Exception $e) {
      $this->debug->log("BookController::edit() error:" . $e->getMessage());
    }
  }

  public function delete() {
    try {
      $this->dbh->beginTransaction();
      $books = new BookModel($this->dbh);
      $books->delete($this->request['id']);
      $this->dbh->commit();
      $url = BASE_URL . Book . '/index/';
    } catch (Exception $e) {
      $this->debug->log("UsersController::delete() error:" . $e->getMessage());
    }
  }


}