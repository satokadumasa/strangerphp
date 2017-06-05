<?php
class DefaultController extends BaseController {
  public function __construct($uri, $url = null) {
    $conf = Config::get('database.config');
    $database = $conf['default_database'];
    parent::__construct($database, $uri, $url);
    $this->controller_class_name = str_replace('Controller', '', get_class($this));;
  }

  public function index() {
    // echo "SESSION:".print_r($_SESSION, true)."<br>";
    // $this->set('session', $_SESSION);
    $this->debug->log("DefaultController::index() SESSION:".print_r($_SESSION, true));
    $this->debug->log("DefaultController::index() COOKIE:".print_r($_COOKIE, true));
    $this->set('action_name', 'Home');
    $this->set('Title', 'Home');
    $this->set('datas', null);
  }
}