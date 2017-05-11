<?php
class Stranger {
  protected $error_log = null;
  protected $info_log = null;
  protected $debug = null;
  protected $argv = [];
  protected $table_name = null;
  protected $class_name = null;
  protected $default_database = null;
  public function __construct($argv, $default_database) {
    $this->argv = $argv;
    $this->default_database = $default_database;

    $this->error_log = new Logger('ERROR');
    $this->info_log = new Logger('INFO');
    $this->debug = new Logger('DEBUG');
  }

  //  
  public function execute() {
    /***
    argv = array(
        [0] => ./stranger.php
        [1] => -g
        [2] => scaffold
        [3] => UserInfos
        [4] => name:string
        [5] => address:string
    )
    */
    $this->debug->log("Stranger::exec argv:".print_r($this->argv, true));
    $this->table_name = $this->argv[3];
    $this->class_name = StringUtil::convertTableNameToClassName($this->table_name);

    $this->modelGenerate();
    $this->maigrateGenerate();
  }

  //  generate scaffold
  public function scaffoldGenerate(){
    $this->debug->log("Stranger::scaffoldGenerate()");
  }

  //  destroy scaffold
  public function scaffoldDestroy(){
    $this->debug->log("Stranger::scaffoldDestroy()");
  }

  //  generate controller
  public function controllerGenerate(){
    $this->debug->log("Stranger::controllerGenerate()");
  }

  //  destroy controller
  public function controllerDestroy(){
    $this->debug->log("Stranger::controllerDestroy()");
  }

  //  generate model
  public function modelGenerate(){
    $this->debug->log("Stranger::modelGenerate()");
    //  テンプレートファイル名作成 
    $template_fileatime = SCAFFOLD_TEMPLATE_PATH . '/models/model_template.tpl';
    //  出力先ファイルを開く  
    $fp = fopen(MODEL_PATH ."/" . $this->class_name . "Model.php", "w");
    //  テンプレートファイル読み込み
    $file_context = file($template_fileatime);
    for($i = 0; $i < count($file_context); $i++) {
      $value = $file_context[$i];
      //  展開先の取得
      preg_match_all(
        "<!----(.*)---->",
        $value,
        $matchs
      );

      if (count($matchs[1]) > 0) {
        if(strpos($value, '<!----class_name')) {
          $value = str_replace('<!----class_name---->', $this->class_name, $value);
        }
        if(strpos($value, '<!----table_name')) {
          //  変数展開
          $value = str_replace('<!----table_name---->', $this->table_name, $value);
        }
        if (strpos($value, '<!----columns---->')) {
          $columns_str = "";
          for ($j = 4; $j < count($this->argv); $j++) {
            $columns_str .= $this->generateColumnsStr($this->argv[$j], 'model');
          }
          $value = $columns_str;
        }
      }
      $fwrite = fwrite($fp, $value);
      if ($fwrite === false) {
        fclose($fp);
        return false;
      }
    }
    return fclose($fp);
  }

  //  destroy model
  public function modelDestroy(){
    $this->debug->log("Stranger::modelDestroy()");
  }

  //  generate template
  public function templateGenerate(){
    $this->debug->log("Stranger::templateGenerate()");
  }

  //  destroy template
  public function templateDestroy(){
    $this->debug->log("Stranger::templateDestroy()");
  }

