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
    $this->debug->log("AuthController::auth() request:".print_r($this->request, true));
    try{
      if(Authentication::auth($this->dbh, $this->request)){
        $this->redirect(DOCUMENT_ROOT);
      }
      else {
        $this->redirect(DOCUMENT_ROOT.'login/');
    }
    } catch (Exception $e) {
      $this->redirect(DOCUMENT_ROOT.'login/');
    }
  }

  /**
   *
   */
  public function confirm(){
    $user = new AuthModel($this->dbh);
    $data = $user->where('User.authentication_key', '=', $this->request['confirm_string'])->find('first');
    $data['User']['authentication_key'] = null;
    $user->save($data);
    $this->set('Title', 'Auth Confirmed');
    $this->set('message', 'Welcom, Confirmed your redistration.');
    $this->set('Auth', $data['Auth']);
    $this->set('datas', $data);
  }

  /**
   *  一覧
   */
  public function index() {
    $auths = new AuthModel($this->dbh);
    $limit = 10 * (isset($this->request['page']) ? $this->request['page'] : 1);
    $offset = 10 * (isset($this->request['page']) ? $this->request['page'] - 1 : 0);

    $datas = $auths->where('Auth.id', '>', 0)->limit($limit)->offset($offset)->find('all');

    $ref = isset($this->request['page']) ? $this->request['page'] : 0;
    $next = isset($this->request['page']) ? $this->request['page'] + 1 : 2;

    $this->set('Title', 'Auth List');
    $this->set('Auth', $datas);
    $this->set('ref', $ref);
    $this->set('next', $next);
  }

  public function show() {
    $datas = null;
    $id = $this->request['id'];

    $auths = new AuthModel($this->dbh);
    $datas = $auths->where('Auth.id', '=', $id)->find('first');
    $this->set('Title', 'Auth Ditail');
    $this->set('Auth', $datas['Auth']);
  }

  public function create() {
    $auths = new AuthModel($this->dbh);
    $form = $auths->createForm();
    $this->set('Title', 'Auth Create');
    $this->set('Auth', $form['Auth']);
  }

  public function save(){
    try {

      $session = Session::get();
      if (!isset($session['Auth'])) {
        // 認証情報が無い場合に例外を投げる
        throw new Exception("権限がありません。", 1);
      }
      if (!in_array($session['Auth']['role_id'], [ADMIN_ROLE_ID, OPERATOR_ROLE_ID], true)) {
        // 権限がない場合に例外を投げる
        throw new Exception("権限がありません。", 1);
      }

      $this->dbh->beginTransaction();
      $auths = new AuthModel($this->dbh);
      $form = $auths->save($this->request);
      $this->dbh->commit();

      $this->redirect(BASE_URL . 'Auth' . '/show/' . $auths->primary_key_value . '/');
      exit();
    } catch (Exception $e) {
      $this->debug->log("AuthController::create() error:" . $e->getMessage());
      $this->set('Title', 'User Save Error');
      $this->set('error_message', '保存ができませんでした。');
    }
  }

  public function edit() {
    try {
      $datas = null;
      $id = $this->request['id'];

      $auths = new UserModel($this->dbh);
      $datas = $auths->where('Auth.id', '=', $id)->find('first');
      $this->set('Title', 'Auth Edit');
      $this->set('User', $datas['User']);
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