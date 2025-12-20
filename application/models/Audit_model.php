<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Audit_model extends CI_Model {

public function list($filter = [], $limit = 200){
  if (!empty($filter['entity']))    $this->db->where('entity', $filter['entity']);
  if (!empty($filter['entity_id'])) $this->db->where('entity_id', (int)$filter['entity_id']);  // <— TAMBAH INI
  if (!empty($filter['action']))    $this->db->where('action', $filter['action']);
  if (!empty($filter['actor']))     $this->db->where('actor_userid', (int)$filter['actor']);
  if (!empty($filter['from']))      $this->db->where('created_at >=', $filter['from'].' 00:00:00');
  if (!empty($filter['to']))        $this->db->where('created_at <=', $filter['to'].' 23:59:59');
  $this->db->order_by('id','DESC');
  $this->db->limit($limit);
  return $this->db->get('audit_logs')->result_array();
}


  public function log($entity, $entity_id, $action, array $before = [], array $after = [], array $meta = []){
    $changes = $this->build_diff($before, $after);
    $record = [
      'request_id'   => $this->request_id(),
      'entity'       => $entity,
      'entity_id'    => $entity_id,
      'action'       => $action,
      'changes_json' => $changes ? json_encode($changes, JSON_UNESCAPED_UNICODE) : null,
      'meta_json'    => json_encode($this->meta_defaults($meta), JSON_UNESCAPED_UNICODE),
      'actor_userid' => (int)($this->session->userdata('userid')),
      'created_at'   => date('Y-m-d H:i:s'),
    ];
    $this->db->insert('audit_logs', $record);
  }

  /** Ringkas diff: hanya field berubah (skip created_at/updated_at) */
  private function build_diff(array $before, array $after){
    if (!$before && !$after) return null;
    $ignore = ['created_at','updated_at','uploaded_at','updated_by','created_by'];
    $keys = array_unique(array_merge(array_keys($before), array_keys($after)));
    $changes = [];
    foreach ($keys as $k){
      if (in_array($k, $ignore, true)) continue;
      $b = array_key_exists($k,$before) ? $before[$k] : null;
      $a = array_key_exists($k,$after)  ? $after[$k]  : null;
      // normalisasi tipe
      if ($b === $a) continue;
      $changes[$k] = ['from'=>$b, 'to'=>$a];
    }
    return $changes;
  }

  private function meta_defaults(array $extra){
    $meta = [
      'ip'      => $_SERVER['REMOTE_ADDR']   ?? null,
      'ua'      => $_SERVER['HTTP_USER_AGENT'] ?? null,
      'url'     => (isset($_SERVER['REQUEST_METHOD'])?$_SERVER['REQUEST_METHOD']:'')." ".($_SERVER['REQUEST_URI'] ?? ''),
      'role'    => function_exists('role_code') ? role_code() : null,
    ];
    return array_merge($meta, $extra);
  }

  private function request_id(){
    if (!isset($_SESSION['req_id'])) {
      $_SESSION['req_id'] = bin2hex(random_bytes(8)); // ringan, unik per request
    }
    return $_SESSION['req_id'];
  }

 
}
