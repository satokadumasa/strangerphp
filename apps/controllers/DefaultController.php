<?php
class DefaultController {
  public function __construct() {
    echo "DefaultController::__construct()<br>";
    $this->controller_class_name = str_replace('Controller', '', get_class($this));;
  }

  public function index() {
    echo "DefaultController::index()<br>";
  }
}