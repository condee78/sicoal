<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Filter_mode {
  const MODE_PERIOD = 'period';
  const MODE_RANGE  = 'range';

  protected $CI;
  protected $sess_key = 'filter_mode_state';

  public function __construct(){
    $this->CI =& get_instance();
    // seed default kalau belum ada
    $state = $this->CI->session->userdata($this->sess_key);
    if (!$state) {
      $state = [
        'mode'       => self::MODE_PERIOD,
        'period_y'   => date('Y'),
        'date_from'  => date('Y-01-01'),
        'date_to'    => date('Y-12-31'),
      ];
      $this->CI->session->set_userdata($this->sess_key, $state);
    }
  }

  public function state(): array {
    return $this->CI->session->userdata($this->sess_key);
  }

  public function set_from_request(){
    $mode = $this->CI->input->post('mode') ?: $this->CI->input->get('mode');
    if (!$mode) return; // nothing to change

    $st = $this->state();
    $st['mode'] = in_array($mode,[self::MODE_PERIOD,self::MODE_RANGE]) ? $mode : self::MODE_PERIOD;

    if ($st['mode']===self::MODE_PERIOD){
      $y = $this->CI->input->post('period_y') ?: $this->CI->input->get('period_y') ?: date('Y');
      $st['period_y'] = preg_replace('/\D/','',$y) ?: date('Y');
      // sinkronkan range dengan tahun
      $st['date_from'] = $st['period_y'].'-01-01';
      $st['date_to']   = $st['period_y'].'-12-31';
    } else {
      $df = $this->CI->input->post('date_from') ?: $this->CI->input->get('date_from') ?: date('Y-01-01');
      $dt = $this->CI->input->post('date_to')   ?: $this->CI->input->get('date_to')   ?: date('Y-12-31');
      $st['date_from'] = date('Y-m-d', strtotime($df));
      $st['date_to']   = date('Y-m-d', strtotime($dt));
      // sinkronkan period_y mengikuti from
      $st['period_y']  = substr($st['date_from'],0,4);
    }

    $this->CI->session->set_userdata($this->sess_key, $st);

    // cookie (fallback) 7 hari
    $this->CI->input->set_cookie([
      'name'   => $this->sess_key,
      'value'  => json_encode($st),
      'expire' => 60*60*24*7,
      'path'   => '/',
      'secure' => false,
      'httponly'=> false,
      'samesite'=> 'Lax',
    ]);
  }

  /** Helper untuk bangun klausa filter standar by ETA LBE / period */
  public function apply_to_db_eta($db){
    $st = $this->state();
    if ($st['mode']===self::MODE_PERIOD){
      // pilih salah satu: period_year atau YEAR(eta_lbe)
      $db->where('s.period_year', (int)$st['period_y']); // jika tabel punya period_year
      // atau:
      // $db->where('YEAR(s.eta_lbe)', (int)$st['period_y']);
    } else {
      $db->where('DATE(s.eta_lbe) >=', $st['date_from']);
      $db->where('DATE(s.eta_lbe) <=', $st['date_to']);
    }
  }

  public function get(){ return $this->state(); }
}
