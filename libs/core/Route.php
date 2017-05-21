<?php
class Route {
  public $route = [];
  private $default_actions = array('index', 'new', 'edit', 'create', 'save', 'confirm', 'show', 'delete');
  private $default_need_id_actions = array('new', 'edit', 'show', 'delete');
  private $url_not_found = array('controller' => 'DefaultController', 'action' => 'index');

  public $error_log;
  public $info_log;
  public $debug;
  private $CONV_STRING_LIST;

  public function __construct($CONV_STRING_LIST) {
    $this->CONV_STRING_LIST = $CONV_STRING_LIST;
    $this->error_log = new Logger('ERROR');
    $this->info_log = new Logger('INFO');
    $this->debug = new Logger('DEBUG');

    $this->setDefaultRoutes();
  }

  public function setRoute ($uri, $controller, $action) {
    $this->debug->log("Route::setDefaultRoutes() setRoute");
    $this->route[$uri] = array('controller' => $controller, 'action' => $action);
  }

  private function setDefaultRoutes() {
    $this->debug->log("Route::setDefaultRoutes() Start");

    $file_list = $this->getFileList(CONTROLLER_PATH);
    foreach ($file_list as $file_name) {
      $namespace  = null;
      $controller = str_replace(CONTROLLER_PATH, '', $file_name);
      $controller = str_replace('Controller.php', '', $controller);
      $arr = explode('/', $controller);
      if(count($arr) > 1){
        $namespace = $arr[0];
        $controller = $arr[1];
      }

      $this->debug->log("Route::setDefaultRoutes() controller:".$controller);
      if($namespace)
        $this->debug->log("Route::setDefaultRoutes() namespace:".$namespace);

      foreach ($this->default_actions as $action) {
        $uri = null;
        if($namespace)
          $uri = '/'.$namespace;
        if(in_array($action, $this->default_need_id_actions)) {
          $uri .= '/'.$controller.'/'.$action.'/ID';
        } else {
          $uri .= '/'.$controller.'/'.$action.'/';
        }

        $this->debug->log("Route::setDefaultRoutes() uri:".$uri);
        if($namespace)
          $this->route[$uri] = array('namespace' => $namespace, 'controller' => $controller.'Controller', 'action' => $action);
        else
          $this->route[$uri] = array('controller' => $controller.'Controller', 'action' => $action);
      }
    }
  }

  public function findRoute($url) {
    $this->debug->log("Route::findRoute() url:".$url);

    foreach ($this->route as $key => $value) {
      $uri = $key;
      $key = str_replace('/', '\/', $key);
      foreach ($this->CONV_STRING_LIST as $k => $v) {
        $key = str_replace($k, $v, $key);
      }
      $pattern = "/".$key."/";
      if (preg_match('/css/', $url)) {
        return;
      }
      $this->debug->log("Route::findRoute() pattern:".$pattern);
      if (preg_match($pattern, $url)) {
        $value['uri'] = $uri;
        $this->debug->log("Route::findRoute() value:".$key.">>>>value:".print_r($value, true));
        return $value;
      }
    }
    return $this->url_not_found;
  }

  public function getFileList($dir) {
    $files = scandir($dir);
    $files = array_filter($files, function ($file) { // 注(1)
      return !in_array($file, array('.', '..'));
    });

    $list = array();
    foreach ($files as $file) {
      $fullpath = rtrim($dir, '/') . '/' . $file; // 注(2)
      if (is_file($fullpath)) {
        $list[] = $fullpath;
      }
      if (is_dir($fullpath)) {
        $list = array_merge($list, $this->getFileList($fullpath));
      }
    }
   
    return $list;
  }

  public function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
  }
}
