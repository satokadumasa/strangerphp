<?php
class BooksController extends BaseController{
  public function __construct($default_database, $uri, $url = null) {
    parent::__construct($default_database, $uri, $url);
  }

  public function index() {
    $this->debug->log("BooksController::index()");
    echo "dbh:".print_r($this->dbh, true)."<br>";
    $book = new BookModel($this->dbh);
    $books = $book->pagenate(2)->find();

    foreach ($books as $book) {
      echo "Users:".$book['Book']['id'].":".$book['Book']['name'].":".$book['User']['id'].":".$book['User']['name']."<br>";
    }
    $this->debug->log("BooksController::index() request:".print_r($this->request, true));
    echo "UsersController::index()<br>";
  }

  public function show() {
    $this->debug->log("BooksController::show()");
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