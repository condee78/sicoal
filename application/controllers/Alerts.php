// application/controllers/Alerts.php
<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Alerts extends CI_Controller {

  public function __construct(){
    parent::__construct();
    $this->load->model(['Shipment_model'=>'shipments','User_model'=>'users','Rekanan_model'=>'rekanan']);
    $this->load->library('email');
    date_default_timezone_set('Asia/Jakarta');
  }

  public function run(){
    // 1) Ambil penerima (Admin LBE)
    $admins = $this->users->emails_by_group(1); // groupid=1 admin LBE
    if (!$admins) { echo "No admin recipients.\n"; }

    // 2) Query due_7days & delayed (belum completed)
    $due = $this->db->query("
      SELECT s.*, r.nama_perusahaan
      FROM shipments s
      JOIN m_rekanan r ON r.kode=s.rekanan_kode
      WHERE s.shipment_completed=0
        AND s.eta_lbe BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY)
      ORDER BY s.eta_lbe ASC
    ")->result_array();

    $delayed = $this->db->query("
      SELECT s.*, r.nama_perusahaan
      FROM shipments s
      JOIN m_rekanan r ON r.kode=s.rekanan_kode
      WHERE s.shipment_completed=0
        AND s.eta_lbe < NOW()
      ORDER BY s.eta_lbe ASC
    ")->result_array();

    // 3) Kirim email ringkas ke Admin LBE
    if ($admins){
      $subject = "[CoalSM] Alerts: Due in 7d (".count($due).") / Delayed (".count($delayed).")";
      $html = $this->render_admin_email($due, $delayed);
      $this->send_mail($admins, $subject, $html);
    }

    // 4) Opsional: email ke Vendor masing-masing (ringkasan milik mereka)
    $byVendor = [];
    foreach (array_merge($due,$delayed) as $row){
      $byVendor[$row['rekanan_kode']][] = $row;
    }
    foreach ($byVendor as $kode=>$rows){
      $vendor = $this->db->get_where('m_rekanan',['kode'=>$kode])->row_array();
      if (!empty($vendor['email'])){
        $subject = "[CoalSM] Reminder Shipment — {$vendor['nama_perusahaan']}";
        $html = $this->render_vendor_email($vendor, $rows);
        $this->send_mail([$vendor['email']], $subject, $html);
      }
      // WA stub (opsional)
      // if (!empty($vendor['wa'])) { $this->send_whatsapp_stub($vendor['wa'], $subject); }
    }

    echo "Alerts sent. Due: ".count($due).", Delayed: ".count($delayed).PHP_EOL;
  }

  private function render_admin_email($due, $delayed){
    $rowToTr = function($r){
      $eta = $r['eta_lbe'] ? date('Y-m-d H:i', strtotime($r['eta_lbe'])) : '-';
      return "<tr>
        <td>{$r['shipment_no']}</td>
        <td>".htmlentities($r['nama_perusahaan'])."</td>
        <td>".htmlentities($r['nominated_vessel'] ?: '-')."</td>
        <td>{$eta}</td>
        <td>".($r['shipment_status'] ?: '-')."</td>
      </tr>";
    };
    $tbl = function($title, $rows) use ($rowToTr){
      $trs = $rows ? implode('', array_map($rowToTr,$rows)) : '<tr><td colspan="5">Tidak ada.</td></tr>';
      return "<h3 style='margin:10px 0'>{$title}</h3>
      <table border='1' cellspacing='0' cellpadding='6' style='border-collapse:collapse;font-family:Arial;font-size:12px'>
        <thead><tr><th>Shipment No</th><th>Shipper</th><th>Vessel</th><th>ETA LBE</th><th>Status</th></tr></thead>
        <tbody>{$trs}</tbody>
      </table>";
    };
    return "<div>{$tbl('Due in 7 days',$due)}<br>{$tbl('Delayed',$delayed)}</div>";
  }

  private function render_vendor_email($vendor, $rows){
    $title = "Shipment Reminder — ".htmlentities($vendor['nama_perusahaan']);
    $rowToTr = function($r){
      $eta = $r['eta_lbe'] ? date('Y-m-d H:i', strtotime($r['eta_lbe'])) : '-';
      return "<tr>
        <td>{$r['shipment_no']}</td>
        <td>".htmlentities($r['nominated_vessel'] ?: '-')."</td>
        <td>{$eta}</td>
        <td>".($r['shipment_status'] ?: '-')."</td>
      </tr>";
    };
    $trs = $rows ? implode('', array_map($rowToTr,$rows)) : '<tr><td colspan="4">Tidak ada.</td></tr>';
    return "<h3>{$title}</h3>
    <p>Mohon perhatian jadwal berikut:</p>
    <table border='1' cellspacing='0' cellpadding='6' style='border-collapse:collapse;font-family:Arial;font-size:12px'>
      <thead><tr><th>Shipment No</th><th>Vessel</th><th>ETA LBE</th><th>Status</th></tr></thead>
      <tbody>{$trs}</tbody>
    </table>";
  }

  private function send_mail(array $to, $subject, $html){
    // Pastikan konfigurasi Email CI3 sudah di-setup (SMTP)
    $this->email->clear();
    $this->email->from('no-reply@yourdomain.com', 'CoalSM');
    $this->email->to($to);
    $this->email->subject($subject);
    $this->email->message($html);
    @$this->email->send(false);
  }

  private function send_whatsapp_stub($phone_e164, $message){
    // Stub: implementasikan call ke provider (Twilio/Meta/alternatif) di sini.
    log_message('info', "WA to {$phone_e164}: {$message}");
  }
}
