<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Files extends CI_Controller {

  private $allowed_doc_types = [
    // Vendor
    'COA' => 'Certificate of Analysis',
    'AWB' => 'Air Waybill',
    'OTHER_VENDOR' => 'Other (Vendor)',
    // Staff/Admin
    'BA_BM' => 'Berita Acara Bongkar Muat',
    'LAB_RESULT' => 'Lab Result / As Received',
    'INVOICE_SC' => 'Invoice Softcopy',
    'INVOICE_HC' => 'Invoice Hardcopy',
    'AH_PROOF' => 'Proof for AH (Completion)'
  ];

  public function __construct(){
    parent::__construct();
    $this->load->model(['Shipment_model'=>'shipments','File_model'=>'files','Rekanan_model'=>'rekanan']);
    $this->load->helper(['url','rbac']);
         $this->load->model('Period_model');

  }

  public function upload($shipment_id){
    $role = role_code();
    $shipment = $this->shipments->find($shipment_id, $role);
    if (!$shipment) show_404();

    // Vendor hanya boleh file untuk baris miliknya
    if ($role==='vendor'){
      $kode_vendor = $this->rekanan->kode_by_user($this->session->userdata('userid'));
      if (!$kode_vendor || $kode_vendor !== $shipment['rekanan_kode']){
        show_error('Unauthorized', 401);
      }
    }

    $doc_type = $this->input->post('doc_type');
    if (!isset($this->allowed_doc_types[$doc_type]) || !can_upload_doc($doc_type, $role)){
      show_error('Doc type not allowed for your role', 403);
    }

  $cfg = [
  'upload_path'      => FCPATH.'uploads/shipments/'.$shipment_id.'/',
  'allowed_types'    => 'pdf|jpg|jpeg|png|doc|docx|xls|xlsx|csv', // pakai PIPE
  'encrypt_name'     => TRUE,
  'max_size'         => 10240,     // KB
  'detect_mime'      => TRUE,      // pakai Fileinfo
  'file_ext_tolower' => TRUE,      // konsistenkan ekstensi
];
    if (!is_dir($cfg['upload_path'])) mkdir($cfg['upload_path'], 0775, true);
    $this->load->library('upload', $cfg);

    if (!$this->upload->do_upload('file')){
    
      $this->session->set_flashdata('err', $this->upload->display_errors('',''));
      return redirect('shipments/detail/'.$shipment_id);
    }

    $up = $this->upload->data();
   $fileId= $this->files->save_meta($shipment_id, $doc_type, $up, $this->session->userdata('userid'));
    $this->session->set_flashdata('ok', 'File uploaded.');
    
     $this->load->model('Audit_model','audit');
 // $fileId = $this->audit->save_meta($shipment_id, $doc_type, $up, $this->session->userdata('userid'));
  $this->audit->log('file', $fileId, 'upload', [], [], [
    'shipment_id'=>$shipment_id,
    'doc_type'=>$doc_type,
    'name'=>$up['client_name'] ?? $up['orig_name'],
    'path'=>$up['full_path'], 'mime'=>$up['file_type'], 'size_kb'=>$up['file_size']
  ]);
  
    redirect('shipments/detail/'.$shipment_id);
  }

  public function download($file_id){
    $role = role_code();
    $row = $this->files->find($file_id);
    if (!$row) show_404();

    $shipment = $this->shipments->find($row['shipment_id'], $role);
    if (!$shipment) show_error('Unauthorized', 401);

    // Vendor hanya boleh baris miliknya
    if ($role==='vendor'){
      $kode_vendor = $this->rekanan->kode_by_user($this->session->userdata('userid'));
      if (!$kode_vendor || $kode_vendor !== $shipment['rekanan_kode']){
        show_error('Unauthorized', 401);
      }
    }

    $path = $row['server_path'];
    if (!file_exists($path)) show_error('File not found', 404);

    $this->load->helper('download');
    $data = file_get_contents($path);
    force_download($row['original_name'], $data);
  }

  public function delete($file_id){
    $role = role_code();
    if (!is_admin_lbe() && !is_superadmin() && !is_staff_lbe()){
      show_error('Unauthorized', 401);
    }
    $row = $this->files->find($file_id);
    if (!$row) show_404();
    $this->files->delete($file_id);
    $this->session->set_flashdata('ok', 'File deleted');
     $this->load->model('Audit_model','audit');
  $this->audit->log('file', $file_id, 'delete', [], [], []);
    redirect('shipments/detail/'.$row['shipment_id']);
  }
}
