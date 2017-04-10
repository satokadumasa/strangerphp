<?php
class FileUtil{
  private $log_file = null;

  public function __construct($log_type = 'info', $){

  }

  public function log($message){
    try {

      if($fp = fopen($this->log_file, 'a'){
        fputs($fp, $message);
      }
      fclose($fp);
    } catch($e) {

    }
  }

  
} 