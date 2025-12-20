<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Period_model extends CI_Model {
  public function all(){
    return $this->db->order_by('year','desc')->get('periods')->result_array();
  }
  public function create($year){
    $now = date('Y-m-d H:i:s');
    $this->db->insert('periods', ['year'=>$year, 'is_active'=>0, 'is_locked'=>0, 'created_at'=>$now, 'updated_at'=>$now]);
  }
  public function set_active($year){
    $now = date('Y-m-d H:i:s');
    $this->db->update('periods',['is_active'=>0,'updated_at'=>$now],[]);
    $this->db->update('periods',['is_active'=>1,'updated_at'=>$now],['year'=>$year]);
  }
  public function set_lock($year,$lock){
    $this->db->update('periods',['is_locked'=>$lock,'updated_at'=>date('Y-m-d H:i:s')],['year'=>$year]);
  }
  public function is_locked($year){
    $row = $this->db->get_where('periods',['year'=>(int)$year])->row_array();
    return $row ? (int)$row['is_locked']===1 : false;
  }
  public function active_year(){
    $r = $this->db->get_where('periods',['is_active'=>1])->row_array();
    return $r ? (int)$r['year'] : (int)date('Y');
  }
}
