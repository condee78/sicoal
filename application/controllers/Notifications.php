<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Notifications extends CI_Controller {
  public function __construct(){
    parent::__construct(); require_login();
    $this->load->model('Notification_model','notif');
  }

  public function unread(){
    $this->output->set_content_type('application/json');
    $uid = (int)$this->session->userdata('userid');
    $list = $this->notif->list_unread($uid, 20);
    $count = $this->notif->unread_count($uid);
    echo json_encode(['ok'=>true,'count'=>$count,'items'=>$list]);
  }

  public function read($target_id){
    $this->output->set_content_type('application/json');
    $uid = (int)$this->session->userdata('userid');
    $this->notif->mark_read((int)$target_id, $uid);
    echo json_encode(['ok'=>true]);
  }
}
