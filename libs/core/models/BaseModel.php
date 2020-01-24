<?php
class BaseModel {
  //  DBハンドル
  protected $dbh   = null;
  //  検索条件指定
  protected $conditions = [];
  //  並び順指定
  public $ascs = [];
  protected $keys = null;
  protected $max_rows = 0;
  protected $limit_num = 0;
  protected $offset_num = 0;

  public $error_log;
  public $info_log;
  public $debug;
  public $columns;

  protected $form;

  public $primary_key = 'id';

  public $primary_key_value = null;

  /**
   *  コンストラクタ  
   *
   *  @param PDOObject &$dbh データベース接続ハンドラ
   */
  public function __construct(&$dbh) {
    try{
      if($dbh) $this->setDbh($dbh);

      $this->error_log = new Logger('ERROR');
      $this->info_log = new Logger('INFO');
      $this->debug = new Logger('DEBUG');
      $columns = Spyc::YAMLLoad(SCHEMA_PATH.$this->table_name.".yaml");
      $this->columns = $columns[$this->table_name];
    } catch(Exception $e) {
      $this->debug->log("       BaseModel::__construct() error:" . $e->getMessage());
    }
  }

  /**
   *  テーブル名設定
   *
   *  @param string $table_name テーブル名
   *  @return BaseModel $this
   */
  public function setTableName($table_name) {
    $this->table_name = $table_name;
    return $this;
  }

  /**
   *  テーブル名設定
   *
   *  @param PDOObject &$dbh データベース接続ハンドラ
   *  @return BaseModel $this
   */
  public function setDbh (&$dbh) {
    if ($dbh == null || $dbh == '') throw new Exception("DataBase handle is null.", 1);
    $this->dbh = $dbh;
    return $this;
  }

  // 検索関連

