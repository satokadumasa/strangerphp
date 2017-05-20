<?php
class BaseModel {
  //  DBハンドル
  protected $dbh   = null;
  //  検索条件指定
  protected $conditions = [];
  //  並び順指定
  protected $ascs = null;
  protected $descs = null;
  protected $keys = null;
  protected $max_rows = 0;
  protected $limit_num = 0;
  protected $offset_num = 0;

  public $error_log;
  public $info_log;
  public $debug;

  protected $form;

  public $primary_key = 'id';

  public function __construct(&$dbh) {
    if($dbh) $this->setDbh($dbh);

    $this->error_log = new Logger('ERROR');
    $this->info_log = new Logger('INFO');
    $this->debug = new Logger('DEBUG');
  }

  public function setTableName($table_name) {
    $this->table_name = $table_name;
  }

  public function setDbh (&$dbh) {
    if ($dbh == null || $dbh == '') throw new Exception("DataBase handle is null.", 1);
    $this->dbh = $dbh;
  }

  // 検索関連
  public function find($type = 'all') {
    $datas = [];
    $primary_keys = [];

    $sql = $this->creteFindSql();

    $column_names = null;

    $this->debug->log("BaseModel::find() sql:" . $sql);
    foreach ($this->dbh->query($sql) as $row) {
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
      $id = $primary_keys[0];
      $this->debug->log("datas:".print_r($datas, true));
      $datas = $datas[$id];
    }
    return $datas;
  }

  public function findHasModelesData(&$datas, $has = null, $primary_keys = null) {
    foreach ($has as $model_name => $options) {
      $model_class_name = $model_name."Model";
      $obj = new $model_class_name($this->dbh);
      $setDatas = $obj->where($options['foreign_key'], 'IN', $primary_keys)->find();
      $this->setHasModelDatas($model_name, $options['foreign_key'],$datas, $setDatas, $primary_keys);
    }
  }

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

    if($this->limit_num > 0) $sql .= " LIMIT " . $this->limit_num ." "; 
    if($this->offset_num > 0) $sql .= " OFFSET " . $this->offset_num . " ";

    return $sql;
  }

  private function createCondition(){
    $cond = null;
    foreach ($this->conditions as $condition) {
      $cond_tmp = null;
      if (is_array($condition['value'])) {
        $value = implode(",", $condition['value']);
        $condition['value'] = null;
        $condition['value'] = " (" . $value .") ";
      }
      $cond_tmp =  " " . $condition['column_name'];
      $cond_tmp .= " " . $condition['operator'];
      $cond_tmp .= " " . $condition['value'] . " ";
      $cond .= $cond ? " and " . $cond_tmp : $cond_tmp;
    }

    if($cond) $cond = " WHERE " . $cond;

    return $cond;
  }

  public function where($column_name, $operator, $value) {
    $this->conditions[] = array(
      'column_name' => $column_name, 
      'operator' => $operator, 
      'value' => $value, 
    );
    return $this;
  }

  public function limit($limit_num) {
    if (!is_int($limit_num)) throw new Exception("Error Processing Request", 1);
    $this->limit_num = $limit_num;
    return $this;
  }

  public function setMaxRows($max_rows) {
    if (!is_int($max_rows)) throw new Exception("Error Processing Request", 1);
    if ($max_rows > 0) $this->max_rows = $max_rows;
    return $this;
  }

  public function offset($offset_num) {
    if (!is_int($offset_num)) throw new Exception("Error Processing Request", 1);
    $this->offset_num = $offset_num;
    return $this;
  }

  public function pagenate($page){
    if (!is_int($page)) throw new Exception("Error Processing Request", 1);
    if ($page > 0 && $this->max_rows > 0) {
      $this->limit_num = $this->max_rows * $page; 
      $this->offset_num = $this->max_rows * ($page - 1); 
    }
    return $this;
  }

  public function asc($asc){
    $this->ascs[] = $this->ascs ? ", ASC " . $this->model_name . "." . $asc :  " DESC " . $this->model_name . "." . $asc;
  }

  public function desc($asc){
    $this->descs[] = $this->descs ? ", DESC " . $this->model_name . "." . $asc :  " DESC " . $this->model_name . "." . $asc;
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

  //  新規登録・更新
  public function save($form) {
    try {
      $hssModels = [];

      $this->validation($form);

      if (isset($form[$this->model_name][$this->primary_key])) $sql = $this->createModifySql($form[$this->model_name]);  // CASE MODIFY
      else $sql = $this->createInsertSql();  // CASE INSERT

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
            $stmt->bindParam($col_name, 'NOW()', PDO::PARAM_STR);
            break;
          default:
            $stmt->bindValue($col_name, $value, $this->getColumnType($col_name));
            break;
        }
      }

      $stmt->execute();
      //  従属モデルへのセーブ処理
      $id = $this->dbh->lastInsertId($this->primary_key);
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
    $col_names = array_keys($this->columns);
    $colums_str = null;
    $values_str = null;
    foreach ($col_names as $col_name) {
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
    $this->debug->log("BaseModel::saveHasModel() foreign_key[".$this->has[$model_name]['foreign_key']."] form:" . print_r($f, true));
    $obj->save($f);
  }

  //  削除

  //  共通
  protected function setValue($key, $value){
    $type = $this->columns[$key]['type'];
    if (is_array($value)) {
      if ($type == 'SET') {
        $val_tmp = '';
        foreach ($value as $key => $val) {
          $val = mysql_escape_string($val);
          $val .= htmlspecialchars($val, ENT_QUOTES);
          $val_tmp .= $val_tmp ? $val_tmp : ", " . $val_tmp;
        }
        $value = $val_tmp;
      }
    } else {
      $value = mysql_escape_string($value);
      $value .= htmlspecialchars($value, ENT_QUOTES);
    }

    switch ($type) {
      case 'INT':
      case 'TINYINT':
      case 'SMALLINT':
      case 'BIGINT':
      case 'FLOAT':
      case 'DOUBLE':
        return $value;
        break;
      case 'SET':
        return "'" . $value . "'";
        break;
      default:
        return "'".$value."'";
        break;
    }
  }

  public function getColumnType($col_name) {
    $type = $this->columns[$col_name]['type'];
    switch ($type) {
      case 'INT':
      case 'TINYINT':
      case 'SMALLINT':
      case 'BIGINT':
        return PDO::PARAM_INT;
        break;
      case 'FLOAT':
      case 'DOUBLE':
        return PDO::PARAM_INT;
        break;
      case 'BOOL':
        return PDO::PARAM_BOOL;
        break;
      case 'SET':
      default:
        return PDO::PARAM_STR;
        break;
    }
  }

  public function delete($id){
    $this->debug->log("BaseModel::delete() has:".print_r($this->has, true));
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
    $this->debug->log("BaseModel::delete() sql[".$sql."] id[".$id."]");
    $stmt = $this->dbh->prepare($sql);
    $stmt->bindValue($this->primary_key, $id, $this->getColumnType($this->primary_key));
    $stmt->execute();
  }

  public function getColumns() {
    return array_keys($this->columns);
  }

  public function validation($form) {
    // $this->form
  }

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
}