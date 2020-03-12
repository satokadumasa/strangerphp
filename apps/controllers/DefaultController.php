<?php
class DefaultController extends BaseController {
  public function __construct($uri, $url = null) {
    $database = Config::get('database.config');
    parent::__construct($database["default_database"], $uri, $url);
    $this->controller_class_name = str_replace('Controller', '', get_class($this));;
  }

  public function index() {
    $this->set('action_name', 'Home');
    $user = new User();
    $user->contain(['UserInfo','Board' => ['Page]])->find();
    $this->set('Title', 'Home');
    $this->set('datas', null);
  }

  public function error() {
    $this->set('action_name', 'Error');
    $this->set('Title', 'Home');
    $this->set('datas', null);
  }
}
