<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Vessels_model extends CI_Model
{
    public function upsert($data)
    {
        $now = date('Y-m-d H:i:s');
        $data['updated_at'] = $now;
        if (empty($data['created_at'])) $data['created_at'] = $now;

        if (!empty($data['name']) && empty($data['name_norm'])) {
            $data['name_norm'] = vessel_name_norm($data['name']);
        }

        // jika ada id -> update
        if (!empty($data['id'])) {
            $id = (int)$data['id'];
            unset($data['id']);
            $this->db->where('id', $id)->update('vessels', $data);
            return $id;
        }

        // jika punya MMSI -> update berdasarkan MMSI
        if (!empty($data['mmsi'])) {
            $row = $this->db->get_where('vessels', ['mmsi' => $data['mmsi']], 1)->row_array();
            if ($row) {
                $this->db->where('id', (int)$row['id'])->update('vessels', $data);
                return (int)$row['id'];
            }
        }

        // fallback: insert baru
        $this->db->insert('vessels', $data);
        return (int)$this->db->insert_id();
    }

    public function find_by_name($name)
    {
        $norm = vessel_name_norm($name);
        return $this->db->get_where('vessels', ['name_norm' => $norm], 1)->row_array();
    }

    public function search_by_name($q, $limit=30)
    {
        $q = vessel_name_norm($q);
        $this->db->like('name_norm', $q);
        $this->db->order_by('is_active', 'DESC');
        $this->db->order_by('name', 'ASC');
        return $this->db->get('vessels', (int)$limit)->result_array();
    }

    public function active_with_mmsi($limit=200)
    {
        $this->db->where('is_active', 1);
        $this->db->where('mmsi IS NOT NULL', null, false);
        $this->db->where("mmsi <> ''", null, false);
        $this->db->order_by('id', 'ASC');
        return $this->db->get('vessels', (int)$limit)->result_array();
    }
}
