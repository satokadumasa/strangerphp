<?php
class DefaultController extends BaseController {
  public function __construct($uri, $url = null) {
    parent::__construct(Config::get('database.config', 'default_database'), $uri, $url);
    $this->controller_class_name = str_replace('Controller', '', get_class($this));;
  }

  public function index() {
    $this->set('action_name', 'Home');
    $this->set('Title', 'Home');
    $this->set('datas', null);
  }
}