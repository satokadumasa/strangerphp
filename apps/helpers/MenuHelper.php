<?php
// require_once dirname(dirname(dirname(__FILE__))) . "/config/config.php";
class MenuHelper{
  public $error_log;
  public $info_log;
  public $debug;

  private $auth = null;
  public function __construct($auth) {
    $this->error_log = new Logger('ERROR');
    $this->info_log = new Logger('INFO');
    $this->debug = new Logger('DEBUG');
    $this->auth = $auth;
  }

  public function site_menu($auth, $option = 'nologin'){
    $log_out_str = "<a href='".DOCUMENT_ROOT."logout/'>Logout</a>";
    $user_edit = "<a href='".DOCUMENT_ROOT."User/edit/".$this->auth['User']['id']."/'>UserEdit</a>";
    if (isset($this->auth['User']['UserInfo'])) {
      $user_info_edit = '<a href="'.DOCUMENT_ROOT.'UserInfo/edit/'.$this->auth['User']['id'].'/">UserInfo</a>' ;
    }
    else {
      $user_info_edit = '<a href="'.DOCUMENT_ROOT.'UserInfo/create/">UserInfo</a>';
    }

    $regist_url = '<a href="'.DOCUMENT_ROOT.'User/create/">登録</a>';
    $login = '<a href="'.DOCUMENT_ROOT.'login/">Login</a>';
    // if (isset($auth[]))
    if ($option == 'logined') {
      $site_menu = <<<EOF
    <ul id="dropmenu">
      <li><a href="#">メニュー</a>
        <ul>
          <li>
            $log_out_str
          </li>
          <li>
            $user_edit
          </li>
          <li>
            $user_info_edit
          </li>
        </ul>
      </li>
    </ul>
EOF;
    }
    else {
      $site_menu = <<<EOF
    <ul id="dropmenu">
      <li><a href="#">メニュー</a>
        <ul>
          <li>
            $login
          </li>
          <li>
            $regist_url
          </li>
        </ul>
      </li>
    </ul>
EOF;

    }
    return $site_menu;
  }  
}