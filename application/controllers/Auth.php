<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends CI_Controller
{
	public function __construct()
	{
	     parent::__construct();
		$this->load->library('form_validation');
		$this->load->library('session');
	
   
    $this->load->model('User_model','users');
    $this->load->helper(['url','form']);
  }

  public function login(){
    if ($this->input->method()==='post'){
      // throttle sederhana (session-based)
      $fail = (int)$this->session->userdata('login_fail_count');
      $last = (int)$this->session->userdata('login_last_ts');
      if ($fail >= 5 && (time()-$last) < 300){
        $data['error'] = 'Terlalu banyak percobaan. Coba lagi dalam beberapa menit.';
        return $this->load->view('auth/login', $data);
      }

      $this->form_validation->set_rules('username','Username','required|trim');
      $this->form_validation->set_rules('password','Password','required');

      if (!$this->form_validation->run()){
        $data['error'] = validation_errors(' ',' ');
        return $this->load->view('auth/login', $data);
      }

      $u  = $this->input->post('username', true);
      $p  = $this->input->post('password');
      $user = $this->users->by_username($u);

      if (!$user || !$this->users->verify_password_and_upgrade($user, $p)){
        $this->session->set_userdata('login_fail_count', $fail+1);
        $this->session->set_userdata('login_last_ts', time());
        $data['error'] = 'Username atau password salah.';
        return $this->load->view('auth/login', $data);
      }

      // sukses → reset throttle
      $this->session->unset_userdata(['login_fail_count','login_last_ts']);

      // set session
      $this->session->sess_regenerate(TRUE);
      $this->session->set_userdata([
        'userid'   => (int)$user['userid'],
        'username' => $user['username'],
        'groupid'  => (string)$user['groupid'],
        'is_login' => 1
      ]);
      $this->users->set_last_login($user['userid']);

      // redirect
      $next = $this->input->get('next') ?: $this->input->post('next');
      if ($next && filter_var(site_url(), FILTER_VALIDATE_URL)){ /* noop */ }
      redirect($next ?: 'shipments');
    }

    $this->load->view('auth/login');
  }

  public function logout(){
    $this->session->sess_destroy();
    redirect('login');
  }

	// Page Login
	public function index()
	{
		//print_r($this->session->userdata());
		$this->form_validation->set_rules('username', 'username', 'trim|required');
		$this->form_validation->set_rules('password', 'password', 'trim|required');

		if ($this->form_validation->run() == false) {
			$this->load->view('login.php');
		} else {
			$this->_login();
		}
	}

	// Authentikasi pengguna
	private function _login()
	{
		$username = $this->input->post('username');
		$password = $this->input->post('password');

		$user = $this->db->get_where('v_login', ['username' => $username])->row_array();
		//echo $this->db->last_query(); exit;

		// Ada User
		if ($user) {
			//verif password
		//	if ($password==$user['password'] && $user['aktif']==1) {
		if ($password==$user['password']) {
				$user['is_login'] = true;
				$this->session->set_userdata($user);
				
				//print_r($this->session->userdata()); exit;
				
				redirect('dashboard');
			} else {
				$this->session->set_flashdata('message', '<div class="alert alert-danger text-center" role="alert">Password yang anda masukkan salah atau status tidak aktif!</div>');
				redirect('auth');
			}
		}
		// Tidak ada user
		else {
			$this->session->set_flashdata('message', '<div class="alert alert-danger text-center" role="alert">Username yang anda masukkan tidak terdaftar!</div>');
			redirect('auth');
		}
	}

	
	public function set_password_random(){
	    $this->load->library('email');
	      $config = array(
    'protocol'    => 'smtp',
    'smtp_host'   => 'ssl://mail.lbebanten.com',
    'smtp_port'   => 465,
    'smtp_user'   => 'info@lbebanten.com',  // alamat email lengkap
    'smtp_pass'   => 'jasacom.n3t',                // pastikan benar
    'mailtype'    => 'html',
    'charset'     => 'utf-8',
    'newline'     => "\r\n",
    'smtp_timeout'=> 10,
    'crlf'        => "\r\n",
    'wordwrap'    => TRUE
);
   
	    $sql=$this->db->query("select * from ci_users where groupid in(3,4)");
	    
	    foreach($sql->result() as $row){
	        $data['password']=RandomString(4);
	        $this->db->where('userid',$row->userid);
	        $this->db->update('ci_users',$data);
	        
	        // NOTIF EMAIL
	        
	        echo  $msg="<html>
            <head>
                <style type='text/css'>
                    body {font-family: Verdana, Geneva, sans-serif}
                    
                </style>
            </head>
            <body>
            
            Dengan Hormat,
            <br><br><bR>
                Berikut Informasi Perubahan Login <b>".$row->nama."</b> Di SiFaBa LBE Banten https://sifaba.lbebanten.com sebagai berikut:<br>
                Username : <b>$row->username </b><br>
                Password : <b>".$data['password']."</b><br><br><br>
                
                <i>Nb: Password Sistem SIFABA akan berlaku 1 bulan, dan setiap taggal 1 setiap awal bulan password akan berubah.</i>
                
            
            </body>
            </html>";

            
         $this->email->set_header('Content-Type', 'text/html');
            $this->email->initialize($config);
            $this->email->from('info@lbebanten.com', 'Info Sifaba - LBE Banten');
            $this->email->to($row->username);
          
            $this->email->subject('RESET PASSWORD MONTHLY Info User '.$row->nama.' di LBE-Banten');
            $this->email->message($msg);
             if ( ! $this->email->send())
            {
                    echo"Generate error";
            }
            echo $msg;
	     }
	     
	      echo  $msg="<html>
            <head>
                <style type='text/css'>
                    body {font-family: Verdana, Geneva, sans-serif}
                    
                </style>
            </head>
            <body>
            
            RESET PASSWORD
            
            </body>
            </html>";

            
            $config['mailtype'] = 'html'; 
            $this->email->set_header('Content-Type', 'text/html');
            $this->email->initialize($config);
            $this->email->from('info@lbebanten.com', 'Info Sifaba - LBE Banten');
            $this->email->to("rakata_oi@yahoo.com");
          
            $this->email->subject('RESET PASSWORD MONTHLY Info User BOWO di LBE-Banten');
            $this->email->message($msg);
              if ( ! $this->email->send())
            {
                    echo"Generate error";
            }
	     
	     
	    
	}
	
	function tes(){
	    	    $this->load->library('email');
$config = array(
    'protocol'    => 'smtp',
    'smtp_host'   => 'ssl://mail.lbebanten.com',
    'smtp_port'   => 465,
    'smtp_user'   => 'info@lbebanten.com',  // alamat email lengkap
    'smtp_pass'   => 'jasacom.n3t',                // pastikan benar
    'mailtype'    => 'html',
    'charset'     => 'utf-8',
    'newline'     => "\r\n",
    'smtp_timeout'=> 10,
    'crlf'        => "\r\n",
    'wordwrap'    => TRUE
);

	     echo  $msg="<html>
            <head>
                <style type='text/css'>
                    body {font-family: Verdana, Geneva, sans-serif}
                    
                </style>
            </head>
            <body>
            
            RESET PASSWORD
            
            </body>
            </html>";

            
            $config['mailtype'] = 'html'; 
            $this->email->set_header('Content-Type', 'text/html');
            $this->email->initialize($config);
            $this->email->from('info@lbebanten.com', 'Info Sifaba - LBE Banten');
            $this->email->to("rakata_oi@yahoo.com");
          
            $this->email->subject('RESET PASSWORD MONTHLY Info User BOWO di LBE-Banten');
            $this->email->message($msg);
              if ( ! $this->email->send())
            {
                    echo"Generate error";
            }
            echo $msg;
	}
	
	public function showqr($de_data){
            	$this->load->library('ciqrcode');
	
        	header("Content-Type: image/png");
        	//$data = encode('https://sifaba.lbebanten.com/cek/disposal/?id_enc=MTU=&choe=UTF-8');
        	$params['data'] = decode($de_data);
        	
        	$this->ciqrcode->generate($params);
        }
        
        public function forgot(){
  // jika sudah login, langsung ke dashboard
  if ($this->session->userdata('userid')) return redirect('shipments');

  if ($this->input->method()==='post'){
    $this->load->library('form_validation');
    $this->form_validation->set_rules('identifier','Email atau Username','required|trim');

    // throttle sederhana per IP
    $fail = (int)$this->session->userdata('fp_fail_count');
    $last = (int)$this->session->userdata('fp_last_ts');
    if ($fail >= 10 && (time()-$last) < 600){
      $data['ok'] = 'Jika akun terdaftar, kami telah mengirim email instruksi reset.';
      return $this->load->view('auth/forgot', $data);
    }

    if ($this->form_validation->run()){
      $ident = $this->input->post('identifier', true);
      $this->load->model('User_model','users');
      $info = $this->users->start_password_reset($ident);

      // Kirim email hanya jika user ada & punya email
      if ($info && !empty($info['email'])){
        $resetUrl = site_url('reset?token='.$info['token']);
        $config = array(
    'protocol'    => 'smtp',
    'smtp_host'   => 'ssl://mail.lbebanten.com',
    'smtp_port'   => 465,
    'smtp_user'   => 'info@lbebanten.com',  // alamat email lengkap
    'smtp_pass'   => 'jasacom.n3t',                // pastikan benar
    'mailtype'    => 'html',
    'charset'     => 'utf-8',
    'newline'     => "\r\n",
    'smtp_timeout'=> 10,
    'crlf'        => "\r\n",
    'wordwrap'    => TRUE
);


        $this->load->library('email');
        $this->email->clear();
          $this->email->initialize($config);
            $this->email->from('info@lbebanten.com', 'Coal Shipping - LBE Banten');
        $this->email->to($info['email']);
        $this->email->subject('Reset Password — Coal Shipping');
        $html = "<p>Halo <b>".htmlentities($info['username'])."</b>,</p>
                 <p>Kami menerima permintaan reset password. Klik tombol berikut (berlaku sampai ".$info['expires']."):</p>
                 <p><a href='".$resetUrl."' style='background:#0d6efd;color:#fff;padding:10px 16px;border-radius:6px;text-decoration:none'>Reset Password</a></p>
                 <p>Jika tombol tidak berfungsi, buka tautan ini: <br><code>".$resetUrl."</code></p>
                 <p>Jika Anda tidak meminta reset, abaikan email ini.</p>";
        $this->email->message($html);
        @$this->email->send(false);
      }

      // Selalu tampilkan pesan sukses yang sama (anti user enumeration)
      $data['ok'] = 'Jika akun terdaftar, kami telah mengirim email instruksi reset.';
      return $this->load->view('auth/forgot', $data);
    }

    $data['error'] = validation_errors(' ',' ');
    return $this->load->view('auth/forgot', $data);
  }

  $this->load->view('auth/forgot');
}

public function reset(){
  // GET: tampil form jika token valid, POST: set password
  $token = $this->input->get('token') ?: $this->input->post('token');
  if (!$token) return show_error('Token tidak ditemukan', 400);

  $this->load->model('User_model','users');
  $user = $this->users->find_by_reset_token($token);
  if (!$user){
    $data['invalid'] = true;
    return $this->load->view('auth/reset', $data);
  }

  if ($this->input->method()==='post'){
    $this->load->library('form_validation');
    $this->form_validation->set_rules('password','Password baru','required|min_length[8]');
    $this->form_validation->set_rules('confirm','Konfirmasi password','required|matches[password]');
    if (!$this->form_validation->run()){
      $data['error'] = validation_errors(' ',' ');
      $data['token'] = $token;
      return $this->load->view('auth/reset', $data);
    }

    $new = $this->input->post('password');
    $this->users->set_new_password_and_clear_token($user['userid'], $new);

    // (opsional) otomatis login setelah reset:
    $this->session->sess_regenerate(TRUE);
    $this->session->set_userdata([
      'userid'   => (int)$user['userid'],
      'username' => $user['username'],
      'groupid'  => (string)$user['groupid'],
      'is_login' => 1
    ]);

    $this->session->set_flashdata('ok','Password berhasil diubah.');
    return redirect('shipments');
  }

  // GET → tampilkan form reset
  $data['token'] = $token;
  $this->load->view('auth/reset', $data);
}

}
