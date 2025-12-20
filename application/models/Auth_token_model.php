<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Auth_token_model extends CI_Model {

  public function create($userid, $selector, $validator_plain, $ttl_secs = 2592000){ // 30*24*3600
    $now = time();
    $data = [
      'userid'     => (int)$userid,
      'selector'   => $selector,
      'token_hash' => hash('sha256', $validator_plain),
      'expires'    => date('Y-m-d H:i:s', $now + $ttl_secs),
      'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
      'ip'         => $_SERVER['REMOTE_ADDR'] ?? null,
      'created_at' => date('Y-m-d H:i:s', $now),
      'last_used'  => date('Y-m-d H:i:s', $now),
    ];
    $this->db->insert('auth_remember_tokens', $data);
    return $this->db->insert_id();
  }

  public function find_by_selector($selector){
    return $this->db->get_where('auth_remember_tokens', ['selector'=>$selector])->row_array();
  }

  public function rotate($id, $new_validator_plain, $ttl_secs = 2592000){
    $this->db->update('auth_remember_tokens', [
      'token_hash' => hash('sha256', $new_validator_plain),
      'expires'    => date('Y-m-d H:i:s', time() + $ttl_secs),
      'last_used'  => date('Y-m-d H:i:s')
    ], ['id'=>(int)$id]);
  }

  public function touch($id){
    $this->db->update('auth_remember_tokens', ['last_used'=>date('Y-m-d H:i:s')], ['id'=>(int)$id]);
  }

  public function delete_by_selector($selector){
    $this->db->delete('auth_remember_tokens', ['selector'=>$selector]);
  }

  public function delete_all_by_user($userid){
    $this->db->delete('auth_remember_tokens', ['userid'=>(int)$userid]);
  }

  public function gc_expired(){
    $this->db->where('expires <', date('Y-m-d H:i:s'))->delete('auth_remember_tokens');
  }

  public function limit_per_user($userid, $keep_latest = 5){
    // hapus token lama di luar 5 terakhir
    $rows = $this->db->order_by('last_used','DESC')
                     ->get_where('auth_remember_tokens',['userid'=>(int)$userid])->result_array();
    if (count($rows) > $keep_latest){
      $ids = array_column(array_slice($rows, $keep_latest), 'id');
      if ($ids) $this->db->where_in('id',$ids)->delete('auth_remember_tokens');
    }
  }
}
