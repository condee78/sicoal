<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Scores extends CI_Controller {
  public function __construct(){
    parent::__construct();
    $this->load->model(['Score_model' => 'scores', 'Rekanan_model' => 'rekanan']);
    $this->load->helper('rbac');
     $this->load->library('Filter_mode','filter');

     $this->load->model('Period_model');

  }

  public function index($year = null){
          $this->filter_mode->set_from_request();

          $role = role_code(); // 'vendor'|'staff'|'admin'|'super'

    $year = $year ?: (int)date('Y');
      //  $shipper = $this->input->get('rekanan_kode'); // optional filter
        
       $shipper = ($role=='vendor') ? $this->rekanan->kode_by_user($this->session->userdata('userid')) : $this->input->get('rekanan_kode');


    $data['shippers']   = $this->rekanan->all();
    $data['filter']     = ['rekanan_kode'=>$shipper];

/*    $data['ranking']    = $this->scores->ranking($shipper);
    $data['trend']      = $this->scores->trend_monthly($shipper);
    $data['breakdown']  = $this->scores->breakdown($shipper);
 */   
 
    $data['rows']      = $this->scores->summary_by_shipper($shipper);
    // MODE PERIODE RANGE
    $st = $this->filter_mode->get();
    $data['filter']=$st;
    $this->load->view('scores/index', $data);
  }
  
  public function shipper($kode){
          $this->filter_mode->set_from_request();

    $y  = $this->input->get('y') ?: date('Y');
    $df = $this->input->get('date_from') ?: ($y.'-01-01');
    $dt = $this->input->get('date_to')   ?: ($y.'-12-31');

    $rows = $this->scores->shipments_of_shipper($kode);
   
    $nama = '';
    if (!empty($rows)) $nama = $rows[0]['nama_perusahaan'];

    $data = [
      'rekanan_kode' => $kode,
      'nama_perusahaan' => $nama,
      'date_from' => $df,
      'date_to'   => $dt,
      'rows'      => $rows
    ];
        // MODE PERIODE RANGE

    $st = $this->filter_mode->get();
    $data['filter']=$st;
  
    $this->load->view('scores/shipper_detail', $data);
  }
}
