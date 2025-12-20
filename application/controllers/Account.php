<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Account extends CI_Controller {
  public function __construct(){
    parent::__construct();
    require_login(); // pastikan user login
    $this->load->model(['User_model'=>'users','Auth_token_model'=>'auth_tokens','Audit_model'=>'audit']);
    $this->load->library('form_validation');
    $this->load->helper(['url','rbac']);
  }

  public function password(){
    if ($this->input->method()==='post'){
      $this->form_validation->set_rules('current','Password sekarang','required');
      $this->form_validation->set_rules('password','Password baru','required|min_length[8]');
      $this->form_validation->set_rules('confirm','Konfirmasi password','required|matches[password]');

      if (!$this->form_validation->run()){
        return $this->load->view('account/password', ['error'=>validation_errors(' ',' ')]);
      }

      $uid   = (int)$this->session->userdata('userid');
      $curr  = $this->input->post('current');
      $new   = $this->input->post('password');

      // Ambil user & verifikasi password lama
      $user = $this->db->get_where('ci_users',['userid'=>$uid])->row_array();
      if (!$user || !$this->users->verify_password_and_upgrade($user, $curr)){
        return $this->load->view('account/password', ['error'=>'Password sekarang salah.']);
      }
      if (hash_equals($curr, $new)){
        return $this->load->view('account/password', ['error'=>'Password baru tidak boleh sama dengan password sekarang.']);
      }

      // Set password baru + bersihkan token reset
      $this->users->set_new_password_and_clear_token($uid, $new);

      // Hapus semua "remember me" (logout semua device lain)
      $this->auth_tokens->delete_all_by_user($uid);

      // Audit (tanpa menyimpan password)
      $this->audit->log('account', $uid, 'password_change', [], [], ['via'=>'self_service']);

      // Optional: tetap biarkan sesi saat ini, hanya perangkat lain yang logout
      $this->session->set_flashdata('ok','Password berhasil diubah. Perangkat lain telah keluar.');
      return redirect('shipments');
    }

    $this->load->view('account/password');
  }
}
