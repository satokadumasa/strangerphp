<?php
require_once dirname(dirname(dirname(__FILE__))) . "/config/config.php";

class View {
  protected $layout = "default";
  protected $iterate_viwes = [];
  protected $converte_values = [];

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
    $this->debug->log("View::framingView() datas:".print_r($datas, true));
    $file_context = file($fileatime);
    // $this->debug->log("View::render() datas:" . print_r($datas, true));
    // $this->debug->log("View::render() file_context:" . print_r($file_context, true));

    // foreach ($file_context as $key => $value) {
    for($i = 0; $i < count($file_context); $i++) {
      // $this->debug->log("View::framingView() value:" . $value);
      $value = $file_context[$i];
      $value = str_replace('¥n', '', $value);
      //  展開先の取得
      preg_match_all(
        "<!----(.*)---->",
        $value,
        $matchs
      );
      if (count($matchs[1]) > 0) {
        $this->debug->log("View::framingView() matchs:".print_r($matchs, true));
        if (strpos($value, '!----renderpartial') > 0) {
          //  部分テンプレート読み込み  
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
        else if(strpos($value, '<!----value:')) {
          //  変数展開
          /*
          string:Array ( 
            [0] => Array ( [0] => [1] => [2] => ) 
            [1] => Array ( [0] => UserList:1:name [1] => UserList:2:name [2] => UserList:3:name ) 
          ) 
          */
          $value = $this->convertKeyToValue($value, $matchs[1], $datas);
          $this->debug->log("View::framingView() value:".$value);

        }
        else if (strpos($value, '<!----iteratior:') && strpos($value, ':start')) {
          //  イテレーター
          $keys = explode(':', $matchs[1][0]);
          /**
           *  イレーター処理メソッド呼び出し
           *
           *  i : カウンター
           *  datas[keys[1]]  : セットする変数（多次元連想配列）
           *  file_context   :  テンプレートの内容
           */
          foreach ($datas[$keys[1]] as $data) {
            $ret = $this->viewIterator($i, $data, $file_context);
          }
          echo "View::framingView() iteratior:ret".$ret."<br>";
          $i = $ret;
        }
      }
      echo $value;
    }
  }

  /**
   *  context   : 置換対象文字列
   *  
   */
  protected function convertKeyToValue($context, $matchs, $datas){
    $this->debug->log("View::convertKeyToValue() datas:".print_r($datas, true));
    foreach ($matchs as $v) {
      $keys = explode(':', $v);
      $this->debug->log("View::convertKeyToValue() v:".print_r($v, true));
      $arr_value = $datas[$keys[1]];
      for($i = 2; $i < count($keys) ; $i++) {
        $this->debug->log("View::convertKeyToValue() keys[$i]:".print_r($keys[$i], true));
        $arr_value = $arr_value[$keys[$i]];
      }
      $search = '<!----'.$v.'---->';
      $context = str_replace($search, $arr_value, $context);
    }
    return $context;
  }
  public function viewIterator($i, $datas, $file_context) {
    $this->debug->log("View::viewIterator() datas:".print_r($datas, true));
    $this->debug->log("View::viewIterator() file_context[$i]:".$file_context[$i]);
    $iterator = [];
    $keys = [];
    $j = 0;
    for($j = ($i + 1); $j < count($file_context); $j++) {
      $value = $file_context[$j];
      $value = str_replace('¥n', '', $value);
      preg_match_all(
        "<!----(.*)---->",
        $value,
        $matchs
      );
      if (strpos($value, '<!----iteratior:') && strpos($value, ':start')) {
        //  イテレーター再帰呼び出し
        $keys = explode(':', $matchs[1][0]);
        if (isset($datas[$keys[1]][$keys[2]])) {
          foreach ($datas[$keys[1]][$keys[2]] as $data) {
            $ret = $this->viewIterator($j, $data, $file_context);
          }
          $j = $ret;
        }
        else {
          for(; $j < count($file_context); $j++) {
            if (strpos($value, '<!----iteratior:') && strpos($value, ':end')) {
              break;
            }
          }
        }

        continue;
      }
      else if (strpos($value, '<!----iteratior:') && strpos($value, ':end')) {
        //  イテレーター自身のイテレーション準備の完了
        break;
      }
      else if (strpos($value, '<!----value:')){
        $this->debug->log("View::viewIterator() matchs[1]:".print_r($matchs[1], true));
        $value = $this->convertKeyToValue($value, $matchs[1], $datas);        
      }
      echo $value;
    }
    return $j;
  }

  public function partialRender($datas){
  }
}