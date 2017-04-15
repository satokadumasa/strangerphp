<?php
class BaseModel {
  //  DBハンドル
  protected $dbh   = null;

  // public $table_name  = null;
  // public $model_name  = null;
  // public $belongthTo  = null;
  // public $has         = null;

  protected $conditions = [];
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

  protected $primary_key = 'id';

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

  public function find($keys = null) {
    $datas = [];
    $primary_keys = [];

    $sql = $this->creteFindSql();

    $column_names = null;

    foreach ($this->dbh->query($sql) as $row) {
      $data = [];
      if(!$column_names) $column_names = array_keys($row);
      foreach ($column_names as  $column_name) {
        if(is_int($column_name)) continue;
        list($model_name, $col_name) = explode(".", $column_name);
        $data[$model_name][$col_name]= $row[$column_name];
      }
      $this->debug->log("BaseModel::find() data:" . print_r($data, true));
      if (array_search($data[$this->model_name][$this->primary_key], $primary_keys)) continue;
      $primary_keys[] = $data[$this->model_name][$this->primary_key];
      $datas[$data[$this->model_name][$this->primary_key]] = $data;
    }

    $this->debug->log("BaseModel::find() primary_keys:" . print_r($primary_keys, true));

    if (isset($primary_keys)) $this->findHasModelesData($datas, $primary_keys);

    $this->debug->log("BaseModel::find() datas:".print_r($datas, true));

    return $datas;
  }

  public function findHasModelesData(&$datas, $primary_keys = null) {
    if ($this->has) {
      foreach ($this->has as $model_name => $options) {
        $model_class_name = $model_name."Model";
        $obj = new $model_class_name($this->dbh);
        $setDatas = $obj->where($options['foreign_key'], 'IN', $primary_keys)->find();
        $this->debug->log("BaseModel::findHasModelesData() setDatas:".print_r($setDatas, true));
        $this->debug->log("BaseModel::findHasModelesData() ---------------------:");
        $this->setHasModelDatas($model_name, $options['foreign_key'],$datas, $setDatas, $primary_keys);
      }
    }
  }

  public function setHasModelDatas($model_name, $foreign_key_name,&$datas, $setDatas, $primary_keys) {
    foreach ($primary_keys as $primary_key) {
      foreach ($setDatas as $setData) {
        if ($setData[$model_name][$foreign_key_name] == $primary_key) {
          $datas[$setData[$model_name][$foreign_key_name]][$this->model_name][$model_name][] = $setData[$model_name];
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

    $this->debug->log("BaseModel::createCondition() sql:".$sql);

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
      $this->debug->log("BaseModel::createCondition() value:".$condition['value']);
      $cond_tmp .= " " . $condition['value'] . " ";
      $cond .= $cond ? " and " . $cond_tmp : $cond_tmp;
    }

    if($cond) $cond = " WHERE " . $cond;

    return $cond;
  }

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
    $this->debug->log("BaseModel::getColumnType() columns:" . print_r($this->columns, true));
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

  public function getColumns() {
    return array_keys($this->columns);
  }

  public function save($form) {
    try {
      $hssModels = [];

      $this->validation($form);

      if (isset($form[$this->model_name][$this->primary_key])) $sql = $this->createModifySql($form[$this->model_name]);  // CASE MODIFY
      else $sql = $this->createInsertSql();  // CASE INSERT

      $this->debug->log("BaseModel::save() SQL:" . $sql);
      $this->debug->log("BaseModel::save() form:".print_r($form, true));
      $this->debug->log("BaseModel::save() model_name:" . $this->model_name);
      $this->debug->log("BaseModel::save() has:" . print_r($this->has,  true));

      if($this->has){
        $hssModels = array_keys($this->has);
        $this->debug->log("BaseModel::save() hssModels:" . print_r($hssModels, true));
      }

      $stmt = $this->dbh->prepare($sql);
      foreach ($form[$this->model_name] as $col_name => $value) {
        if ($hssModels && in_array($col_name, $hssModels)) {
          continue;
        }
        $colum_name = ":".$col_name;
        $this->debug->log("BaseModel::save() col_name(2):" . $col_name .">>>>value:".$value);
        switch ($col_name) {
          case 'created_at':
          case 'modified_at':
            $this->debug->log("BaseModel::save() col_name(3):" . $col_name .">>>>value:".$value);
            $stmt->bindParam($col_name, 'NOW()', PDO::PARAM_STR);
            break;
          default:
            $this->debug->log("BaseModel::save() col_name(4):" . $col_name .">>>>value:".$value);
            $stmt->bindValue($col_name, $value, $this->getColumnType($col_name));
            break;
        }
      }

      $stmt->execute();
      $id = $this->dbh->lastInsertId($this->primary_key);
      $this->debug->log("BaseModel::save() id:" . print_r($id, true));

      foreach ($form[$this->model_name] as $model_name => $value) {
        if ($hssModels && in_array($model_name, $hssModels)) {
          $model_class_name = $model_name."Model";
          $obj = new $model_class_name($this->dbh);
          $this->debug->log("BaseModel::save() model_name:" . $model_name);
          $this->debug->log("BaseModel::save() foreign_key:" . $this->has[$model_name]['foreign_key']);
          $value[$this->has[$model_name]['foreign_key']] = $id;
          $f = [];
          $f[$col_name] = $value;
          $obj->save($f);
        } 
        else
          continue;
      }

      $this->debug->log("BaseModel::save() stmt:" . print_r($stmt, true));
    } catch (Exception $e) {
      $this->debug->log("BaseModel::save() error:" . $e->getMessage());
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

  public function validation($form) {
    // $this->form
  }
}