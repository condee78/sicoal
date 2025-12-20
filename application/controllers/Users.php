<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Users extends CI_Controller {
  public function __construct(){
    parent::__construct();
    require_login();
    $this->load->model('Users_model','users');
  }

  private function require_super_or_admin(){
    // super admin & admin LBE boleh akses listing,
    // tapi pembatasan: hanya super admin boleh buat/edit role super admin.
    $g = (int)$this->session->userdata('groupid');
    if (!in_array($g,[0,1])) show_error('Forbidden', 403);
  }

  public function index(){
    $this->require_super_or_admin();
    $role = $this->input->get('role');   // 0/1/2
    $q    = $this->input->get('q');      // search

    $rows = $this->users->list($role, $q);
    $this->load->view('users/index', [
      'rows'=>$rows,
      'role'=>$role,
      'q'=>$q,
      'me_group'=>(int)$this->session->userdata('groupid')
    ]);
  }

  public function create(){
    $this->require_super_or_admin();
    $this->load->view('users/form', [
      'row'=>null,
      'me_group'=>(int)$this->session->userdata('groupid')
    ]);
  }

  public function edit($userid){
    $this->require_super_or_admin();
    $row = $this->users->find((int)$userid);
    if (!$row) show_404();

    // admin LBE tidak boleh mengedit super admin
    if ((int)$this->session->userdata('groupid')===1 && (int)$row['groupid']===0){
      show_error('Forbidden',403);
    }

    $this->load->view('users/form', [
      'row'=>$row,
      'me_group'=>(int)$this->session->userdata('groupid')
    ]);
  }

  public function save(){
    $this->require_super_or_admin();

    $userid   = (int)$this->input->post('userid');
    $username = trim($this->input->post('username'));
    $groupid  = (int)$this->input->post('groupid');
    $nama     = trim($this->input->post('nama'));
    $email    = trim($this->input->post('email'));
    $telp     = trim($this->input->post('telp'));
    $kode     = trim($this->input->post('kode'));
    $password = $this->input->post('password');
    $pass2    = $this->input->post('password2');

    // validasi basic
    if ($username===''){ $this->session->set_flashdata('err','Username wajib.'); redirect($_SERVER['HTTP_REFERER']); }
    if ($nama===''){ $this->session->set_flashdata('err','Nama wajib.'); redirect($_SERVER['HTTP_REFERER']); }
    if ($this->users->username_exists($username, $userid)){
      $this->session->set_flashdata('err','Username sudah dipakai.');
      redirect($_SERVER['HTTP_REFERER']);
    }

    // pembatasan role: admin LBE tidak boleh set super admin
    if ((int)$this->session->userdata('groupid')===1 && $groupid===0){
      $this->session->set_flashdata('err','Admin LBE tidak boleh membuat/menetapkan Super Admin.');
      redirect($_SERVER['HTTP_REFERER']);
    }

    if ($userid==0){
      if (empty($password) || $password!==$pass2){
        $this->session->set_flashdata('err','Password wajib & harus sama.');
        redirect($_SERVER['HTTP_REFERER']);
      }
    } else {
      if (!empty($password) && $password!==$pass2){
        $this->session->set_flashdata('err','Konfirmasi password tidak sama.');
        redirect($_SERVER['HTTP_REFERER']);
      }
    }

    $payload = compact('username','groupid','nama','email','telp','kode');
    if (!empty($password)) $payload['password']=$password;

    $id = $this->users->save($payload, $userid ?: null);
    $this->session->set_flashdata('ok','Data tersimpan.');
    redirect('users');
  }

  public function delete($userid){
    $this->require_super_or_admin();
    $row = $this->users->find((int)$userid);
    if (!$row) show_404();

    // admin LBE tidak boleh hapus super admin
    if ((int)$this->session->userdata('groupid')===1 && (int)$row['groupid']===0){
      show_error('Forbidden',403);
    }
    // cegah hapus diri sendiri
    if ((int)$this->session->userdata('userid') === (int)$userid){
      $this->session->set_flashdata('err','Tidak boleh menghapus akun sendiri.');
      redirect('users');
    }

    $this->users->delete((int)$userid);
    $this->session->set_flashdata('ok','Akun dihapus.');
    redirect('users');
  }

  public function reset($userid){
    $this->require_super_or_admin();
    $row = $this->users->find((int)$userid);
    if (!$row) show_404();

    // admin LBE tidak boleh reset super admin
    if ((int)$this->session->userdata('groupid')===1 && (int)$row['groupid']===0){
      show_error('Forbidden',403);
    }

    // generate password sementara (8 char)
    $new = substr(bin2hex(random_bytes(8)),0,8);
    $this->users->reset_password((int)$userid, $new);

    $this->session->set_flashdata('ok','Password baru: '.$new);
    redirect('users');
  }
}
