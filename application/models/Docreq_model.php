<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Docreq_model extends CI_Model {
  public function all_enabled(){
    return $this->db->get_where('doc_requirements',['enabled'=>1])->result_array();
  }
  public function missing_for_shipment($shipment_id, $role_code = 'staff'){
    $s = $this->db->get_where('shipments',['id'=>$shipment_id])->row_array();
    if (!$s) return [];

    $rules = $this->all_enabled();
    $miss = [];
    foreach ($rules as $r){
      // role filter
      $roles = array_map('trim', explode(',', $r['required_for_roles'] ?: ''));
      if ($roles && !in_array($role_code, $roles, true)) continue;

      $field = $r['when_field'];
      $okTrigger = false;
      if ($r['when_operator']==='NOT_NULL'){
        $okTrigger = !empty($s[$field]);
      } elseif ($r['when_operator']==='EQUALS'){
        $okTrigger = (string)($s[$field] ?? '') === (string)$r['when_value'];
      }

      if ($okTrigger){
        // cek file ada?
        $exists = $this->db->select('1')->from('shipment_files')
                  ->where(['shipment_id'=>$shipment_id,'doc_type'=>$r['doc_type']])
                  ->get()->row_array();
        if (!$exists){
          $miss[] = ['doc_type'=>$r['doc_type'],'label'=>$r['label']];
        }
      }
    }
    return $miss;
  }
}
