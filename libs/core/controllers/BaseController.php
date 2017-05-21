<?php
class BaseController {
  public $error_log;
  public $info_log;
  public $debug;

  protected $dbh = null;
  protected $dbConnect = null;
  protected $request = [];
  protected $view = null;
  protected $datas = [];

  public $action = null;
  public $controller_class_name = null;

  public function __construct($default_database, $uri, $url) {
    $this->error_log = new Logger('ERROR');
    $this->info_log = new Logger('INFO');
    $this->debug = new Logger('DEBUG');

    $this->dbConnect = new DbConnect();
    $this->dbConnect->setConnectionInfo($default_database);
    $this->dbh = $this->dbConnect->createConnection();
    
    $this->setRequest($uri, $url);
    $this->view = new View();
  }

  public function setRequest($uri, $url) {
    $this->debug->log("BaseController::getRequestValues() _POST".print_r($_POST, true));
    $this->debug->log("BaseController::getRequestValues() _GET".print_r($_GET, true));

    if (isset($_POST)) {
      foreach ($_POST as $key => $value) {
        $this->perseKey($key, $value);
      }
    }

    if (isset($_GET)) {
      foreach ($_GET as $key => $value) {
        $this->perseKey($key, $value);
      }
    }

    $urls = explode('/', explode('?', $url)[0]);
    $uris = explode('/', $uri);
    
    for ($i=0; $i < count($urls) - 1; $i++) { 
      if(!$uris[$i]) continue;
      if($uris[$i] == $urls[$i]) continue;
      $this->request[mb_strtolower($uris[$i], 'UTF-8')] = $urls[$i];
    }
    $this->debug->log("BaseController::getRequestValues() request".print_r($this->request, true));
  }

  /**
   *
   */
  public function setAction($action) {
    $this->action = $action;
  }

  /**
   *
   */
  public function beforeAction() {
    $this->debug->log("BaseController::befor()");
  }

  /**
   *
   */
  public function afterAction() {
    $this->debug->log("BaseController::after()");
  }

  /**
   *
   */
  protected function set($key, $data){
    $this->datas[$key] = $data;
  }

  /**
   *
   */
  public function render(){
    $this->view->render($this->controller_class_name, $this->action, $this->datas);
  }


  protected function perseKey($key, $value) {
    // $this->debug->log("BaseController::perseKey() key(array)".$key);
    // $keys = explode('::', $key);
    // if (is_array($keys)) {
    //   $this->debug->log("BaseController::perseKey() keys(array)".print_r($keys, true));
    //   $this->request = $this->set_lowest([], $keys, $value);
    //   $this->debug->log("BaseController::perseKey() request(array)".print_r($this->request, true));
    // }
    // else $this->request[$key] = $value;
    $this->request[$key] = $value;
  }

  protected function redirect($url) {
    header("Location: {$url}");
    exit;
  }
}
