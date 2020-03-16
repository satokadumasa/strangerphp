<?php
class DefaultController extends BaseController {
  public function __construct($uri, $url = null) {
    $database = Config::get('database.config');
    parent::__construct($database["default_database"], $uri, $url);
    $this->controller_class_name = str_replace('Controller', '', get_class($this));;
  }

  public function index() {
    $this->debug->log("DefaultController::index() START");
    $this->set('action_name', 'Home');

    $this->debug->log("DefaultController::index() CH-01");
    $user = new User($this->dbh);
    $this->debug->log("DefaultController::index() CH-02");
    $this->debug->log("DefaultController::index() user:".print_r($user, true));
    $data = $user->contain(['UserInfo','Board' => ['Page']])
      ->select([
        'User' => [
          'id',
          'username',
        ],
        'UserInfo' => [
          'username AS name',
          'addres',
        ],
        'Board' => [
          'title',
          'created_at',
        ],
      ])->find();
    $this->debug->log("DefaultController::index() CH-03");

    $this->set('Title', 'Home');
    $this->set('datas', $data);
  }

  public function error() {
    $this->set('action_name', 'Error');
    $this->set('Title', 'Home');
    $this->set('datas', null);
  }
}
