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
    $this->debug->log("View::render() layout:" . $this->layout);
    $this->framingView($datas , $this->layout, $controller_class_name, $action);
  }

  protected function framingView($datas, $fileatime, $controller_class_name = null, $action = null) {
    echo "TPL:" . $fileatime ."<br>";
    $file_context = file($fileatime);
    $this->debug->log("View::render() datas:" . print_r($datas, true));
    $this->debug->log("View::render() file_context:" . print_r($file_context, true));
    foreach ($file_context as $key => $value) {
      $this->debug->log("View::render() value:" . $value);
      if (strpos($value, '!----renderpartial') > 0) {
        $value = str_replace('¥n', '', $value);
        $value = str_replace('<!----', '', $value);
        $value = str_replace('---->', '', $value);
        if (strpos($value, 'CONTROLLER/ACTION') > 0) {
          if (isset($action)) {
            $value = str_replace('ACTION', $action, $value);
          }
          if (isset($controller_class_name)) {
            $value = str_replace('CONTROLLER', $controller_class_name, $value);
          }
        }
        echo "VALUE:" . $value . "<br>";
        $arr = explode(':', $value);
        
        $partial_tpl_filename = VIEW_TEMPLATE_PATH . $arr[1] . ".tpl";
        $this->framingView($datas , $partial_tpl_filename);
      }
      echo $value;
    }

  }

  public function partialRender($datas){
  }
}