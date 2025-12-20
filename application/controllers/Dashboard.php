<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {
  public function __construct(){
    parent::__construct();
    $this->load->model('Shipment_model','shipments');
    $this->load->helper('rbac');
     $this->load->model('Period_model');
     $this->load->library('Filter_mode','filter');

       require_login();                 // <— penting

    // pastikan user login
  }

  public function index(){
        if (is_admin_lbe() || is_superadmin()){
         $this->filter_mode->set_from_request();
            $st = $this->filter_mode->get();
//Array ( [mode] => period [period_y] => 2025 [date_from] => 2025-01-01 [date_to] => 2025-12-31 ) WHERE s.period_year = ?

     //$activeY = $this->Period_model->active_year();
     $activeY=$y=date('Y');
     
    if($st['mode']=='period')
     $activeY=$y   = $st['period_y'];
     
        $df  = $this->input->get('date_from') ?: ($y.'-01-01');
        $dt  = $this->input->get('date_to')   ?: ($y.'-12-31');

        $data = [
          'date_from' => $df,
          'date_to'   => $dt,
          'year'      => (int)substr($df,0,4),
        ];
        
    $data['cards'] = $this->shipments->kpi_cards($activeY);
    $data['monthly'] = $this->shipments->plan_actual_monthly((int)date('Y'));
    $data['by_shipper'] = $this->shipments->by_shipper_counts($activeY);
    $data['performance'] = $this->shipments->performance_table($activeY);
        
        $data['filter']=$st;

       // $this->load->view('dashboard/index', $data);
    $this->load->view('dashboard/index', $data);
        }else{
            redirect('shipments');
        }
  }
  
   

    // API untuk chart
    public function period_data(){
        $this->output->set_content_type('application/json');

        $df = $this->input->get('date_from') ?: date('Y-01-01');
        $dt = $this->input->get('date_to')   ?: date('Y-12-31');

        try{
          $sum = $this->shipments->period_summary($df, $dt);
          return $this->output->set_output(json_encode(['ok'=>true,'data'=>$sum]));
        }catch(Throwable $e){
          return $this->output->set_output(json_encode(['ok'=>false,'msg'=>$e->getMessage()]));
        }
    }
  
}
