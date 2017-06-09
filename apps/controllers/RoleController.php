<?php
class RoleController extends BaseController{
  public function __construct($uri, $url = null) {
    $conf = Config::get('database.config');
    $database = $conf['default_database'];
    parent::__construct($database, $uri, $url);
    $this->controller_class_name = str_replace('Controller', '', get_class($this));;
    $this->setAuthCheck(['index', 'create', 'edit', 'show', 'save', 'delete']);
    $this->role_ids = Config::get('acc/roles');
  }

  public function index() {
    $roles = new RoleModel($this->dbh);
    $limit = 10 * (isset($this->request['page']) ? $this->request['page'] : 1);
    $offset = 10 * (isset($this->request['page']) ? $this->request['page'] - 1 : 0);

    $datas = $roles->where('Role.id', '>', 0)->limit($limit)->offset($offset)->find('all');

    $ref = isset($this->request['page']) ? $this->request['page'] : 0;
    $next = isset($this->request['page']) ? $this->request['page'] + 1 : 2;

    $this->set('Title', 'Role List');
    $this->set('datas', $datas);
    $this->set('Role', $datas);
    $this->set('ref', $ref);
    $this->set('next', $next);
  }

  public function show() {
    $datas = null;
    $id = $this->request['id'];

    $roles = new RoleModel($this->dbh);
    $datas = $roles->where('Role.id', '=', $id)->find('first');
    $this->set('Title', 'Role Ditail');
    $this->set('Role', $datas['Role']);
    $this->set('datas', $datas);
  }

  public function create() {
    $this->debug->log("RoleController::create()");
    $roles = new RoleModel($this->dbh);
    $form = $roles->createForm();
    $this->set('Title', 'Role Create');
    $this->set('Role', $form['Role']);
  }

  public function save(){
    $this->debug->log("RoleController::save()");
    try {
      $this->dbh->beginTransaction();
      $roles = new RoleModel($this->dbh);
      $roles->save($this->request);
      $this->dbh->commit();
      $url = BASE_URL . 'Role' . '/show/' . $roles->primary_key_value . '/';
      $this->redirect($url);
    } catch (Exception $e) {
      $this->debug->log("RoleController::create() error:" . $e->getMessage());
      $this->set('Title', 'Role Save Error');
      $this->set('error_message', '保存ができませんでした。');
    }
  }

  public function edit() {
    $this->debug->log("RoleController::edit()");
    try {
      $datas = null;
      $id = $this->request['id'];

      $roles = new RoleModel($this->dbh);
      $datas = $roles->where('Role.id', '=', $id)->find('first');
      $this->set('Title', 'Role Edit');
      $this->set('Role', $datas['Role']);
      $this->set('datas', $datas);
    } catch (Exception $e) {
      $this->debug->log("RoleController::edit() error:" . $e->getMessage());
    }
  }

  public function delete() {
    try {
      $this->dbh->beginTransaction();
      $roles = new RoleModel($this->dbh);
      $roles->delete($this->request['id']);
      $this->dbh->commit();
      $url = BASE_URL . 'Role' . '/index/';
    } catch (Exception $e) {
      $this->debug->log("UsersController::delete() error:" . $e->getMessage());
    }
  }


}