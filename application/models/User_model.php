<?php defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model {

  public function by_username($username){
    return $this->db->get_where('ci_users', ['username'=>$username])->row_array();
  }

  public function verify_password_and_upgrade(array $user, $raw_password){
    $hash = (string)($user['password'] ?? '');
    $userid = (int)$user['userid'];

    // 1) bcrypt/argon (via password_hash)
    if (preg_match('/^\$2y\$|\$argon2id\$/', $hash)){
      $ok = password_verify($raw_password, $hash);
      // upgrade jika perlu (cost berubah)
      if ($ok && password_needs_rehash($hash, PASSWORD_BCRYPT, ['cost'=>11])){
        $new = password_hash($raw_password, PASSWORD_BCRYPT, ['cost'=>11]);
        $this->db->update('ci_users', ['password'=>$new], ['userid'=>$userid]);
      }
      return $ok;
    }

    // 2) legacy MD5
    if (strlen($hash)===32 && ctype_xdigit($hash)){
      $ok = (md5($raw_password) === strtolower($hash));
      if ($ok){
        $new = password_hash($raw_password, PASSWORD_BCRYPT, ['cost'=>11]);
        $this->db->update('ci_users', ['password'=>$new], ['userid'=>$userid]);
      }
      return $ok;
    }

    // 3) plaintext (sangat lama) → upgrade ke bcrypt jika cocok
    if ($hash === $raw_password){
      $new = password_hash($raw_password, PASSWORD_BCRYPT, ['cost'=>11]);
      $this->db->update('ci_users', ['password'=>$new], ['userid'=>$userid]);
      return true;
    }

    return false;
  }

  public function set_last_login($userid){
    if (!$this->db->field_exists('last_login','ci_users')){
      // opsional: tambahkan kolom kalau belum ada
      // $this->db->query("ALTER TABLE ci_users ADD last_login DATETIME NULL");
      return;
    }
    $this->db->update('ci_users',['last_login'=>date('Y-m-d H:i:s')],['userid'=>(int)$userid]);
  }

  // Sudah ada di versi sebelumnya, tetap disimpan:
  public function emails_by_group($groupid){
    $rows = $this->db->select('email')->from('ci_users')
            ->where('groupid', (int)$groupid)->get()->result_array();
    return array_values(array_filter(array_map(fn($r)=>trim($r['email']??''), $rows)));
  }
}
