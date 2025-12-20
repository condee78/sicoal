<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Notifier extends CI_Controller {
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
      $this->email->message(($p['body'] ?? ''));
      if (!$this->email->send(false)) {
        log_message('error','Email gagal: '.$this->email->print_debugger(['headers']));
        return false;
      }
    }
    return true;
  }

  private function send_wa(int $notif_id, array $p): bool {
    // Integrasikan ke gateway WA kamu (HTTP POST ke webhook).
    // return true jika sukses; false jika gagal.
    return true;
  }
}
