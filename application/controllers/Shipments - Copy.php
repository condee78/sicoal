<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Shipments extends CI_Controller {
  public function __construct(){
    parent::__construct();
    $this->load->model('Shipment_model','shipments');
    $this->load->model('Rekanan_model','rekanan');
    $this->load->model('File_model','File_model');
    $this->load->helper(['rbac','url','form','my_helper']);
    $this->load->model('Docreq_model');
     $this->load->model('Period_model');
     $this->load->library('Filter_mode','filter');
  require_login();                 // <— penting

  }

  public function index(){
        $this->filter_mode->set_from_request();

    $filter = $this->input->get();
    $role = role_code(); // 'vendor'|'staff'|'admin'|'super'
    $rekanan_kode = ($role=='vendor') ? $this->rekanan->kode_by_user($this->session->userdata('userid')) : null;
    

    
    // simpan preferensi view di session
  $v = $this->input->get('view');
  if ($v && in_array($v, ['cards','grid'], true)) {
    $this->session->set_userdata('shipments_view', $v);
  }
  $viewPref = $this->session->userdata('shipments_view') ?: 'cards';
  
    $data['filter'] = $filter;
    $data['shippers'] = $this->rekanan->all();
    $data['rows'] = $this->shipments->list($filter, $role, $rekanan_kode);
    $filter = $this->input->get(); // tahun, status, dsb (optional)
    $rows = $this->shipments->list_v($filter, $role,$rekanan_kode); // gunakan view shipments_v untuk metrik K/O/R/S/T/V

    $data = [
    'rows'     => $rows,
    'filter'   => $filter,
    'viewPref' => $viewPref,
    'role'     => $role,
  ];
  
  $st = $this->filter_mode->get();
  $data['filter']=$st;

  if ($viewPref === 'grid') {
    $this->load->view('shipments/list_grid', $data);
  } else {
    $this->load->view('shipments/list', $data); // ini view-mu yang sekarang (list lama)
  }
    
   // $this->load->view('shipments/list', $data);
  }

  public function edit($id){
    $this->filter_mode->set_from_request();
    $st = $this->filter_mode->get();
    
    $role = role_code();
    $data['mode'] = 'edit';
    $data['data'] = $this->shipments->find($id, $role);
    $data['shippers'] = $this->rekanan->all();
    if ($role=='vendor') $data['current_shipper'] = $this->rekanan->by_user($this->session->userdata('userid'));
    $this->load->view('shipments/form',$data);
  }

  public function create(){
    $role = role_code();
    $data['mode'] = 'create';
    $data['data'] = [];
    $data['shippers'] = $this->rekanan->all();
    if ($role=='vendor') $data['current_shipper'] = $this->rekanan->by_user($this->session->userdata('userid'));
    $this->load->view('shipments/form',$data);
  }

  public function save($id=null){
    $role = role_code();
    $payload = $this->input->post();
    $this->shipments->save_role_based($id, $payload, $role, $this->session->userdata('userid'));
    redirect('shipments');
  }

  public function import(){
    // tampilkan form upload + proses (admin LBE / super)
    // (sesuaikan dengan ExcelReader yang sudah dibahas sebelumnya)
    echo "Import page (implement sesuai kebutuhan)";
  }

 public function export(){
  $filter = $this->input->get();
  $role   = role_code();
  $this->load->model('Rekanan_model','rekanan');
  $rekanan_kode = ($role=='vendor') ? $this->rekanan->kode_by_user($this->session->userdata('userid')) : null;

  $rows = $this->shipments->export_query($filter, $role, $rekanan_kode);

  // Kolom per role
  if ($role==='vendor'){
    $headers = ['No','Shipment No','Vessel','ETA LBE','Plan (MT)','Actual (MT)','Status'];
    $extract = function($row){
      return [
        $row['shipment_no'] ?? '',
        $row['nominated_vessel'] ?? '',
        $row['eta_lbe'] ? date('Y-m-d H:i', strtotime($row['eta_lbe'])) : '',
        $row['volume_plan_mt'] ?? '',
        $row['volume_actual_mt'] ?? '',
        $row['shipment_status'] ?? ''
      ];
    };
  } else {
    $headers = [
      'Shipper','Shipment No','Vessel','Loading Port','ETA Loading Port','ETA LBE',
      'Plan (MT)','Actual (MT)','K (Loading Days)','O (N-M)','R (P-N)','S (Q-P)','T (L/S)','V (U-L)',
      'Received Status (calc)','Shipment Status','Created','Updated','Status'
    ];
    
    

    $extract = function($row){
        $status=getShipmentStatus($row);
      return [
        $row['nama_perusahaan'] ?? '',
        $row['shipment_no'] ?? '',
        
        $row['nominated_vessel'] ?? '',
        $row['loading_port'] ?? '',
        $row['eta_loading_port'] ? date('Y-m-d', strtotime($row['eta_loading_port'])) : '',
        $row['eta_lbe'] ? date('Y-m-d H:i', strtotime($row['eta_lbe'])) : '',
        $row['volume_plan_mt'] ?? '',
        $row['volume_actual_mt'] ?? '',
        $row['k_total_loading_days'] ?? '',
        $row['o_diff_days'] ?? '',
        $row['r_diff_days'] ?? '',
        $row['s_diff_days'] ?? '',
        $row['t_throughput'] ?? '',
        $row['v_variance'] ?? '',
        $row['received_status_calc'] ?? '',
        $row['shipment_status'] ?? '',
        $row['created_at'] ?? '',
        $row['updated_at'] ?? '',
        $status['last_completed_label']
        
      ];
    };
  }

  // Build spreadsheet (PhpSpreadsheet)
  $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
  $sheet = $spreadsheet->getActiveSheet()->setTitle('Shipments');
  // header
  $c=1; $sheet->setCellValueByColumnAndRow($c++,1,'#'); foreach($headers as $h){ $sheet->setCellValueByColumnAndRow($c++,1,$h); }
  // rows
  $r=2; $i=1;
  foreach($rows as $row){
    $c=1; $sheet->setCellValueByColumnAndRow($c++, $r, $i++);
    foreach($extract($row) as $val){ $sheet->setCellValueByColumnAndRow($c++, $r, $val); }
    $r++;
  }
  foreach(range('A','Z') as $colID){ $sheet->getColumnDimension($colID)->setAutoSize(true); }

  $fname = 'shipments_export_'.date('Ymd_His').'.xlsx';
  header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
  header('Content-Disposition: attachment; filename="'.$fname.'"');
  header('Cache-Control: max-age=0');
  $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
  $writer->save('php://output'); exit;
}

  public function detail($id){
    $role = role_code();
    $data['data'] = $this->shipments->detail_v($id, $role); // dari view shipments_v
    $this->load->view('shipments/detail',$data); // buat view sederhana jika diperlukan
  }
  
  
  // application/controllers/Shipments.php
public function check_unique(){
  require_login();
  $this->output->set_content_type('application/json');

  $shipment_no  = trim($this->input->get('shipment_no') ?? '');
  $rekanan_kode = trim($this->input->get('rekanan_kode') ?? '');
  $exclude_id   = (int)($this->input->get('exclude_id') ?? 0); // saat edit

  if ($shipment_no==='' || $rekanan_kode==='') {
    return $this->output->set_output(json_encode(['ok'=>false,'msg'=>'Param kosong']));
  }

  $this->db->from('shipments')
           ->where('shipment_no', $shipment_no)
           ->where('rekanan_kode', $rekanan_kode);
  if ($exclude_id>0) $this->db->where('id <>', $exclude_id);

  $exists = $this->db->count_all_results() > 0;

  return $this->output->set_output(json_encode(['ok'=>true,'exists'=>$exists]));
}
}
