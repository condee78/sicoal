<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Imports extends CI_Controller {

  public function __construct(){
    parent::__construct();
    $this->load->model(['Shipment_model'=>'shipments','Rekanan_model'=>'rekanan']);
    $this->load->library('ExcelReader');
    $this->load->helper(['rbac','url']);
         $this->load->model('Period_model');

    if (!(is_admin_lbe() || is_superadmin())) show_error('Unauthorized', 401);
  }

  // STEP 1: upload -> parse -> preview
  public function index(){
    if ($this->input->method() === 'post'){
      $mode = $this->input->post('mode') === 'revise' ? 'revise' : 'plan';
      if (empty($_FILES['excel']['tmp_name'])){
        $this->session->set_flashdata('err','File belum dipilih.');
        return redirect('shipments/import');
      }

      try {
        $parsed = $this->excelreader->read_assoc($_FILES['excel']['tmp_name'], 1);
        $rows   = $parsed['rows'];


        // VALIDASI
        $report = ($mode==='revise')
          ? $this->validate_revise($rows)
          : $this->validate_plan($rows);

        // simpan ke file temp (biar aman untuk data besar)
        $tmpdir = FCPATH.'uploads/tmp_imports/';
        if (!is_dir($tmpdir)) @mkdir($tmpdir, 0775, true);
        $token  = bin2hex(random_bytes(8));
        $tmpfile = $tmpdir.$token.'.json';
        file_put_contents($tmpfile, json_encode([
          'mode'=>$mode, 'rows'=>$rows, 'report'=>$report,
          'time'=>date('c'), 'user'=>$this->session->userdata('userid')
        ]));

        // render preview
        $data = [
          'mode'=>$mode,
          'token'=>$token,
          'report'=>$report,
          'sample'=>array_slice($rows, 0, 50), // tampilkan 50 baris pertama
          'total'=>count($rows),
        ];
        return $this->load->view('shipments/import_preview', $data);
      } catch (\Throwable $e){
        $this->session->set_flashdata('err','Gagal membaca Excel: '.$e->getMessage());
        return redirect('shipments/import');
      }
    }
    // GET -> tampil form upload
    $this->load->view('shipments/import');
  }

  // STEP 2: commit ke DB
  public function commit(){
    if ($this->input->method() !== 'post') show_404();
    if (!(is_admin_lbe() || is_superadmin())) show_error('Unauthorized', 401);

    $token = $this->input->post('token');
    $only_valid = (bool)$this->input->post('only_valid');
    $tmpfile = FCPATH.'uploads/tmp_imports/'.$token.'.json';
    if (!file_exists($tmpfile)){
      $this->session->set_flashdata('err','Session import sudah kedaluwarsa / tidak ditemukan.');
      return redirect('shipments/import');
    }

    $payload = json_decode(file_get_contents($tmpfile), true);
    $mode    = $payload['mode'];
    $rows    = $payload['rows'];
    $report  = $payload['report'];

    // ambil hanya baris valid kalau diminta
    if ($only_valid){
      $valid_indexes = array_keys(array_filter($report['rows'], fn($r) => empty($r['errors'])));
      $rows = array_values(array_intersect_key($rows, array_flip($valid_indexes)));
    }

    if ($mode==='revise'){
      $res = $this->shipments->import_revise($rows, $this->session->userdata('userid'));
      $this->session->set_flashdata('ok', "Revisi OK: updated {$res['updated']}, skipped {$res['skipped']}");
    } else {
      $res = $this->shipments->import_plan($rows, $this->session->userdata('userid'));
      $this->session->set_flashdata('ok', "Plan OK: inserted {$res['inserted']}, updated {$res['updated']}, skipped {$res['skipped']}");
    }

$this->load->model('Audit_model','audit');

  if ($mode==='revise'){
    $res = $this->shipments->import_revise($rows, $this->session->userdata('userid'));
    $this->audit->log('import', null, 'import_revise', [], [], [
      'counts'=>$res, 'mode'=>$mode, 'token'=>$token
    ]);
  } else {
    $res = $this->shipments->import_plan($rows, $this->session->userdata('userid'));
    $this->audit->log('import', null, 'import_plan', [], [], [
      'counts'=>$res, 'mode'=>$mode, 'token'=>$token
    ]);
  }
    // hapus temp
    @unlink($tmpfile);
    redirect('shipments/import');
  }

  // ===== VALIDASI =====

  private function validate_plan(array $rows){
    $out = ['rows'=>[], 'summary'=>['errors'=>0,'warnings'=>0,'valid'=>0,'total'=>count($rows)]];
    // cache kode rekanan
    $codes = $this->db->select('kode')->get('m_rekanan')->result_array();
    $valid_codes = array_column($codes,'kode');
 
    foreach ($rows as $i=>$r){
      $errs=[]; $warn=[];
      $rk = $r['rekanan_kode'] ?? null;
      $sn = $r['shipment_no'] ?? null;

      if (!$rk) $errs[]='Shipper Code (B$) kosong';
      if ($rk && !in_array($rk, $valid_codes, true)) $errs[]='Shipper Code tidak terdaftar';
      if (!$sn) $errs[]='Shipment No (C$) kosong';

      // tanggal
      if (!empty($r['eta_loading_port']) && !$this->is_date($r['eta_loading_port'])) $warn[]='ETA Loading Port bukan tanggal valid';
      // volume
      if (isset($r['volume_plan_mt']) && !is_numeric($r['volume_plan_mt'])) $warn[]='Volume Plan bukan angka';

      $out['rows'][$i] = ['errors'=>$errs, 'warnings'=>$warn];
      if ($errs){ $out['summary']['errors'] += count($errs); }
      if ($warn){ $out['summary']['warnings'] += count($warn); }
      if (!$errs){ $out['summary']['valid']++; }
    }
    return $out;
  }

  private function validate_revise(array $rows){
    $out = ['rows'=>[], 'summary'=>['errors'=>0,'warnings'=>0,'valid'=>0,'total'=>count($rows)]];

    // cache master rekanan
    $codes = $this->db->select('kode')->get('m_rekanan')->result_array();
    $valid_codes = array_column($codes,'kode');

    foreach ($rows as $i=>$r){
      $errs=[]; $warn=[];
      $rk = $r['rekanan_kode'] ?? null;
      $sn = $r['shipment_no'] ?? null;

      if (!$rk) $errs[]='Shipper Code (B$) kosong';
      if ($rk && !in_array($rk, $valid_codes, true)) $errs[]='Shipper Code tidak terdaftar';
      if (!$sn) $errs[]='Shipment No (C$) kosong';

      // cek eksistensi + status Completed
      if ($rk && $sn){
        $exist = $this->db->get_where('shipments', ['rekanan_kode'=>$rk,'shipment_no'=>$sn])->row_array();
        if (!$exist) $errs[]='Shipment belum ada (tidak bisa direvisi)';
        if ($exist && (int)$exist['shipment_completed']===1) $errs[]='Shipment sudah Completed (tidak bisa direvisi)';
      }

      // format tanggal angka
      foreach (['eta_lbe','commence_disch','complete_disch'] as $dk){
        if (isset($r[$dk]) && $r[$dk]!=='' && !$this->is_date($r[$dk]))
          $warn[]="$dk bukan tanggal valid";
      }
      if (isset($r['volume_plan_mt']) && $r['volume_plan_mt']!=='' && !is_numeric($r['volume_plan_mt']))
        $warn[]='Volume Plan bukan angka';

      // status value
      if (isset($r['shipment_status']) && $r['shipment_status']!==''){
        $allowed = ['Completed','On process','Not Completed'];
        if (!in_array($r['shipment_status'], $allowed, true))
          $warn[]='Shipment Status bukan nilai standar';
      }

      $out['rows'][$i] = ['errors'=>$errs, 'warnings'=>$warn];
      if ($errs){ $out['summary']['errors'] += count($errs); }
      if ($warn){ $out['summary']['warnings'] += count($warn); }
      if (!$errs){ $out['summary']['valid']++; }
    }
    return $out;
  }

  private function is_date($v){
    if (!$v) return false;
    return (bool)strtotime($v);
  }
}
