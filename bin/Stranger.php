<?php
class Stranger {
  protected $error_log = null;
  protected $info_log = null;
  protected $debug = null;
  protected $argv = [];
  protected $table_name = null;
  protected $class_name = null;

  public function __construct($argv) {
    $this->argv = $argv;
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
            $columns_str .= $this->generateColumnsStr($this->argv[$j]);
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
  protected function generateColumnsStr($column_define){
    $columns_str = "";
    $arr = explode(':', $column_define);

    $datas = array(
        'column_name' => $arr[0],
        'type' => $arr[1],
        'length' => isset($arr[2]) ? $arr[2] : 255,
        'null' => isset($arr[3]) ? $arr[3] : 'false',
        'key' => isset($arr[4]) ? $arr[4] : '',
        'default' => isset($arr[5]) ? $arr[4] : 'null',
      );

    $template_fileatime = SCAFFOLD_TEMPLATE_PATH."/models/parts/column.tpl";
    $file_context = file($template_fileatime);
    for($i = 0; $i < count($file_context); $i++) {
      $value = $file_context[$i];
      preg_match_all(
        "<!----(.*)---->",
        $value,
        $matchs
      );
      if (count($matchs[1]) > 0){
        $columns_str .= $this->convertKeyToValue($value, $matchs[1], $datas);
      }
      else {
        $columns_str .= $value ;
      }
    }

    return $columns_str;
  }
}