  /**
   *  モデルの検索
   * 
   *  @param string $type 'all':全件 'first':先頭一件
   *  @return array $datas 検索結果データ格納配列
   */
  public function find($type = 'all') {
    $datas = [];
    $primary_keys = [];

    $sql = $this->creteFindSql();

    $column_names = null;

    $this->debug->log("BaseModel::find() sql:".$sql);
    $stmt = $this->dbh->prepare($sql);

    foreach ($this->conditions as $k => $v) {
      $arr = explode('.', $v['column_name']);
      $value = $v['value'];
      $col_name = $arr[count($arr) - 1];

      $column_name = str_replace('.', '_', $v['column_name']);
      $column_name = StringUtil::convertTableNameToClassName($column_name);

      switch ($col_name) {
        case 'created_at':
        case 'modified_at':
          if ($v['operator'] != 'IS NULL') {
            $value = $value ? $value : 'NOW()';
            $stmt->bindParam($column_name, $value, PDO::PARAM_STR);
          }
          break;
        default:
          if ($v['operator'] != 'IS NULL' && $v['operator'] != 'IN') {
            $param_type = is_numeric($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue($column_name, $value, $param_type);
          }
          break;
      }
    }
    $stmt->execute();

    foreach ($stmt->fetchAll() as $row) {
      $data = [];
      if(!$column_names) $column_names = array_keys($row);
      foreach ($column_names as  $column_name) {
        if(is_int($column_name)) continue;
        list($model_name, $col_name) = explode(".", $column_name);
        $data[$model_name][$col_name]= $row[$column_name];
      }

      if (array_search($data[$this->model_name][$this->primary_key], $primary_keys)) continue;

      $primary_keys[] = $data[$this->model_name][$this->primary_key];
      $datas[$data[$this->model_name][$this->primary_key]] = $data;
    }

    if (count($primary_keys) > 0) {
      if ($this->has){
        $this->findHasModelesData($datas, $this->has, $primary_keys);
      }
      if ($this->has_many_and_belongs_to) {
        $this->findHasManyAndBelongsTo($datas, $primary_keys);
      }
    }
    if ($type === 'first') {
      if (!isset($primary_keys[0])) return false;
      $id = $primary_keys[0];
      $datas = $datas[$id];
    }

    return $datas;
  }

  public function find_by_sql($sql) {
    $stmt = $this->dbh->prepare($sql);
    $stmt->execute();
    foreach ($stmt->fetchAll() as $row) {
      $column_names = array_keys($row);
      foreach ($column_names as  $column_name) {
        if(is_int($column_name)) continue;
        list($model_name, $col_name) = explode(".", $column_name);
        $model_name = $model_name ? $model_name : $this->table_name;
        $data[$model_name][$col_name]= $row[$column_name];
      }
    }
    return $data;
  }

  /**
   *  HasOne/HasManyなモデルの検索
   * 
   *  @param array &$data 検索結果データ格納配列
   *  @param array $has 子モデル
   *  @param array $primary_keys 検索親IDs
   *  @retrun none
   */
  public function findHasModelesData(&$datas, $has = null, $primary_keys = null) {
    foreach ($has as $model_name => $options) {
      $model_class_name = $model_name."Model";
      $obj = new $model_class_name($this->dbh);
      $setDatas = $obj->where($options['foreign_key'], 'IN', $primary_keys)->find();
      $this->setHasModelDatas($model_name, $options['foreign_key'],$datas, $setDatas, $primary_keys);
    }
  }

  /**
   *  HasManyAndBelongsToなモデルの検索
   *
   *  @param array &datas 検索結果格データ納配列
   *  @param array $primary_keys 検索親IDs
   *  @retrun none
   */
  public function findHasManyAndBelongsTo(&$datas, $primary_keys = null) 
  {
    foreach ($this->has_many_and_belongs_to as $hasModeName => $options) 
    {
      $belongth_to_model_name = $options['through'];
      $belongth_to_model_class_name = $belongth_to_model_name."Model";
      $belongth_to_model_class_instance = new $belongth_to_model_class_name($this->dbh);
      $setDatas = $belongth_to_model_class_instance->where($options['foreign_key'], 'IN', $primary_keys)->find();

      foreach ($belongth_to_model_class_instance->belongthTo as $model_name => $value) 
      {
        if ($hasModeName === $model_name) 
        {
          foreach ($primary_keys as $primary_key) {
            foreach ($setDatas as $setData) {
              if ($setData[$this->model_name][$this->primary_key] == $primary_key) {
                $datas[$setData[$this->model_name][$this->primary_key]][$this->model_name][$model_name][][$model_name] = $setData[$model_name];
              }
            }
          }
        }
      }
    }
  }

  /**
   *  検索SQL生成処理
   *
   *  @retrun string $sql
   */
  private function creteFindSql(){
    $sql = null;
    $relationship_conds = [];
    $relationship_columuns = [];
    $relationship_sql = [];
    $relationship_columuns[] = " " . $this->model_name . ".*";

    if (isset($this->belongthTo)) {
      foreach ($this->belongthTo as $model_name => $relationship_conditions) {
        $model_class_name = $model_name . "Model";
        $obj = new $model_class_name($this->dbh);
        $table_name = $obj->table_name;
        $join_cond = isset($relationship_conditions['JOIN_COND']) ? $relationship_conditions['JOIN_COND'] : "INNER";

        $relationship_sql = "";
        foreach ($relationship_conditions['conditions'] as $key => $value) {
          $sql_tmp = " " . $key . " = " . $value;
          $relationship_sql .= $relationship_sql ? " AND " .$sql_tmp . " " : " " . $sql_tmp . " ";
        }
        $relationship_sql = " " . $join_cond . " JOIN " . $table_name . " as " . $model_name . " on " . $relationship_sql;

        $relationship_conds[] = $relationship_sql;
        $relationship_columuns[] = ", " . $model_name . ".*";
      }
    }

    $sql = "SELECT ";
    foreach ($relationship_columuns as $value) $sql .= $value;

    $sql .= " FROM " . $this->table_name . " as " . $this->model_name . " ";

    if (is_array($relationship_sql)) foreach ($relationship_sql as $value) $sql .= $value;

    $sql .= " ";

    foreach ($relationship_conds as $value) {
      $sql .= $value;
    }

    $sql .= $this->createCondition();

    if (count($this->ascs) > 0 ) {
      $sql .= ' ORDER BY ';
    }

    if (count($this->ascs) > 0) {
      foreach ($this->ascs as $key => $asc) {
        $sql .= $asc;
      }
    }

    if($this->limit_num > 0) $sql .= " LIMIT " . $this->limit_num ." "; 
    if($this->offset_num > 0) $sql .= " OFFSET " . $this->offset_num . " ";

    return $sql;
  }

  /**
   *  検索条件生成処理
   *
   *  @retrun BaseModel $this
   */
  private function createCondition(){
    $cond = null;
    for($i = 0; $i < count($this->conditions); $i++) {
      $cond_tmp = null;
      $condition = $this->conditions[$i];
      $column_name = str_replace('.', '_', $condition['column_name']);
      $column_name = StringUtil::convertTableNameToClassName($column_name);

      if (is_array($condition['value'])) {
        // $arr = implode(",", $condition['value']);
        $value = "";
        
        $col_arr = explode('.', $condition['column_name']);
        foreach ($condition['value'] as $k => $v) {
          $val = $this->setValue($col_arr[count($col_arr) - 1], $v);
          $value .= $value ? "," . $val : $val;
        }
        $condition['value'] = null;
        $condition['value'] = $value;
      }
      $cond_tmp =  " " . $condition['column_name'];
      if ($condition['operator'] == 'IS NULL') {
        $cond_tmp .= " " . $condition['operator'] . " ";
      }
      else if ($condition['operator'] == 'IN') {
        $cond_tmp .= " " . " IN (".$condition['value'].") ";
      }
      else {
        $cond_tmp .= " " . $condition['operator'];
        $cond_tmp .= " :" . $column_name . " ";
      }
      $cond .= $cond ? " and " . $cond_tmp : $cond_tmp;
    }

    if($cond) $cond = " WHERE " . $cond;
    return $cond;
  }

  /**
   *  検索条件設定処理
   *
   *  @retrun BaseModel $this
   */
  public function where($column_name, $operator, $value) {
    $this->conditions[] = array(
      'column_name' => $column_name, 
      'operator' => $operator, 
      'value' => $value, 
    );
    return $this;
  }

  /**
   *  検索件数設定処理
   *
   *  @retrun BaseModel $this
   */
  public function limit($limit_num) {
    if (!is_int($limit_num)) throw new Exception("Error Processing Request", 1);
    $this->limit_num = $limit_num;
    return $this;
  }

  /**
   *  検索件数上限設定処理
   *
   *  @retrun BaseModel $this
   */
  public function setMaxRows($max_rows) {
    if (!is_int($max_rows)) throw new Exception("Error Processing Request", 1);
    if ($max_rows > 0) $this->max_rows = $max_rows;
    return $this;
  }

  /**
   *  検索開始位置設定処理
   *
   *  @retrun BaseModel $this
   */
  public function offset($offset_num) {
    if (!is_int($offset_num)) throw new Exception("Error Processing Request", 1);
    $this->offset_num = $offset_num;
    return $this;
  }

  /**
   *  検索対象頁設定処理
   *
   *  @retrun BaseModel $this
   */
  public function pagenate($page){
    if (!is_int($page)) throw new Exception("Error Processing Request", 1);
    if ($page > 0 && $this->max_rows > 0) {
      $this->limit_num = $this->max_rows * $page; 
      $this->offset_num = $this->max_rows * ($page - 1); 
    }
    return $this;
  }

  /**
   *  検索並び順（昇順）設定処理
   *
   *  @param string $asc 対象カラム名
   *  @retrun BaseModel $this
   */
  public function asc($asc){
    $this->ascs[] = $this->ascs ? "," . $this->model_name . "." . $asc . " ASC ":  " " . $this->model_name . "." . $asc . " ASC ";
    return $this;
  }

  /**
   *  検索並び順（降順）設定処理
   *
   *  @param string $asc 対象カラム名
   *  @retrun BaseModel $this
   */
  public function desc($asc){
    $this->ascs[] = $this->ascs ? "," . $this->model_name . "." . $asc . " DESC ":  " " . $this->model_name . "." . $asc . " DESC ";
    return $this;
  }

  public function setHasModelDatas($model_name, $foreign_key_name,&$datas, $setDatas, $primary_keys) {
    foreach ($primary_keys as $primary_key) {
      foreach ($setDatas as $setData) {
        if ($setData[$model_name][$foreign_key_name] == $primary_key) {
          $datas[$setData[$model_name][$foreign_key_name]][$this->model_name][$model_name][][$model_name] = $setData[$model_name];
        }
      }
    }
  }

  //  新規登録・更新処理
  /**
   *  新規登録・更新処理 
   * 
   *  @param array $form  フォーム入力値
   */
  public function save($form) {
    try {
      $hssModels = [];
      $hasManyAndBelongsToModels = [];
      $now_date = date('Y-m-d H:i:s');

      $this->validation($form);
      if (
        isset($form[$this->model_name][$this->primary_key]) && 
        (
          $form[$this->model_name][$this->primary_key] != '' || 
          $form[$this->model_name][$this->primary_key] != null
        )
      ) {
        $sql = $this->createModifySql($form[$this->model_name]);  // CASE MODIFY
      }
      else {
        unset($form[$this->model_name][$this->primary_key]);
        $sql = $this->createInsertSql();  // CASE INSERT
      }
      
      if($this->has){
        $hssModels = array_keys($this->has);
      }
      if ($this->has_many_and_belongs_to) {
        $hasManyAndBelongsToModels = array_keys($this->has_many_and_belongs_to);
      }
      $stmt = $this->dbh->prepare($sql);
      foreach ($form[$this->model_name] as $col_name => $value) {
        if ($hssModels && in_array($col_name, $hssModels)) {
          continue;
        }
        if ($hasManyAndBelongsToModels && in_array($col_name, $hasManyAndBelongsToModels)) {
          continue;
        }
        $colum_name = ":".$col_name;
        switch ($col_name) {
          case 'created_at':
          case 'modified_at':
            $now_date = $value ? $value : $now_date;
            $stmt->bindValue($col_name, $now_date, $this->getColumnType($col_name));
            break;
          default:
            $stmt->bindValue($col_name, $value, $this->getColumnType($col_name));
            break;
        }
      }

      $stmt->execute();
      //  従属モデルへのセーブ処理
      $this->primary_key_value = $this->dbh->lastInsertId($this->primary_key);
      if (isset($form[$this->model_name])) {
        foreach ($form[$this->model_name] as $model_name => $value) {
          if ($hssModels && in_array($model_name, $hssModels)) {
            $array_keys = array_keys($value);
            if ($this->is_hash(array_keys($value))) {
              foreach ($value as $num => $val) {
                if ($hssModels && in_array($model_name, $hssModels)) {
                  $this->saveHasModel($model_name, $id, $val);
                }
                else if ($hasManyAndBelongsToModels && in_array($model_name, $hasManyAndBelongsToModels)) {
                  $this->saveHasModel($model_name, $id, $val);
                }
              }
            } else {
              $this->saveHasModel($model_name, $id, $value);
            }
          } 
          else
            continue;
        }
      }
    } catch (Exception $e) {
      throw new Exception($e->getMessage(), 1);
    }
  }

  public function createInsertSql() {
    $col_names = array_keys($this->columns);
    $colums_str = null;
    $values_str = null;
    foreach ($col_names as $col_name) {
      if ($col_name === $this->primary_key) continue;

      $colums_str .= $colums_str ? ", " . $col_name : $col_name;
      if ($col_name === 'created_at' || $col_name === 'modified_at') 
        $values_str .= $values_str ? ", NOW()" : "NOW()";
      else
        $values_str .= $values_str ? ", :".$col_name : ":".$col_name;
    }

    $sql = "INSERT INTO " . $this->table_name . " (" . $colums_str .") VALUES (" . $values_str . ")";
    return $sql;
  }

  public function createModifySql($form) {
    // $col_names = array_keys($this->columns);
    $col_names = array_keys($form);
    $colums_str = null;
    $values_str = null;
    foreach ($col_names as $col_name) {
      if ($col_name == $this->primary_key) continue;
      $colums_str_tmp = $col_name . " = :" . $col_name;
      $colums_str .= $colums_str ? ", " . $colums_str_tmp : $colums_str_tmp;
    }
    return  "UPDATE " . $this->table_name . " SET " . $colums_str ." WHERE " . $this->primary_key . " = :" . $this->primary_key ."";
  }

  public function saveHasModel($model_name, $id, $form) {
    $model_class_name = $model_name."Model";
    $obj = new $model_class_name($this->dbh);
    $form[$this->has[$model_name]['foreign_key']] = $id;
    $f = [];
    $f[$model_name] = $form;
    $obj->save($f);
  }

  //  削除
  public function delete($id){
    //  隷属するモデルを先に検索・削除する。
    if (isset($this->has)) {
      foreach ($this->has as $model_name => $value) {
        $model_class_name = $model_name . "Model" ;
        $obj = new $model_class_name($this->dbh);
        $datas = $obj->where($value['foreign_key'] , '=', $id)->find();
        foreach ($datas as $key => $data) {
          $obj->delete($data[$model_name][$obj->primary_key]);
        }
      }
    }

    $sql = "DELETE FROM " . $this->table_name . " WHERE " . $this->primary_key . "=:" . $this->primary_key;
    $stmt = $this->dbh->prepare($sql);
    $stmt->bindValue($this->primary_key, $id, $this->getColumnType($this->primary_key));
    $stmt->execute();
  }

  //  共通
  protected function setValue($key, $value){
    $type = $this->columns[$key]['type'];
    if (is_array($value)) {
      if ($type == 'SET') {
        $val_tmp = '';
        foreach ($value as $key => $val) {
          $val = mysqli_escape_string($val);
          $val .= htmlspecialchars($val, ENT_QUOTES);
          $val_tmp .= $val_tmp ? $val_tmp : ", " . $val_tmp;
        }
        $value = $val_tmp;
      }
    }

    $value = $value == 'string' ? 'varchar' : $value;

    switch ($type) {
      case 'int':
      case 'tinyint':
      case 'smallint':
      case 'bigint':
      case 'float':
      case 'double':
        return $value;
      case 'set':
        return "'" . $value . "'";
      default:
        return "'".$value."'";
        break;
    }
  }

  public function getColumnType($col_name) {
    $type = $this->columns[$col_name]['type'];
    switch ($type) {
      case 'int':
      case 'tinyint':
      case 'smallint':
      case 'bigint':
        return PDO::PARAM_INT;
        break;
      case 'float':
      case 'double':
        return PDO::PARAM_INT;
        break;
      case 'bool':
        return PDO::PARAM_BOOL;
        break;
      case 'set':
      default:
        return PDO::PARAM_STR;
        break;
    }
  }

  public function getColumns() {
    return array_keys($this->columns);
  }

  public function validation($form) {
    // $this->form
  }

  /**
   *  Array/Hash判定メソッド 
   *
   *  @param Object $data 判定対象オブジェクト
   *  @return boolean
   */
  public function is_hash($data) {
    if (is_array($data)){
      foreach ($data as $key => $value) {
        if (!is_numeric($value)) continue;
        else return true;
      }
      return false;
    }
    return false;
  }

  public function createForm() {
    $keys = array_keys($this->columns);
    $form = [];
    foreach ($keys as $key => $value) {
      $form[$this->model_name][$value] = '';
    }
    return $form;
  }
}