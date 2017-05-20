  public function index() {
    $<!----table_name----> = new <!----class_name---->Model($this->dbh);
    $limit = 10 * (isset($this->request['page']) ? $this->request['page'] : 1);
    $offset = 10 * (isset($this->request['page']) ? $this->request['page'] - 1 : 0);

    $datas = $<!----table_name---->->where('<!----class_name---->.id', '>', 0)->limit($limit)->offset($offset)->find('all');

    $ref = isset($this->request['page'] ? $this->request['page'] : 0;
    $next = isset($this->request['page'] ? $this->request['page'] + 1 : 2;

    $this->set('Title', '<!----class_name----> List');
    $this->set('datas', $datas);
    $this->set('ref', $ref);
    $this->set('next', $next);
    $thi
  }

  public function show() {
    $datas = null;
    $id = $this->request['id'];

    $<!----table_name----> = new <!----class_name---->Model($this->dbh);
    $datas = $<!----table_name---->->where('<!----class_name---->.id', '=', $id)->find('first');
    $this->set('Title', '<!----class_name----> Ditail');
    $this->set('datas', $datas);
  }

  public function create() {
    $this->debug->log("<!----class_name---->Controller::create()");
    try {
      echo "<!----class_name---->Controller::create()<br>";
      $this->dbh->beginTransaction();
      $<!----table_name----> = new <!----class_name---->Model($this->dbh);
      $<!----table_name---->->save($this->request);
      $this->dbh->commit();
    } catch (Exception $e) {
      $this->debug->log("<!----class_name---->Controller::create() error:" . $e->getMessage());
    }
  }

  public function edit() {
    $this->debug->log("<!----class_name---->Controller::edit()");
  }

  public function delete() {
    try {
      $this->dbh->beginTransaction();
      $<!----table_name----> = new <!----class_name---->.Model($this->dbh);
      $<!----table_name---->->delete($this->request['id']);
      $this->dbh->commit();
    } catch (Exception $e) {
      $this->debug->log("UsersController::delete() error:" . $e->getMessage());
    }
  }
