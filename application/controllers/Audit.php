<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Audit extends CI_Controller {
  public function __construct(){
    parent::__construct();
    $this->load->model(['Audit_model'=>'audit','User_model'=>'users']);
    $this->load->helper('rbac');
     $this->load->model('Period_model');
    if (!(is_admin_lbe() || is_superadmin())) show_error('Unauthorized', 401);
  }

  public function index(){
    $filter = [
  'entity'    => $this->input->get('entity'),
  'entity_id' => $this->input->get('entity_id'),   // <— TAMBAH
  'action'    => $this->input->get('action'),
  'actor'     => $this->input->get('actor'),
  'from'      => $this->input->get('from'),
  'to'        => $this->input->get('to')
];

    $data['filter'] = $filter;
    $data['rows'] = $this->audit->list($filter, 500);
    $data['users'] = $this->db->select('userid,username')->from('ci_users')->order_by('username')->get()->result_array();
    $this->load->view('audit/index', $data);
  }
  
}
