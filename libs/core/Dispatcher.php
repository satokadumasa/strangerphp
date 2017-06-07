<?php
class Dispatcher {
  private $route;
  private $default_database;
  public function __construct($route) {
    $this->route = $route;
    $this->error_log = new Logger('ERROR');
    $this->info_log = new Logger('INFO');
    $this->debug = new Logger('DEBUG');
  }

  public function dispatcheController() {
    // $this->debug->log("ENV:" . print_r($_SERVER, true));

    $route = $this->route->findRoute($_SERVER['REQUEST_URI']);
    $controller_name = $route['controller'];
    $controller = new $controller_name($route['uri'], $_SERVER['REQUEST_URI']);
    $controller->setAction($route['action']);
    $controller->beforeAction();
    $controller->$route['action']();
    $controller->afterAction();
    $controller->render();
    exit();
  }
}