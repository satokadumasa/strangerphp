<?php
require_once PROJECT_ROOT . '/vendor/autoload.php';

class Notification {
  //  ログ関連
  public $error_log;
  public $info_log;
  public $debug;

  public $conf = null;
  public function __construct() {
    $this->error_log = new Logger('ERROR');
    $this->info_log = new Logger('INFO');
    $this->debug = new Logger('DEBUG');
    $conf = Config::get('mailer');
    $this->conf = $conf['mailer'];
  }

  public function sendRegistNotify($data, $body, $subject){
    $this->debug->log("Notification::sendRegistNotify() body:".print_r($body, true));

    $transport = \Swift_SmtpTransport::newInstance()
        ->setHost($this->conf['host'])
        ->setPort($this->conf['port'])
        ->setEncryption($this->conf['encrypt'])
        ->setUsername($this->conf['username'])
        ->setPassword($this->conf['password'])
    ;
    $mailer = \Swift_Mailer::newInstance($transport);
    $message = \Swift_Message::newInstance()
        ->setSubject($subject)
        ->setFrom(array($this->conf['from'] => 'k-holy'))
        ->setTo(array($data['User']['email'] => 'k-holy'))
        ->setBody($body, 'text/plain')
        ;
    $failedRecipients = array();
    if ($mailer->send($message, $failedRecipients)) {
        return true;
    }
  }

  public function geterateRegistNotifyMessage($form, $class_name, $teplate_name) {
    $this->debug->log("Notification::geterateRegistNotifyMessage() form:".print_r($form, true));
    $url = BASE_URL . '/confirm/' . $form['User']['authentication_key'];

    $site_info = Config::get('site_info');

    $this->debug->log("Notification::geterateRegistNotifyMessage() site_info:".print_r($site_info, true));
    $mailer_data = [
      'Mailer' => [
        'username' => $form['User']['username'],
        'email' => $form['User']['email'],
        'url' => $url,
        'site_name' => $site_info['site_info']['site_name'],
        'address' => $site_info['site_info']['address'],
        'tel' => $site_info['site_info']['tel'],
        'admin_mail' => $this->conf['from'],
      ],
    ];
    $this->debug->log("Notification::geterateRegistNotifyMessage() mailer_data:".print_r($mailer_data, true));
    $view = new View($teplate_name);
    $body = [];
    $file_name = VIEW_TEMPLATE_PATH.$class_name.'/'.$teplate_name.'.tpl';
    $view->framingView($body, $mailer_data, $file_name, 'Mailer');
    return implode('', $body);
  }
}