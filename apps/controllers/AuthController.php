<?php
class AuthController extends BaseController{
  public function __construct($uri, $url = null) {
    $conf = Config::get('database.config');
    $database = $conf['default_database'];
    parent::__construct($database, $uri, $url);
    $this->controller_class_name = str_replace('Controller', '', get_class($this));
    $this->role_ids = Config::get('acc/auths');
  }

  /**
   *  ログイン画面
   */
  public function login() {
    $auths = new UserModel($this->dbh);
    $form = $auths->createForm();
    $this->set('Title', 'Auth Login');
    $this->set('User', $form['User']);
  }

  /**
   *  ログアウト処理
   */
  public function logout() {
    session_destroy();
    $this->redirect('/');
  }

  /**
   *  ログイン処理
   */
  public function auth() {
    try{
      if(Authentication::auth($this->dbh, $this->request)){
        $this->redirect('/');
      }
      else {
        $this->redirect('/login/');
    }
    } catch (Exception $e) {
      $this->redirect('/login/');
    }
  }

  /**
   *  一覧
   */
  public function index() {
    $auths = new UserModel($this->dbh);
    $limit = 10 * (isset($this->request['page']) ? $this->request['page'] : 1);
    $offset = 10 * (isset($this->request['page']) ? $this->request['page'] - 1 : 0);

    $datas = $auths->where('Auth.id', '>', 0)->limit($limit)->offset($offset)->find('all');

    $ref = isset($this->request['page']) ? $this->request['page'] : 0;
    $next = isset($this->request['page']) ? $this->request['page'] + 1 : 2;

    $this->set('Title', 'Auth List');
    $this->set('datas', $datas);
    $this->set('Auth', $datas);
    $this->set('ref', $ref);
    $this->set('next', $next);
  }

  public function show() {
    $datas = null;
    $id = $this->request['id'];

    $auths = new UserModel($this->dbh);
    $datas = $auths->where('User.id', '=', $id)->find('first');
    $this->set('Title', 'Auth Ditail');
    $this->set('Auth', $datas['Auth']);
    $this->set('datas', $datas);
  }

  public function create() {
    $this->debug->log("AuthController::create()");
    $auths = new UserModel($this->dbh);
    $form = $auths->createForm();
    $this->debug->log("AuthController::create() form:" .print_r($form, true));
    $this->set('Title', 'Auth Create');
    $this->set('User', $form['User']);
  }

  public function save(){
    $this->debug->log("AuthController::save()");
    try {
      $this->dbh->beginTransaction();
      $auths = new UserModel($this->dbh);
      $form = $auths->save($this->request);
      $this->debug->log("AuthController::save() form:".print_r($form, true));
      $this->dbh->commit();

      $request_str = serialize($form);
      $this->debug->log("AuthController::save() request_str:".print_r($request_str, true));

      $cmd = 'php ' . BIN_PATH . 'send_notify.php';
      $this->debug->log("AuthController::save() exec_cmd:".$cmd);
      $result = exec($cmd);
      $this->debug->log("AuthController::save() exec_result:".print_r($result, true));

      // $body = null;
      // $notification = new Notification();
      // $body = $notification->geterateRegistNotifyMessage($form, 'Mailer', 'regist_notify');
      // $notification->sendRegistNotify($this->request, $body, '登録確認メール');

      // return new RedirectResponse('/avalon/', 303);
      // $url = BASE_URL . 'Auth' . '/show/' . $auths->primary_key_value . '/';
      // $this->redirect($url);
      $this->set('Title', 'User Registed');
    } catch (Exception $e) {
      $this->debug->log("AuthController::create() error:" . $e->getMessage());
    }
  }

  public function edit() {
    $this->debug->log("AuthController::edit()");
    try {
      $datas = null;
      $id = $this->request['id'];

      $auths = new UserModel($this->dbh);
      $datas = $auths->where('Auth.id', '=', $id)->find('first');
      $this->set('Title', 'Auth Edit');
      $this->set('Auth', $datas['Auth']);
      $this->set('datas', $datas);
    } catch (Exception $e) {
      $this->debug->log("AuthController::edit() error:" . $e->getMessage());
    }
  }

  public function delete() {
    try {
      $this->dbh->beginTransaction();
      $auths = new UserModel($this->dbh);
      $auths->delete($this->request['id']);
      $this->dbh->commit();
      $url = BASE_URL . 'Auth' . '/index/';
    } catch (Exception $e) {
      $this->debug->log("UsersController::delete() error:" . $e->getMessage());
    }
  }
}