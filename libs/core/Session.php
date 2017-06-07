<?php
class Session {
  public static function sessionStart() {
    session_start(['cookie_lifetime' => COOKIE_LIFETIME]);
  }

  public static function get() {
    return isset($_SESSION[COOKIE_NAME]) ? $_SESSION[COOKIE_NAME] : false;
  }

  public static function set($value) {
    $_SESSION[COOKIE_NAME] = $value;
  }
}