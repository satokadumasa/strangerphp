<?php
require_once dirname(dirname(dirname(__FILE__))) . "/config/config.php";

class View {
  protected $layout = "default";

  public function __construct() {
    $this->error_log = new Logger('ERROR');
    $this->info_log = new Logger('INFO');
    $this->debug = new Logger('DEBUG');
  }

  public function setLayout($layout){
    $this->layout = $layout ? $layout : $this->layout;
  }

  public function render($controller_class_name, $action, $datas){
    $this->layout = VIEW_TEMPLATE_PATH . "/Layout/" . $this->layout . ".tpl";
    // $this->debug->log("View::render() layout:" . $this->layout);
    $this->framingView($datas , $this->layout, $controller_class_name, $action);
  }

  protected function framingView($datas, $fileatime, $controller_class_name = null, $action = null) {
    echo "TPL:" . $fileatime ."<br>";
    $file_context = file($fileatime);
    // $this->debug->log("View::render() datas:" . print_r($datas, true));
    // $this->debug->log("View::render() file_context:" . print_r($file_context, true));

    // foreach ($file_context as $key => $value) {
    for($i = 0; $i < count($file_context); $i++) {
      // $this->debug->log("View::framingView() value:" . $value);
      $value = $file_context[$i];
      $value = str_replace('¥n', '', $value);
      echo "VALUE:" . $value . "<br>";
      preg_match_all(
        "<!----(.*)---->",
        $value,
        $matchs
      );
      if (count($matchs[1]) > 0) {
        $this->debug->log("View::framingView() matchs:".print_r($matchs, true));
        if (strpos($value, '!----renderpartial') > 0) {
          $renderpartial = $matchs[1][0];
          $this->debug->log("View::framingView() renderpartial:".$matchs[1][0]);
          if (strpos($value, 'CONTROLLER/ACTION') > 0) {
            if (isset($action)) {
              $renderpartial = str_replace('ACTION', $action, $renderpartial);
            }
            if (isset($controller_class_name)) {
              $renderpartial = str_replace('CONTROLLER', $controller_class_name, $renderpartial);
            }
          }

          $arr = explode(':', $renderpartial);
          
          $partial_tpl_filename = VIEW_TEMPLATE_PATH . $arr[1] . ".tpl";
          $this->framingView($datas , $partial_tpl_filename);
        }
        else {
          foreach ($matchs[1] as $key => $value) {
            $arr = explode(':', $value);
            if ($arr[0] == 'iteratior' && $arr[2] == 'start') {
              $i = $this->viewIterator($i, $arr[1], $file_context);
            }
            if ($arr[0] == 'iteratior' && $arr[2] == 'end') {
              break;
            }

            $value = $file_context[$j];
          }
        }
      }
      /*
      string:Array ( 
        [0] => Array ( [0] => [1] => [2] => ) 
        [1] => Array ( [0] => UserList:1:name [1] => UserList:2:name [2] => UserList:3:name ) 
      ) 
      */
      echo $value;
    }
  }

  public function viewIterator($i, $file_context) {
    for($j = $i; ($j + 1) < count($file_context); $j++) {
      $arr = explode(':', $value);
      if ($arr[0] == 'iteratior' && $arr[2] == 'start') {
        $i = $this->viewIterator($i, $arr[1], $file_context);
      }
      if ($arr[0] == 'iteratior' && $arr[2] == 'end') {
        break;
      }

      $value = $file_context[$j];
    }
    return $j;
  }

  public function partialRender($datas){
  }
}