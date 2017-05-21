<?php
class Stranger {
  protected $error_log = null;
  protected $info_log = null;
  protected $debug = null;
  protected $argv = [];
  protected $table_name = null;
  protected $class_name = null;
  protected $default_database = null;
  protected $dbh = null;

  public function __construct($argv, $default_database) {
    $this->argv = $argv;
    $this->default_database = $default_database;

    $this->dbConnect = new DbConnect();
    $this->dbConnect->setConnectionInfo($default_database);
    $this->dbh = $this->dbConnect->createConnection();

    $this->error_log = new Logger('ERROR');
    $this->info_log = new Logger('INFO');
    $this->debug = new Logger('DEBUG');
  }

  //  
  /**
   *  stranger command execute method
   *
   */
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
    if ($this->argv[1] == '-g') {
      $this->executeGenerates();
    }
    if ($this->argv[1] == 'migrate'){
        $this->execMigration();
        exit();
    }
    else if ($this->argv[1] == '-d') {
      $this->executeDestroies();
    }
    else if ($this->argv[1] == 'db:migrate') {
      $arr = explode(':', $this->argv[1]);
    }
    else if ($this->argv[1] == 'migrate'){
        $this->execMigration();
    }
  }

  /**
   *  テンプレート、モデル、コントローラー生成 
   */
  public function executeGenerates(){
    if ($this->argv[1] == '-g') {
      if ($this->argv[2] == 'scaffold') {
        $this->scaffoldGenerate();
      }
      else if ($this->argv[2] == 'controller'){
        $this->controllerGenerate();
      }
      else if ($this->argv[2] == 'model'){
        $this->modelGenerate();
      }
      else if ($this->argv[2] == 'model' || $this->argv[2] == 'column'){
        $this->maigrateGenerate();
      }
      else {
        echo "Please specify the correct parameter.\n";
      }
    }
  }

  /**
   *  テンプレート、モデル、コントローラー削除
   */
  public function executeDestroies(){
    if ($this->argv[1] == '-d') {
      if ($this->argv[2] == 'scaffold') {
        scaffoldDestroy();
      }
      else if ($this->argv[2] == 'controller'){
        $this->controllerDestroy();
      }
      else if ($this->argv[2] == 'model'){
        $this->modelDestroy();
      }
      else if ($this->argv[2] == 'model' || $this->argv[2] == 'column'){
        $this->maigrateGenerate();
      }
      else {
        echo "Please specify the correct parameter.\n";
      }
    }
    else {
      echo "Please specify the correct parameter.\n";
    }
  }

  //  generate scaffold
  /**
   *  キャッフォルドファイル一括生成メソッド 
   */
  public function scaffoldGenerate(){
    $this->debug->log("Stranger::scaffoldGenerate()");
    $this->controllerGenerate();
    $this->modelGenerate();
    $this->maigrateGenerate();
    $this->templateGenerate();
  }

  //  destroy scaffold
  /**
   *  キャッフォルドファイル一括削除メソッド 
   */
  public function scaffoldDestroy(){
    $this->debug->log("Stranger::scaffoldDestroy()");
    $this->controllerGenerate();
    $this->modelGenerate();
    $this->maigrateGenerate();
  }

  public function execMigration(){
    $migration_files = $this->getFileList(MIGRATION_PATH);
    foreach ($migration_files as $key => $value) {
      if (!strpos($value, '.php')) continue;
      require_once $value;
      $arr = explode('/', $value);
      $migration_file = $arr[count($arr) - 1];
      $migration_file = str_replace('.php', '', $migration_file);
      $migration = new $migration_file($this->default_database);
      $migration->up();
    }
  }

  public function getFileList($dir) {
    $files = scandir($dir);
    $files = array_filter($files, function ($file) {
      return !in_array($file, array('.', '..'));
    });

    $list = array();
    foreach ($files as $file) {
      $fullpath = rtrim($dir, '/') . '/' . $file;
      if (is_file($fullpath)) {
        $list[] = $fullpath;
      }
      if (is_dir($fullpath)) {
        $list = array_merge($list, $this->getFileList($fullpath));
      }
    }
   
    return $list;
  }

  //  generate controller
  /**
   *  コントローラー生成メソッド 
   */
  public function controllerGenerate(){
    $template_fileatime = SCAFFOLD_TEMPLATE_PATH . 'controllers/controller_template.tpl';
    //  出力先ファイルを開く  
    $out_put_filename =  CONTROLLER_PATH ."/" . $this->class_name . "Controller.php";
    $fp = fopen($out_put_filename, "w");
    $return = $this->applyTemplate($template_fileatime, $fp, $this->class_name, null, null);
  }

  //  destroy controller
  /**
   *  コントローラー削除メソッド 
   */
  public function controllerDestroy(){
    $this->debug->log("Stranger::controllerDestroy()");
  }

  //  generate model
  /**
   *  モデル生成メソッド 
   */
  public function modelGenerate(){
    $this->debug->log("Stranger::modelGenerate()");
    //  テンプレートファイル名作成 
    $template_fileatime = SCAFFOLD_TEMPLATE_PATH . 'models/model_template.tpl';
    //  出力先ファイルを開く  
    $out_put_filename =  MODEL_PATH ."/" . $this->class_name . "Model.php";
    $fp = fopen($out_put_filename, "w");
    $return = $this->applyTemplate($template_fileatime, $fp, $this->class_name, null);
    fclose($fp);
    if ($return === false) {
      return false;
    }
  }

  //  destroy model
  /**
   *  モデル削除メソッド 
   */
  public function modelDestroy(){
    $this->debug->log("Stranger::modelDestroy()");
  }

  //  generate template
  /**
   *  Viewテンプレート生成メソッド 
   */
  public function templateGenerate(){
    echo "create view templates.\n";
    $this->debug->log("Stranger::templateGenerate()");
    $view_template_folder = VIEW_TEMPLATE_PATH . $this->class_name . '/';
    if (!file_exists($view_template_folder)) {
      if(!mkdir($view_template_folder)) return false;
    }
    //  index 
    echo "create index template.\n";
    $index = $view_template_folder . 'index.tpl';
    $template_fileatime = SCAFFOLD_TEMPLATE_PATH . '/views/index.tpl';
    $method = 'index';
    $this->createViewTemplate($template_fileatime, $index, $method);

    //  show
    echo "create show template.\n";
    $show = $view_template_folder . 'show.tpl';
    $template_fileatime = SCAFFOLD_TEMPLATE_PATH . '/views/detail.tpl';
    $method = 'detail';
    $this->createViewTemplate($template_fileatime, $show, $method);
    //  create
    echo "create create template.\n";
    $create = $view_template_folder . 'create.tpl';
    $template_fileatime = SCAFFOLD_TEMPLATE_PATH . '/views/form_exterior.tpl';
    $method = 'create';
    $this->createViewTemplate($template_fileatime, $create, $method);
    //  edit
    echo "create edit template.\n";
    $edit = $view_template_folder . 'edit.tpl';
    $template_fileatime = SCAFFOLD_TEMPLATE_PATH . '/views/form_exterior.tpl';
    $method = 'edit';
    $this->createViewTemplate($template_fileatime, $edit, $method);
  }

  public function createViewTemplate($template_fileatime, $view_template, $method) {
    $fp = fopen($view_template, "w");
    $return = $this->applyTemplate($template_fileatime, $fp, $this->class_name, null, $method);
    fclose($fp);
    if ($return === false) {
      return false;
    }
  }

  //  destroy template
  /**
   *  Viewテンプレート削除メソッド 
   */
  public function templateDestroy(){
    $this->debug->log("Stranger::templateDestroy()");
  }

  //  generate maigrate_file  
  /**
   *  マイグレーションファイル生成メソッド
   */
  public function maigrateGenerate(){
    $this->debug->log("Stranger::maigrateGenerate()");
    //  テンプレートファイル名作成 
    $template_fileatime = SCAFFOLD_TEMPLATE_PATH . '/migrate/migration_template.tpl';
    $now_date = date('YmdHis');
    $create = null;
    if ($this->argv[2] == 'scaffold' || $this->argv[2] == 'model') {
      $create = $this->argv[1] == '-g' ? 'CreateTable' : 'DropTable';
    } else if ($this->argv[2] == 'column') {
      $create = $this->argv[1] == '-g' ? 'AddColumn' : 'DropColumn';
    }
    $migration_class_name = 'Migrate' . $now_date . $create . $this->class_name;
    $out_put_filename = MIGRATION_PATH .'/' . $migration_class_name . ".php";
    //  出力先ファイルを開く
    $fp = fopen($out_put_filename, "w");
    $return = $this->applyTemplate($template_fileatime, $fp, $this->class_name, $migration_class_name);
    fclose($fp);
    if ($return === false) {
      return false;
    }
  }

  //  destroy maigrate_file 
  /**
   *  マイグレーションファイル削除メソッド
   */ 
  public function maigrateDestroy(){
    $this->debug->log("Stranger::maigrateDestroy()");
  }

  /**
   *  テンプレート適用メソッド
   *
   *  template_fileatime : テンプレートファイル名  
   *  fp : 出力先ファイルポインター 
   *  class_name : クラス名
   *  migration_class_name : マイグレーションファイル　クラス名 
   *  method_name : コントローラメソッド名
   */
  public function applyTemplate($template_fileatime, &$fp, $class_name, $migration_class_name = null, $method_name = null) {
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
        if (strpos($value, '<!----migration_class_name---->')) {
          $value = str_replace('<!----migration_class_name---->', $migration_class_name, $value);
        }
        if (strpos($value, '<!----class_name---->')) {
          $value = str_replace('<!----class_name---->', $this->class_name, $value);
        }
        if (strpos($value, '<!----action_name---->')) {
          $value = str_replace('<!----action_name---->', $method_name, $value);
        }
        if (strpos($value, '<!----table_name---->')) {
          $value = str_replace('<!----table_name---->', $this->table_name, $value);
        }
        if (strpos($value, '<!----pk_name---->')) {
          $value = str_replace('<!----pk_name---->', 'id', $value);
        }
        if (strpos($value, '<!----method_name---->')) {
          $value = str_replace('<!----method_name---->', $method_name, $value);
        }
        if (strpos($value, '<!----form_columns---->')) {
          $columns_str = "";
          for ($j = 4; $j < count($this->argv); $j++) {
            $columns_str .= $this->generateColumnsStr($this->argv[$j], 'form');
          }
          $value = $columns_str;
          $this->debug->log("Stranger::applyTemplate() form_columns:".$value);
        }
        if (strpos($value, '<!----columns---->')) {
          $columns_str = "";
          for ($j = 4; $j < count($this->argv); $j++) {
            $this->debug->log("Stranger::applyTemplate() argv:".print_r($this->argv[$j], true));
            $columns_str .= $this->generateColumnsStr($this->argv[$j], 'sql');
          }
          $value = $columns_str;
        }
        if (strpos($value, '<!----model_columns---->')){
          $columns_str = "";
          for ($j = 4; $j < count($this->argv); $j++) {
            $columns_str .= $this->generateColumnsStr($this->argv[$j], 'model');
          }
          $value = $columns_str;
        }

        if (strpos($value, '<!----up_template---->')) {
          $this->debug->log("Stranger::applyTemplate() value:".$value);
          if ($this->argv[2] == 'scaffold' || $this->argv[2] == 'model') {
            $template_fileatime = SCAFFOLD_TEMPLATE_PATH . '/migrate/parts/create_table.tpl';
            $this->applyTemplate($template_fileatime, $fp, $this->class_name);
          }
          else if ($this->argv[2] == 'column'){
            $type = $this->argv == '-g' ? 'add_col' : 'drop_col';
            $columns_str = null;
            for ($j = 4; $j < count($this->argv); $j++) {
              $columns_str .= $this->generateColumnsStr($this->argv[$j], $type);
            }
            $fwrite = fwrite($fp, $columns_str);
            if ($fwrite === false) {
              return false;
            }
          }
          continue;
        }
        if (strpos($value, '<!----down_template---->')) {
          if ($this->argv[2] == 'scaffold' || $this->argv[2] == 'model') {
            $template_fileatime = SCAFFOLD_TEMPLATE_PATH . '/migrate/parts/drop_table.tpl';
            $this->applyTemplate($template_fileatime, $fp, $class_name);
          }
          else if ($this->argv[2] == 'column'){
            $type = $this->argv == '-g' ? 'drop_col' : 'add_col';
            $columns_str = null;
            for ($j = 4; $j < count($this->argv); $j++) {
              $columns_str .= $this->generateColumnsStr($this->argv[$j], $type);
            }
            $fwrite = fwrite($fp, $columns_str);
            if ($fwrite === false) {
              return false;
            }
          }
          continue;
        }
        if (strpos($value, '<!----controller_method---->')) {
          if ($this->argv[2] == 'scaffold') {
            echo "create scaffold controller.\n";
            $template_fileatime = SCAFFOLD_TEMPLATE_PATH . '/controllers/parts/scaffold_controller_methods_template.tpl';
            $this->applyTemplate($template_fileatime, $fp, $class_name, null, null);
          }
          else {
            echo "create non-scaffold controller.\n";
            for ($j = 4; $j < count($this->argv); $j++) {
              $template_fileatime = SCAFFOLD_TEMPLATE_PATH . '/controllers/parts/method_template.tpl';
              $this->applyTemplate($template_fileatime, $fp, $class_name, null, $this->argv[$j]);
            }
          }
          continue;
        }
        if (strpos($value, '<!----detail_columns---->')) {
          $value = $this->geterateColumnString($this->argv);
          $fwrite = fwrite($fp, $value);
          if ($fwrite === false) {
            return false;
          }
        }
        if (strpos($value, '<!----details---->')) {
          //  詳細画面テンプレート挿入
          echo "insert detail template.\n";
          $template_fileatime = SCAFFOLD_TEMPLATE_PATH . '/views/detail.tpl';
          $this->applyTemplate($template_fileatime, $fp, $class_name, null, null);
          $fwrite = fwrite($fp, $value);
          if ($fwrite === false) {
            return false;
          }
          continue;
        }
        if (strpos($value, '<!----columun_name---->')) {
          echo "covert [columun_name].\n";
          for ($j = 4; $j < count($this->argv); $j++) {
            $columns_str .= $this->convertKeyToValue($value, $matchs[0], $this->argv);
          }
          $value = $columns_str;
          $fwrite = fwrite($fp, $value);
          if ($fwrite === false) {
            return false;
          }
          continue;
        }
      }
      $fwrite = fwrite($fp, $value);
      if ($fwrite === false) {
        return false;
      }
    }
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
      if ($match == 'table_name') {
        $context = str_replace($search, $this->table_name, $context);
      } else {
        $context = str_replace($search, $datas[$match], $context);
      }
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
    $this->debug->log("Stranger::generateColumnsStr() column_define:$column_define");
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
        'model_name' => $this->class_name,
      );
    $this->debug->log("Stranger::generateColumnsStr() datas:".print_r($datas, true));
    if ($type == 'model') {
      $template_fileatime = SCAFFOLD_TEMPLATE_PATH."/models/parts/column.tpl";
    }
    else if ($type == 'sql'){
      $template_fileatime = SCAFFOLD_TEMPLATE_PATH."/migrate/parts/column.tpl";
    }
    else if ($type == 'add_col') {
      $template_fileatime = SCAFFOLD_TEMPLATE_PATH . '/migrate/parts/add_column.tpl';
    }
    else if ($type == 'drop_col') {
      $template_fileatime = SCAFFOLD_TEMPLATE_PATH . '/migrate/parts/drop_column.tpl';
    }
    else if ($type == 'view') {
      $template_fileatime = SCAFFOLD_TEMPLATE_PATH . '/views/parts/column.tpl';
    }
    else if ($type == 'form') {
      $template_fileatime = SCAFFOLD_TEMPLATE_PATH . '/views/parts/form/column.tpl';
    }
    $file_context = file($template_fileatime);
    for($i = 0; $i < count($file_context); $i++) {
      $value = $file_context[$i];
      preg_match_all(
        "|<!----(.*)---->|U",
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

  /**
   * 
   *
   * @param $argv 
   */
  protected function geterateColumnString($argv) {
    for ($i = 4; $i < count($argv); $i++) {
      $arr = explode(':', $argv[$i]);
      $value = null;

      $datas = array(
          'column_name' => $arr[0],
          'type' => $arr[1],
          'length' => isset($arr[2]) ? $arr[2] : 255,
          'null' => isset($arr[3]) ? $arr[3] : 'false',
          'null_ok' => isset($arr[3]) ? '' : 'NOT NULL',
          'key' => isset($arr[4]) ? $arr[4] : '',
          'default' => isset($arr[5]) ? $arr[4] : 'null',
        );
      $column_string = null;
      $column_string .= "  <div>\n";
      $column_string .= "    <div>\n";
      $column_string .= "      <!----" . $datas['column_name'] . "---->\n";
      $column_string .= "    </div>\n";
      $column_string .= "    <div>\n";
      $column_string .= "      <!----value:" . $this->class_name . ":" . $datas['column_name'] . "---->\n";
      $column_string .= "    </div>\n";
      $column_string .= "  </div>\n";

      return $column_string;
    }
  }

  protected function convertTypeKeyString($type, $length, $value) {
    switch ($type) {
      case 'int':
        return $type."(8)";
        break;
      case 'tinyint':
        return $type."(1)";
        break;
      case 'smallint':
        return $type."(3)";
        break;
      case 'bigint':
      case 'float':
      case 'double':
        return $type."($length)";
        break;
      case 'date':
      case 'datetime':
      case 'timestamp':
      case 'time':
      case 'year':
        return $type;
        break;
      case 'set':
      case 'enum':
        return $type . "(" . $length . ")";
        break;
      case 'string':
        return  "varchar(" . $length . ")";
        break;
      case 'text':
        return $type;
      default:
        return $type . "(" . $length . ")";
        break;
    }
  }
}
