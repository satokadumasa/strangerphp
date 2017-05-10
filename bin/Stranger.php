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
    echo "SCAFFOLD_TEMPLATE_PATH:".SCAFFOLD_TEMPLATE_PATH."\n";
    //  テンプレートファイル名作成 
    $template_fileatime = SCAFFOLD_TEMPLATE_PATH . '/models/model_template.tpl';
    //  出力先ファイルを開く  
    $fp = fopen("c:\\folder\\resource.txt", "w");
    //  テンプレートファイル読み込み
    $file_context = file($template_fileatime);
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
        if(strpos($value, '<!----class_name:') || strpos($value, '<!----table_name:')) {
          //  変数展開
          $value = $this->convertKeyToValue($value, $matchs[1], $datas);
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
   *  
   */
  protected function convertKeyToValue($context, $matchs, $datas){
    foreach ($matchs as $v) {
      $keys = explode(':', $v);
      $arr_value = $datas[$keys[1]];
      for($i = 2; $i < count($keys) ; $i++) {
        $arr_value = $arr_value[$keys[$i]];
      }
      $search = '<!----'.$v.'---->';
      $context = str_replace($search, $arr_value, $context);
    }
    return $context;
  }
}