  //  generate maigrate_file  
  public function maigrateGenerate(){
    $this->debug->log("Stranger::maigrateGenerate()");
    //  テンプレートファイル名作成 
    $template_fileatime = SCAFFOLD_TEMPLATE_PATH . '/migrate/migration_template.tpl';
    //  出力先ファイルを開く  
    $now_date = date('YmdHis');
    $class_name = $now_date . 'Create' . $this->class_name;
    $fp = fopen(MIGRATION_PATH .'/' . $class_name . ".php", "w");
    //  テンプレートファイル読み込み
    $file_context = file($template_fileatime);
    for($i = 0; $i < count($file_context); $i++) {
      $value = $file_context[$i];
      //  展開先の取得
      preg_match_all(
        "<!----(.*)---->",
        $value,
        $matchs
      );

      if (count($matchs[1]) > 0) {
        if(strpos($value, '<!----class_name')) {
          $value = str_replace('<!----class_name---->', $class_name, $value);
        }
        if(strpos($value, '<!----table_name')) {
          //  変数展開
          $value = str_replace('<!----table_name---->', $this->table_name, $value);
        }
        if(strpos($value, '<!----pk_name')) {
          //  変数展開
          $value = str_replace('<!----pk_name---->', 'id', $value);
        }
        if (strpos($value, '<!----columns---->')) {
          $columns_str = "";
          for ($j = 4; $j < count($this->argv); $j++) {
            $columns_str .= $this->generateColumnsStr($this->argv[$j], 'sql');
          }
          $value = $columns_str;
        }
      }
      $fwrite = fwrite($fp, $value);
      if ($fwrite === false) {
        fclose($fp);
        return false;
      }
    }
    return fclose($fp);
  }

  //  destroy maigrate_file  
  public function maigrateDestroy(){
    $this->debug->log("Stranger::maigrateDestroy()");
  }

  /**
   *  キー置換メソッド
   *  
   *  context   : 置換対象文字列
   *  matchs    : 置換対象キー文字列
   *  datas     : 置き換えデータ
   */
  protected function convertKeyToValue($context, $matchs, $datas){
    foreach ($matchs as $match) {
      $search = '<!----'.$match.'---->';
      $context = str_replace($search, $datas[$match], $context);
    }
    return $context;
  }

  /**
   *  
   *  
   *  column_define :   
   */
  protected function generateColumnsStr($column_define, $type){
    $columns_str = "";
    $arr = explode(':', $column_define);
    $value = null;
    $datas = array(
        'column_name' => $arr[0],
        'type' => $arr[1],
        'type_str' => $this->convertTypeKeyString($arr[1], (isset($arr[2]) ? $arr[2] : 255), $value),
        'length' => isset($arr[2]) ? $arr[2] : 255,
        'null' => isset($arr[3]) ? $arr[3] : 'false',
        'null_ok' => isset($arr[3]) ? '' : 'NOT NULL',
        'key' => isset($arr[4]) ? $arr[4] : '',
        'default' => isset($arr[5]) ? $arr[4] : 'null',
      );
    if ($type == 'model') {
      $template_fileatime = SCAFFOLD_TEMPLATE_PATH."/models/parts/column.tpl";
    }
    else if ($type == 'sql'){
      $template_fileatime = SCAFFOLD_TEMPLATE_PATH."/migrate/parts/column.tpl";
    }
    $file_context = file($template_fileatime);
    for($i = 0; $i < count($file_context); $i++) {
      $value = $file_context[$i];
      preg_match_all(
        "|<!----(.*)---->|U",
        $value,
        $matchs
      );
      echo "matchs:".print_r($matchs, true)."\n";
      if (count($matchs[1]) > 0){
        $columns_str .= $this->convertKeyToValue($value, $matchs[1], $datas);
      }
      else {
        $columns_str .= $value ;
      }
    }

    return $columns_str;
  }

  protected function convertTypeKeyString($type, $length, $value) {
    switch ($type) {
      case 'INT':
      case 'TINYINT':
      case 'SMALLINT':
      case 'BIGINT':
      case 'FLOAT':
      case 'DOUBLE':
        return $type."($length)";
        break;
      case 'DATE':
      case 'DATETIME':
      case 'TIMESTAMP':
      case 'TIME':
      case 'YEAR':
        return $type;
        break;
      case 'SET':
      case 'ENUM':
        # code...
        break;
        return $type . "(" . $length . ")";
        break;
      default:
        return $type . "(" . $length . ")";
        break;
    }
  }
}
