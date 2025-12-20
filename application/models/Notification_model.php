<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Notification_model extends CI_Model {

  /* ===== Utilities (audience resolving) ===== */

  public function admin_userids(): array {
    // groupid: 0 superadmin, 1 admin lbe
    $res = $this->db->select('userid')->from('ci_users')->where_in('groupid',[0,1])->get()->result_array();
    return array_map(fn($r)=> (int)$r['userid'], $res);
  }

  public function vendor_userids_by_kode(string $rekanan_kode): array {
    // m_rekanan.userid = owner utama; bisa ditambah policymu (multi user vendor) kalau ada
    $r = $this->db->select('userid')->get_where('m_rekanan',['kode'=>$rekanan_kode])->row_array();
    return $r ? [(int)$r['userid']] : [];
  }

  public function userids_for_import(array $rekanan_kodes): array {
    $uids = [];
    foreach (array_unique($rekanan_kodes) as $kode) {
      $uids = array_merge($uids, $this->vendor_userids_by_kode($kode));
    }
    return array_values(array_unique($uids));
  }

  /* ===== Core creator ===== */

  private function create_notif(string $type, string $title, string $body, array $options): int {
    // $options: shipment_id?, rekanan_kode?, actor_userid?, audience ('admins'|'vendor'|'user'|'mixed'), targets_userids[], queue_channels[]
    $now = date('Y-m-d H:i:s');
    $data = [
      'type'         => $type,
      'title'        => $title,
      'body'         => $body,
      'shipment_id'  => $options['shipment_id']  ?? null,
      'rekanan_kode' => $options['rekanan_kode'] ?? null,
      'actor_userid' => $options['actor_userid'] ?? null,
      'audience'     => $options['audience']     ?? 'mixed',
      'created_at'   => $now,
    ];
    
    $this->db->insert('notifications', $data);
    $notif_id = (int)$this->db->insert_id();

    // target fan-out
    $targets = $options['targets_userids'] ?? [];
    foreach ($targets as $uid) {
      $this->db->insert('notification_targets', [
        'notif_id' => $notif_id, 'userid' => (int)$uid, 'delivered_at'=>null, 'read_at'=>null
      ]);
    }

    // queue for channels
    $channels = $options['queue_channels'] ?? ['inapp'];
    foreach ($channels as $ch) {
      $payload = [
        'subject' => $title,
        'body'    => $body,
        'shipment_id'  => $data['shipment_id'],
        'rekanan_kode' => $data['rekanan_kode']
      ];
      $this->db->insert('notification_queue', [
        'notif_id' => $notif_id,
        'channel'  => $ch,
        'payload_json' => json_encode($payload),
        'status'   => 'queued',
        'updated_at'=> $now,
        'created_at'=> $now
      ]);
    }
    return $notif_id;
  }

  /* ===== Events mapping to your 5 rules ===== */

  // (1) setelah import: kabari semua vendor yang ada di file
  public function on_import(array $rekanan_kodes, int $actor_userid, int $inserted, int $updated) {
    if (empty($rekanan_kodes)) return;
    $targets = $this->userids_for_import($rekanan_kodes);
    if (!$targets) return;
    $title = 'Import Shipment Data';
    $body  = "Ada import data shipment. Insert: {$inserted}, Update: {$updated}. Vendor terkait: ".implode(', ', array_unique($rekanan_kodes)).".";
    $this->create_notif('IMPORT', $title, $body, [
      'actor_userid'=>$actor_userid, 'audience'=>'vendor', 'targets_userids'=>$targets, 'queue_channels'=>['inapp','email']
    ]);
  }

  // (2) perubahan shipment → kirim ke pihak terdampak (vendor terkait)
  public function on_shipment_changed(string $shipment_id, string $rekanan_kode, array $changed_fields, int $actor_userid) {
    $title = 'Perubahan Data Shipment';
    $body  = "Ada perubahan pada Shipment #{$shipment_id} (Vendor {$rekanan_kode}). Field: ".implode(', ', array_keys($changed_fields)).".";
    $vendor_targets = $this->vendor_userids_by_kode($rekanan_kode);
    if ($vendor_targets) {
      $this->create_notif('SHIPMENT_CHANGED', $title, $body, [
        'shipment_id'=>$shipment_id,'rekanan_kode'=>$rekanan_kode,
        'actor_userid'=>$actor_userid, 'audience'=>'vendor',
        'targets_userids'=>$vendor_targets, 'queue_channels'=>['inapp','email']
      ]);
    }
  }

  // (3) sample received & actual set admin → notif ke vendor
  public function on_sample_or_actual_set(string $shipment_id, string $rekanan_kode, int $actor_userid, array $keys_set) {
    $title = 'Update Actual/Sample';
    $body  = "Admin meng-set ".implode(', ',$keys_set)." untuk Shipment #{$shipment_id}.";
    $targets = $this->vendor_userids_by_kode($rekanan_kode);
    if ($targets) {
      $this->create_notif('SAMPLE_RECEIVED', $title, $body, [
        'shipment_id'=>$shipment_id,'rekanan_kode'=>$rekanan_kode,
        'actor_userid'=>$actor_userid,'audience'=>'vendor',
        'targets_userids'=>$targets,'queue_channels'=>['inapp','email']
      ]);
    }
  }

  // (4) vendor set actual_departure → ingatkan upload COA & COW
  public function on_vendor_set_departure(string $shipment_id, string $rekanan_kode, int $actor_userid) {
    $title = 'Aksi Diperlukan: Upload COA & COW';
    $body  = "Anda telah mengisi Actual Departure untuk Shipment #{$shipment_id}. Harap unggah dokumen COA dan COW.";
    $targets = $this->vendor_userids_by_kode($rekanan_kode);
    if ($targets) {
      $this->create_notif('DEPARTURE_SET', $title, $body, [
        'shipment_id'=>$shipment_id,'rekanan_kode'=>$rekanan_kode,
        'actor_userid'=>$actor_userid,'audience'=>'vendor',
        'targets_userids'=>$targets,'queue_channels'=>['inapp','email','wa'] // jika ada WA webhook
      ]);
    }
  }

  // (5) setiap perubahan oleh vendor → kirim ke admin
  public function on_vendor_change_to_admin(string $shipment_id, string $rekanan_kode, array $changed_fields, int $actor_userid) {
    $title = 'Vendor Update Shipment';
    $body  = "Vendor {$rekanan_kode} mengubah Shipment #{$shipment_id}; field: ".implode(', ', array_keys($changed_fields)).".";
     $targets = $this->admin_userids();
  
    if ($targets) {
        
      $this->create_notif('VENDOR_CHANGE', $title, $body, [
        'shipment_id'=>$shipment_id,'rekanan_kode'=>$rekanan_kode,
        'actor_userid'=>$actor_userid,'audience'=>'admins',
        'targets_userids'=>$targets,'queue_channels'=>['inapp','email']
      ]);
    }
  }

  /* ===== In-app helpers ===== */

  public function unread_count(int $userid): int {
    return (int)$this->db->where('userid',$userid)->where('read_at IS NULL',null,false)->count_all_results('notification_targets');
  }

  public function list_unread(int $userid, int $limit=20): array {
    return $this->db->select('nt.id as target_id, n.*')
      ->from('notification_targets nt')
      ->join('notifications n','n.id=nt.notif_id')
      ->where('nt.userid',$userid)->where('nt.read_at IS NULL',null,false)
      ->order_by('n.created_at','DESC')->limit($limit)->get()->result_array();
  }

  public function mark_read(int $target_id, int $userid){
    $this->db->update('notification_targets', ['read_at'=>date('Y-m-d H:i:s')],
                      ['id'=>$target_id,'userid'=>$userid]);
  }
  
  
  
 public function run(){
    // process 50 tasks sekali jalan
    $jobs = $this->db->limit(50)->get_where('notification_queue',['status'=>'queued'])->result_array();
    $now = date('Y-m-d H:i:s');

    foreach($jobs as $job){
      $ok = true; $err = null;
      $payload = json_decode($job['payload_json'], true) ?: [];
      try{
        if ($job['channel']==='email'){
          $ok = $this->send_email($job['notif_id'], $payload);
        } else if ($job['channel']==='wa'){
          $ok = $this->send_wa($job['notif_id'], $payload); // implement sesuai gateway mu
        } else { // inapp no-op, ditandai sent agar tak diproses lagi
          $ok = true;
        }
      }catch(Throwable $e){ $ok=false; $err=$e->getMessage(); }

      $this->db->update('notification_queue',[
        'status'=>$ok?'sent':'error',
        'last_error'=>$err, 'updated_at'=>$now
      ],['id'=>$job['id']]);

      // catat delivered_at ke semua target notif ini (opsional)
      if ($ok){
        $this->db->update('notification_targets', ['delivered_at'=>$now], ['notif_id'=>$job['notif_id']]);
      }
    }
    echo "OK\n";
  }

  private function send_email(int $notif_id, array $p): bool {
    // ambil recipients dari notification_targets
    $targets = $this->db->select('u.email')
      ->from('notification_targets nt')
      ->join('ci_users u','u.userid=nt.userid')
      ->where('nt.notif_id',$notif_id)->get()->result_array();
    $emails = array_values(array_filter(array_map(fn($r)=> $r['email']??'', $targets)));

    if (!$emails) return true; // tidak ada email; anggap beres

    $this->load->library('email');
	      $config = array(
    'protocol'    => 'smtp',
    'smtp_host'   => 'ssl://mail.lbebanten.com',
    'smtp_port'   => 465,
    'smtp_user'   => 'info@lbebanten.com',  // alamat email lengkap
    'smtp_pass'   => 'jasacom.n3t',                // pastikan benar
    'mailtype'    => 'html',
    'charset'     => 'utf-8',
    'newline'     => "\r\n",
    'smtp_timeout'=> 10,
    'crlf'        => "\r\n",
    'wordwrap'    => TRUE
);
   
    foreach ($emails as $to){
      $this->email->set_header('Content-Type', 'text/html');
      $this->email->initialize($config);
      $this->email->clear(true);
      $this->email->from('info@lbebanten.com', 'SICOAL - LBE Banten');
      $this->email->to($to);
      $this->email->subject($p['subject'] ?? 'Notifikasi');
      $data['pesan']=$p['body'];
      $psn = $this->load->view('layout/mail', $data, true);
      
      
      $this->email->message(($psn ?? ''));
      if (!$this->email->send(false)) {
        log_message('error','Email gagal: '.$this->email->print_debugger(['headers']));
        return false;
      }
    }
    return true;
  }
}
