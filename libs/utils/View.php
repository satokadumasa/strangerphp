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
    $this->framingView($datas , $this->layout, $controller_class_name, $action);
  }

  protected function framingView($datas, $fileatime, $controller_class_name = null, $action = null) {
    $file_context = file($fileatime);
    for($i = 0; $i < count($file_context); $i++) {
      $value = $file_context[$i];
      $value = str_replace('¥n', '', $value);
      //  展開先の取得
      preg_match_all(
        "<!----(.*)---->",
        $value,
        $matchs
      );
      if (count($matchs[1]) > 0) {
        if (strpos($value, '!----renderpartial') > 0) {
          //  部分テンプレート読み込み  
          $renderpartial = $matchs[1][0];
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
          if (isset($datas[$keys[1]])) {
            foreach ($datas[$keys[1]] as $data) {
              $ret = $this->viewIterator($i, $data, $file_context);
            }
            $i = $ret;
          }
          else {
            for(; $i < count($file_context); $i++) {
              if (strpos($value, '!----iteratior:') && strpos($value, ':end')) break;
            }
          }
        }
      }
      echo $value;
    }
  }

  /**
   *  キー置換メソッド
   *  
   *  context   : 置換対象文字列
   *  matchs    : 置換対象キー文字列
   *  
   */
  protected function convertKeyToValue($context, $matchs, $datas){
    // echo "datas:".print_r($datas, true)."<br>";
    foreach ($matchs as $v) {
      $keys = explode(':', $v);
      // echo "keys:".print_r($keys, true)."<br>";
      // echo "key:".$keys[1]."<br>";
      $arr_value = $datas[$keys[1]];
      for($i = 2; $i < count($keys) ; $i++) {
        $arr_value = $arr_value[$keys[$i]];
      }
      $search = '<!----'.$v.'---->';
      $context = str_replace($search, $arr_value, $context);
    }
    return $context;
  }

  /**
   *  イテレータ表示機能
   *  
   *  i     : Line counter
   *  datas : 表示用データ  
   *  file_context  : テンプレート内容  
   */
  public function viewIterator($i, $datas, $file_context) {
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
        echo "----value:<br>";
        $value = $this->convertKeyToValue($value, $matchs[1], $datas);        
      }
      echo $value;
    }
    return $j;
  }

  public function partialRender($datas){
  }
}