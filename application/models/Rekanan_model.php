<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Rekanan_model extends CI_Model {

  


  public function all($q=null){
    if ($q){
      $this->db->group_start()
        ->like('kode',$q)->or_like('nama_perusahaan',$q)->or_like('email',$q)
      ->group_end();
    }
    return $this->db->order_by('nama_perusahaan','asc')->get('m_rekanan')->result_array();
  }

  public function find($id){
    return $this->db->get_where('m_rekanan',['id_rekanan'=>(int)$id])->row_array();
  }

  public function by_user($userid){
    return $this->db->get_where('m_rekanan',['userid'=>(int)$userid])->row_array();
  }

  public function kode_by_user($userid){
    $r = $this->by_user($userid);
    return $r ? (string)$r['kode'] : null;
  }

  /** Simpan + sinkron user vendor. Kembalikan id_rekanan. */
  public function save_and_sync(?int $id, array $in, int $actor_userid){
    $this->load->model('User_model','users');
    $this->load->model('Audit_model','audit');

    // normalisasi
    $payload = [
      'kode'             => trim((string)$in['kode']),
      'nama_perusahaan'  => trim((string)$in['nama_perusahaan']),
      'alamat_perusahaan'=> trim((string)$in['alamat_perusahaan']),
      'email'            => trim((string)$in['email']),
      'telp'             => trim((string)$in['telp']),
      'wa'               => trim((string)$in['wa']),
      'ttd_nama'         => trim((string)$in['ttd_nama']),
      'ttd_jabatan'      => trim((string)$in['ttd_jabatan']),
    ];
    $username  = trim((string)($in['username'] ?? $payload['kode']));
    $set_pass  = (string)($in['set_password'] ?? ''); // kosong = auto/gak ganti
    $send_mail = !empty($in['send_mail']) ? 1 : 0;

    // validasi unik (kode, email)
    $this->guard_unique('m_rekanan','kode',$payload['kode'],$id,'id_rekanan');
    $this->guard_unique('m_rekanan','email',$payload['email'],$id,'id_rekanan');

    $this->db->trans_start();

    $before = $id ? $this->find($id) : [];
    $userId = $before['userid'] ?? null;
    $oldKode= $before['kode']   ?? null;

    // sinkron user
    if ($userId){
      // update user vendor
      $u = [
        'username' => $username,
        'groupid'  => 4,
        'kode'     => $payload['kode'],
        'nama'     => $payload['nama_perusahaan'],
        'email'    => $payload['email'],
        'telp'     => $payload['telp'],
      ];
      if ($set_pass!==''){ // kalau admin mengisi password baru di form
        $u['password'] = password_hash($set_pass, PASSWORD_BCRYPT, ['cost'=>11]);
      }
      $this->db->update('ci_users', $u, ['userid'=>$userId]);
    } else {
      // buat user vendor baru
      $u = [
        'username' => $username ?: strtolower(preg_replace('/\W+/','',$payload['kode'])),
        'groupid'  => 4,
        'kode'     => $payload['kode'],
        'nama'     => $payload['nama_perusahaan'],
        'email'    => $payload['email'],
        'telp'     => $payload['telp'],
        'password' => password_hash(($set_pass!==''?$set_pass:$this->gen_password()), PASSWORD_BCRYPT, ['cost'=>11])
      ];
      $this->guard_unique('ci_users','username',$u['username'], null, 'userid');
      $this->db->insert('ci_users',$u);
      $userId = (int)$this->db->insert_id();
    }

    // upsert m_rekanan
    if ($id){
      $payload['userid'] = (int)$userId;
      $this->db->update('m_rekanan', $payload, ['id_rekanan'=>$id]);
    } else {
      $payload['userid'] = (int)$userId;
      $this->db->insert('m_rekanan',$payload);
      $id = (int)$this->db->insert_id();
    }

    // jika KODE berubah → propagate ke shipments.rekanan_kode
    if ($id && $oldKode && $oldKode !== $payload['kode']){
      $this->db->update('shipments',['rekanan_kode'=>$payload['kode']], ['rekanan_kode'=>$oldKode]);
      $this->db->update('ci_users',['kode'=>$payload['kode']], ['userid'=>$userId]); // jaga konsistensi
    }

    $after = $this->find($id);
    $this->audit->log('rekanan', $id, ($before?'update':'create'), $before, $after, ['via'=>'form']);

    $this->db->trans_complete();
    if (!$this->db->trans_status()) throw new \RuntimeException('DB transaction failed');

    // email password awal (opsional)
    if (!$before && $send_mail && !empty($after['email'])){
      $this->send_welcome($u['username'], ($set_pass?:'[password terset otomatis]'), $after['email']);
    }

    return $id;
  }

  public function delete_with_guard(int $id){
    $this->load->model('Audit_model','audit');
    $row = $this->find($id);
    if (!$row) return false;
    // cegah hapus kalau ada shipments
    $cnt = $this->db->where('rekanan_kode',$row['kode'])->count_all_results('shipments');
    if ($cnt>0) throw new \RuntimeException('Tidak bisa dihapus: masih ada data shipments terkait.');

    $this->db->trans_start();
    // hapus user juga? Biasanya jangan—bisa nonaktifkan. Di sini kita biarkan user tetap ada.
    $this->db->delete('m_rekanan',['id_rekanan'=>$id]);
    $this->audit->log('rekanan', $id, 'delete', $row, [], ['via'=>'form']);
    $this->db->trans_complete();
    return true;
  }

  private function guard_unique($table,$col,$val,$id=null,$pk='id'){
    if ($val==='') return;
    $this->db->from($table)->where($col,$val);
    if ($id) $this->db->where("$pk !=",(int)$id);
    $exists = $this->db->count_all_results();
    if ($exists) throw new \InvalidArgumentException("Duplikat: {$col} sudah dipakai.");
  }

  private function gen_password($len=10){
    $alphabet='ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789!@#$%';
    $out=''; for($i=0;$i<$len;$i++) $out.=$alphabet[random_int(0,strlen($alphabet)-1)];
    return $out;
  }

  private function send_welcome($username, $password_label, $to){
    $this->load->library('email');
    $this->email->clear();
    $this->email->from('no-reply@yourdomain.com', 'Coal Shipping');
    $this->email->to($to);
    $this->email->subject('Akses Vendor — Coal Shipping');
    $html = "<p>Halo,</p>
      <p>Akun vendor Anda telah dibuat/diupdate.</p>
      <ul>
        <li>Username: <b>".htmlentities($username)."</b></li>
        <li>Password: <b>".htmlentities($password_label)."</b></li>
        <li>Role: Vendor</li>
      </ul>
      <p>Silakan login di ".site_url('login').". Demi keamanan, segera ganti password Anda.</p>";
    $this->email->message($html);
    @$this->email->send(false);
  }
}


