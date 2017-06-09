<?php
require_once __DIR__ . '/../vendor/autoload.php';

class Authentication{
  public static function auth(&$dbh, $request){
    $auths = new UserModel($dbh);
    $auth = $auths->auth($request);
    if ($auth){
      $user_cookie_name = StringUtil::makeRandStr(USER_COOKIE_NAME_LENGTH);
      setcookie(COOKIE_NAME, $user_cookie_name, time() + COOKIE_LIFETIME);
      // Session::sessionStart();
      $data['Auth'] = $auth;
      Session::set($data);
      // setcookie(COOKIE_NAME, $user_cookie_name, COOKIE_LIFETIME, '/', DOMAIN_NAME);
      return true;
    }
    else {
      return false;
    }
  }

  public static function isAuth(){
    if (DEFAULT_FLAG_OF_AUTHENTICATION ) {
      $session = Session::get();
      return isset($session['Auth']) ? $session['Auth'] : false;
    }
    return true;
  }

  public static function roleCheck($role_ids, $action) {
    if (!isset($role_ids['acc'][$action]) || $role_ids['acc'][$action] = '') return true;
    $session = Session::get();
    return in_array($session['Auth']['User']['role_id'], $role_ids['acc'][$action]);
  }
}


