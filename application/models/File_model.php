<?php defined('BASEPATH') OR exit('No direct script access allowed');

class File_model extends CI_Model {

  public function by_shipment($shipment_id){
    return $this->db->order_by('uploaded_at','desc')
                    ->get_where('shipment_files', ['shipment_id'=>$shipment_id])
                    ->result_array();
  }

  public function find($id){
    return $this->db->get_where('shipment_files', ['id'=>$id])->row_array();
  }

  public function save_meta($shipment_id, $doc_type, $upload, $user_id){
    $data = [
      'shipment_id'  => (int)$shipment_id,
      'doc_type'     => $doc_type,
      'server_path'  => $upload['full_path'],
      'original_name'=> $upload['client_name'] ?? $upload['orig_name'] ?? basename($upload['full_path']),
      'mime'         => $upload['file_type'] ?? null,
      'size_bytes'   => (int)($upload['file_size'] ?? 0) * 1024, // CI reports KB
      'uploaded_by'  => (int)$user_id,
      'uploaded_at'  => date('Y-m-d H:i:s')
    ];
    $this->db->insert('shipment_files', $data);
    return $this->db->insert_id();
  }

  public function delete($id){
    $row = $this->find($id);
    if ($row){
      @unlink($row['server_path']);
      $this->db->delete('shipment_files', ['id'=>$id]);
    }
    return (bool)$row;
  }
}
