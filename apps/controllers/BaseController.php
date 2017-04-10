<?php
class BaseController {
  public $error_log;
  public $info_log;
  public $debug;

  protected $dbh = null;
  protected $dbConnect = null;
  protected $request = [];

  public function __construct($default_database, $uri, $url) {
    $this->error_log = new Logger('ERROR');
    $this->info_log = new Logger('INFO');
    $this->debug = new Logger('DEBUG');

    $this->dbConnect = new DbConnect();
    $this->dbConnect->setConnectionInfo($default_database);
    $this->dbh = $this->dbConnect->createConnection();
    // $_POST['Users.dada'] = 'sdasdasd';
    
    $this->setRequest($uri, $url);
  }

  public function setRequest($uri, $url) {
    if (isset($_POST)) {
      foreach ($_POST as $key => $value) {
        // $this->perseKey($key, $this->h($value));
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
    
    for ($i=0; $i < count($urls); $i++) { 
      if(!$uris[$i]) continue;
      if($uris[$i] == $urls[$i]) continue;
      $this->request[mb_strtolower($uris[$i], 'UTF-8')] = $urls[$i];
    }
    $this->debug->log("BaseController::getRequestValues() request".print_r($this->request, true));
  }

  // public function h($str) {
  //   return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
  // }

  /**
   * 指定された階層にある値を設定します。
   *
   * @param   array   $array  配列
   * @param   mixed   $keys   階層
   * @return  array   設定後の配列
   */
  /*
  private function set_lowest($array, $keys, $value) {
      $keys = (array) $keys;
      if (empty($array)) {
          $tmp =& $array;
      } else {
          $tmp =& $array[array_shift($keys)];
      }

      foreach ($keys as $key) {
          if (!isset($tmp[$key])) {
              $tmp[$key] = null;
          }
          $tmp =& $tmp[$key];
      }
      $tmp = $value;
      return $array;
  }
  */
  private function perseKey($key, $value) {
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
}
