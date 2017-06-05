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
  protected $auth_check = [];

  public function __construct($database, $uri, $url) {
    $this->error_log = new Logger('ERROR');
    $this->info_log = new Logger('INFO');
    $this->debug = new Logger('DEBUG');
    
    $this->dbConnect = new DbConnect();
    $this->dbConnect->setConnectionInfo($database);
    $this->dbh = $this->dbConnect->createConnection();
    
    $this->setRequest($uri, $url);
    $this->view = new View();
  }

  public function setRequest($uri, $url) {
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
      if(!isset($uris[$i])) continue;
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
    Session::sessionStart();
    $this->debug->log("BaseController::beforeAction() CH-01:".print_r($this->auth_check, true));
    $this->debug->log("BaseController::beforeAction() action:".$this->action);
    if ($this->auth_check && in_array($this->action, $this->auth_check)) {
      $this->debug->log("BaseController::beforeAction() CH-02");
      if(!Authentication::isAuth()){
        $this->debug->log("BaseController::beforeAction() CH-03");
        $this->redirect('/');
        exit();
      }
    }
  }

  /**
   *
   */
  public function afterAction() {
    // setcookie(COOKIE_NAME, $user_cookie_name, COOKIE_LIFETIME, '/', DOMAIN_NAME);
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
    $this->set('SiteTitle', SITE_NAME);
    $this->view->render($this->controller_class_name, $this->action, $this->datas);
  }


  protected function perseKey($key, $value) {
    $this->request[$key] = $value;
  }

  protected function redirect($url) {
    header("Location: {$url}");
    exit;
  }

  public function setAuthCheck($actions) {
    $this->auth_check = $actions;
  }
}
