<?php
class DefaultController extends BaseController {
  public function __construct($uri, $url = null) {
    $conf = Config::get('database.config');
    $database = $conf['default_database'];
    parent::__construct($database, $uri, $url);
    $this->controller_class_name = str_replace('Controller', '', get_class($this));;
  }

  public function index() {
    $this->set('action_name', 'Home');
    $this->set('Title', 'Home');
    $this->set('datas', null);
  }

  public function error() {
    $this->set('action_name', 'Error');
    $this->set('Title', 'Home');
    $this->set('datas', null);
  }
}