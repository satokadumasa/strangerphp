<?php
class BaseController {
  //  ログ関連
  public $error_log;
  public $info_log;
  public $debug;

  //  データベースハンドラー  
  protected $dbh = null;
  protected $dbConnect = null;
  protected $request = [];
  protected $view = null;
  protected $datas = [];

  public $action = null;
  public $controller_class_name = null;
  //  認証関連
  protected $auth_check = [];
  public $roles = [];
  public $role_ids = [];

  public function __construct($database, $uri, $url) {
    Session::sessionStart();
    $this->error_log = new Logger('ERROR');
    $this->info_log = new Logger('INFO');
    $this->debug = new Logger('DEBUG');
    
    $this->dbConnect = new DbConnect();
    $this->dbConnect->setConnectionInfo($database);
    $this->dbh = $this->dbConnect->createConnection();
    $this->defaultSet();
    $this->setRequest($uri, $url);
    $this->view = new View();
  }

  protected function defaultSet(){
    $this->set('document_root',DOCUMENT_ROOT);
    if (isset($_SESSION[COOKIE_NAME]['error_message'])) {
      $this->set('error_message', $_SESSION[COOKIE_NAME]['error_message']);
    }
    //    $this->set('Sitemenu',)
    $session = Session::get();
    if (isset($session['Auth'])) {
      $log_out_str = "<a href='".DOCUMENT_ROOT."logout/'>Logout</a>";
    }
    else {
      $log_out_str = "<a href='".DOCUMENT_ROOT."login/'>Login</a>";
    }
    $this->set('Sitemenu',$log_out_str);
    Session::deleteMessage('error_message');
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
    if ($this->auth_check && in_array($this->action, $this->auth_check)) {
      $auth = Authentication::isAuth();
      if($auth){
        if ($this->role_ids && !Authentication::roleCheck($this->role_ids, $this->action)){
          $this->redirect(DOCUMENT_ROOT);
          exit();
        }
      } 
    }
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
