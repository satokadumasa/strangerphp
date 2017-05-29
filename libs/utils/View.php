<?php
require_once dirname(dirname(dirname(__FILE__))) . "/config/config.php";
/**
 * 
 */
class View {
  protected $layout = "default";
  protected $iterate_viwes = [];
  protected $converte_values = [];

  public function __construct() {
    $this->error_log = new Logger('ERROR');
    $this->info_log = new Logger('INFO');
    $this->debug = new Logger('DEBUG');
  }

  /**
   * Layout template set.
   * 
   * @param string $layout layout filename
   */
  public function setLayout($layout){
    $this->layout = $layout ? $layout : $this->layout;
  }

  /**
   * render view.
   * 
   * @param string $controller_class_name Controller class name
   * @param string $action action name
   * @param array $datas set data
   */
  public function render($controller_class_name, $action, $datas){
    $this->layout = VIEW_TEMPLATE_PATH . "/layout/" . $this->layout . ".tpl";
    $this->framingView($datas , $this->layout, $controller_class_name, $action);
  }

  /**
   * Set data to template
   * 
   * @param array $datas set data
   * @param string $fileatime template file name
   * @param string $controller_class_name Controller class name
   * @param string $action action name
   */
  protected function framingView($datas, $fileatime, $controller_class_name = null, $action = null) {
    $this->debug->log("View::framingView() fileatime:".$fileatime);

    if (file_exists(VIEW_TEMPLATE_PATH . $controller_class_name . '/' . $action . 'tpl')) return false;
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
          /*****
           * 例）
           *   string:Array ( 
           *     [0] => Array ( [0] => [1] => [2] => ) 
           *     [1] => Array ( [0] => UserList:1:name [1] => UserList:2:name [2] => UserList:3:name ) 
           *   ) 
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
            $i = isset($ret) ? $ret : $i;
          }
          else {
            for(; $i < count($file_context); $i++) {
              if (strpos($value, '!----iteratior:') && strpos($value, ':end')) break;
            }
          }
        }
        else if(strpos($value, '!----select_options:')) {
          /**
           *  <!----select_options:UserInfo:pref_id:Prefecture:name---->
           */
          $arr = explode(':', $value);
          preg_match_all(
            "<!----select_options:(.*)---->",
            $value,
            $matchs
          );
          $arr = explode(':', $matchs[1]);
          $this->selectOptionHelper($selects, $option_data, $value_name);
          $i++;
        }
        else if(strpos($value, '!----radiobutton_options:')) {
          /**
           *  <!----radiobutton_options:UserInfo:pref_id:Prefecture:name---->
           */
          $arr = explode(':', $value);
          preg_match_all(
            "<!----radiobutton_options:(.*)---->",
            $value,
            $matchs
          );
          $arr = explode(':', $matchs[1]);
          $this->selectOptionHelper($selects, $option_data, $value_name);
          $i++;
        }
        else if(strpos($value, '!----checkbox_options:')) {
          /**
           *  <!----checkbox_options:UserInfo:pref_id:Prefecture:name---->
           */
          $arr = explode(':', $value);
          preg_match_all(
            "<!----checkbox_options:(.*)---->",
            $value,
            $matchs
          );
          $arr = explode(':', $matchs[1]);
          $this->selectOptionHelper($selects, $option_data, $value_name);
          $i++;
        }
      }
      echo $value;
    }
  }

  /**
   *  SELECT OPTIONタグ生成メソッド
   *  
   *  @param array $selects    : 選択済みID
   *  @param array $option_data : 選択枝データ
   *  @param array $value_name : 選択名
   *  @return none
   */
  protected function selectOption($selects, $option_data, $value_name){
    $options_str = "";
    foreach ($option_data as $key => $value) {
      $selected = (in_array($value['id'], $selects)) ? 'selected' : '';
      $options_str .= "<option value='" . $value['id'] . "' " . $selected . ">" . $value[$value_name] . "</option>\n";
    }
    echo $options_str;
  }

  /**
   *  SELECT OPTIONタグ生成メソッド
   *  
   *  @param array $select    : 選択済みID
   *  @param array $option_data : 選択枝データ
   *  @param array $value_name : 選択名
   *  @param string $column_name : カラム名 
   *  @return none
   */
  protected function radiobutton($select, $option_data, $value_name, $column_name){
    $options_str = "";
    foreach ($option_data as $key => $value) {
      $selected = ($value['id'] = $selects) ? 'checked' : '';
      $options_str .= "<input type='radio' name='".$this->class_name."[".$column_name."]' value='" . $value['id'] . "' " . $selected . ">" . $value[$value_name] . "\n";
    }
    echo $options_str;
  }

  /**
   *  SELECT OPTIONタグ生成メソッド
   *  
   *  @param array $selects    : 選択済みID
   *  @param array $option_data : 選択枝データ
   *  @param array $value_name : 選択名
   *  @param string $column_name : カラム名 
   *  @return none
   */
  protected function checkbox($selects, $option_data, $value_name, $column_name){
    $options_str = "";
    foreach ($option_data as $key => $value) {
      $selected = (in_array($value['id'], $selects)) ? 'checked' : '';
      $options_str .= "<input type='radio' name='".$this->class_name."[".$column_name."]' value='" . $value['id'] . "' " . $selected . ">" . $value[$value_name] . "\n";
    }
    echo $options_str;
  }

  /**
   *  キー置換メソッド
   *  
   *  @param string $context    : 置換対象文字列
   *  @param array $matchs : 置換対象キー文字列
   *  @param array $datas set data
   */
  protected function convertKeyToValue($context, $matchs, $datas){
    foreach ($matchs as $v) {
      $keys = explode(':', $v);
      $arr_value = $datas[$keys[1]];

      if (!$arr_value) return null;

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
   *  @param int $i : Line counter
   *  @param array $datas : 表示用データ  
   *  @param string $file_context : テンプレート内容  
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
        $value = $this->convertKeyToValue($value, $matchs[1], $datas);        
      }
      echo $value;
    }
    return $j;
  }

  public function partialRender($datas){
  }
}