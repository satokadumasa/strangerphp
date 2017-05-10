<?php
class <!----controller_name---->Controller extends BaseController{
  public function __construct($default_database, $uri, $url = null) {
    parent::__construct($default_database, $uri, $url);
    $this->controller_class_name = str_replace('Controller', '', get_class($this));;
  }

  public function index() {
    $this->debug->log("BooksController::index()");
    $<!----table_name----> = new BookModel($this->dbh);
    $books = $book->pagenate(2)->find();

    echo "UsersController::index()<br>";
  }

  public function show() {
    $this->debug->log("BooksController::show()");
    $datas = null;
    echo "datas:".print_r($datas, true);
  }

  public function create() {
    $this->debug->log("BooksController::create()");
  }

  public function edit() {
    $this->debug->log("BooksController::edit()");
  }

  public function delete() {
    echo "BooksController::delete()<br>";
    $this->debug->log("BooksController::delete() requestValues".print_r($this->requestValues, true));
  }
}