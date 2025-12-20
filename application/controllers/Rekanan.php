<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Rekanan extends CI_Controller {
  public function __construct(){
    parent::__construct();
         $this->load->model('Period_model');

    require_login();
    if (!(is_superadmin() || is_admin_lbe())) show_error('Unauthorized', 401);
    $this->load->model('Rekanan_model','rekanan');
  }

  public function index(){
    $q = $this->input->get('q');
    $data['q'] = $q;
    $data['rows'] = $this->rekanan->all($q);
    $this->load->view('rekanan/index',$data);
  }

  public function create(){
    $data = ['mode'=>'create','row'=>[
      'kode'=>'','nama_perusahaan'=>'','alamat_perusahaan'=>'',
      'email'=>'','telp'=>'','wa'=>'','ttd_nama'=>'','ttd_jabatan'=>''
    ]];
    $this->load->view('rekanan/form',$data);
  }

  public function edit($id){
    $row = $this->rekanan->find((int)$id);
    if (!$row) show_404();
    $data = ['mode'=>'edit','row'=>$row];
    $this->load->view('rekanan/form',$data);
  }

  public function save($id=null){
    try{
      $in = $this->input->post(null,true);
      $rid = $this->rekanan->save_and_sync($id? (int)$id : null, $in, $this->session->userdata('userid'));
      $this->session->set_flashdata('ok','Data shipper berhasil disimpan.');
      redirect('rekanan');
    }catch(\Throwable $e){
      $this->session->set_flashdata('err',$e->getMessage());
      if ($id) return redirect('rekanan/edit/'.$id);
      return redirect('rekanan/create');
    }
  }

  public function delete($id){
    try{
      $this->rekanan->delete_with_guard((int)$id);
      $this->session->set_flashdata('ok','Data shipper dihapus.');
    }catch(\Throwable $e){
      $this->session->set_flashdata('err',$e->getMessage());
    }
    redirect('rekanan');
  }
}
