<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Users_model extends CI_Model {

  // list dengan filter dan pencarian sederhana
  public function list($role_filter=null, $q=null){
    $this->db->from('ci_users');
    // hanya tampilkan admin/staff (bukan vendor) di halaman ini
    // groupid: 0 superadmin, 1 admin lbe, 2 staff lbe, 4 vendor/shipper
    $this->db->where_in('groupid',[0,1,2]);

    if ($role_filter !== null && $role_filter!=='') {
      $this->db->where('groupid', (int)$role_filter);
    }
    if ($q){
      $this->db->group_start()
        ->like('username', $q)
        ->or_like('nama', $q)
        ->or_like('email', $q)
        ->or_like('telp', $q)
      ->group_end();
    }
    $this->db->order_by('groupid','ASC');
    $this->db->order_by('username','ASC');
    return $this->db->get()->result_array();
  }

  public function find($userid){
    return $this->db->get_where('ci_users',['userid'=>$userid])->row_array();
  }

  public function username_exists($username, $exclude_userid=0){
    $this->db->from('ci_users')->where('username',$username);
    if ($exclude_userid) $this->db->where('userid !=',(int)$exclude_userid);
    return $this->db->count_all_results() > 0;
  }

  public function save($payload, $userid=null){
    $data = [
      'username' => $payload['username'],
      'groupid'  => (int)$payload['groupid'], // 0,1,2
      'kode'     => $payload['kode'] ?? null,
      'nama'     => $payload['nama'],
      'email'    => $payload['email'] ?? null,
      'telp'     => $payload['telp'] ?? null,
    ];
    if (!empty($payload['password'])) {
      $data['password'] = password_hash($payload['password'], PASSWORD_BCRYPT);
    }
    if ($userid){
      $this->db->update('ci_users', $data, ['userid'=>$userid]);
      return $userid;
    } else {
      $this->db->insert('ci_users',$data);
      return (int)$this->db->insert_id();
    }
  }

  public function delete($userid){
    $this->db->delete('ci_users',['userid'=>$userid]);
  }

  public function reset_password($userid, $newpass){
    $hash = password_hash($newpass, PASSWORD_BCRYPT);
    $this->db->update('ci_users', ['password'=>$hash], ['userid'=>$userid]);
  }
}
