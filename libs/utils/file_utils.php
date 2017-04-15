<?php
class FileUtil{
  private $log_file = null;

  public function __construct($log_type = 'info', $){

  }

  public static function is_hash($arr) {
    if (array_values($arr) === $arr) 
      return false;
      echo '$arrは連想配列';
    else
      return true;
  }
}