<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Periods extends CI_Controller {
  public function __construct(){
    parent::__construct();
              $this->load->model('Period_model');

    $this->load->helper('rbac');
    if (!(is_superadmin() || is_admin_lbe())) show_error('Unauthorized', 401);
  }

  public function index(){
    $data['rows'] = $this->Period_model->all();
    $this->load->view('periods/index', $data);
  }

  public function create(){
    $y = (int)$this->input->post('year');
    if (!$y) return redirect('Period_model');
    $this->Period_model->create($y);
    redirect('periods');
  }

  public function set_active($year){
    $this->Period_model->set_active((int)$year);
    redirect('periods');
  }

  public function lock($year){
    $this->Period_model->set_lock((int)$year, 1);
    redirect('periods');
  }

  public function unlock($year){
    $this->Period_model->set_lock((int)$year, 0);
    redirect('periods');
  }
}
