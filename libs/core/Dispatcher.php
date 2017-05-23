<?php
class Dispatcher {
  private $route;
  private $default_database;
  public function __construct($route, $default_database) {
    $this->route = $route;
    $this->default_database = $default_database;
    $this->error_log = new Logger('ERROR');
    $this->info_log = new Logger('INFO');
    $this->debug = new Logger('DEBUG');
  }

  public function dispatcheController() {
    $this->debug->log("ENV:" . print_r($_SERVER, true));
    $this->debug->log("routes:" . print_r($this->route, true));

    $route = $this->route->findRoute($_SERVER['REQUEST_URI']);
    $this->debug->log("route:" . print_r($route, true));
    $controller_name = $route['controller'];
    $this->debug->log("Dispatcher::__construct() controller:".$controller_name.":");
    $this->debug->log("Dispatcher::__construct() POST:".print_r($_POST, true).":");
    $this->debug->log("Dispatcher::__construct() GET:".print_r($_GET, true).":");
    $this->debug->log("Dispatcher::__construct() route:".print_r($route, true).":");
    $controller = new $controller_name($this->default_database, $route['uri'], $_SERVER['REQUEST_URI']);
    $controller->setAction($route['action']);
    $controller->beforeAction();
    $controller->$route['action']();
    $controller->afterAction();
    $controller->render();
    exit();
  }

  // private function 
}