<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Vessels extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    // Halaman detail (map + info)
    public function detail($id)
    {
        $id = (int)$id;
        $row = $this->db->query("
            SELECT v.*,
                   lp.lat, lp.lon, lp.sog, lp.cog, lp.heading, lp.nav_status,
                   lp.destination, lp.eta, lp.pos_time, lp.fetched_at
            FROM vessels v
            LEFT JOIN vessel_last_position lp ON lp.vessel_id = v.id
            WHERE v.id = ?
            LIMIT 1
        ", [$id])->row_array();

        if (!$row) show_404();

        $data['vessel'] = $row;
        $this->load->view('vessels/detail', $data);
    }

    // API JSON untuk refresh live data
    public function api_detail($id)
    {
        $id = (int)$id;
        $row = $this->db->query("
            SELECT v.id, v.name, v.mmsi, v.imo, v.vessel_type, v.flag, v.vendor_kode,
                   lp.lat, lp.lon, lp.sog, lp.cog, lp.heading, lp.nav_status,
                   lp.destination, lp.eta, lp.pos_time, lp.fetched_at
            FROM vessels v
            LEFT JOIN vessel_last_position lp ON lp.vessel_id = v.id
            WHERE v.id = ?
            LIMIT 1
        ", [$id])->row_array();

        $out = [
            'ok' => $row ? true : false,
            'data' => $row ?: null
        ];

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($out, JSON_UNESCAPED_UNICODE));
    }

    // (Opsional) API untuk ambil track terakhir (polyline)
    public function api_track($id)
    {
        $id = (int)$id;

        // ambil track 12 jam terakhir (ubah sesuai kebutuhan)
        $rows = $this->db->query("
            SELECT lat, lon, pos_time
            FROM vessel_positions
            WHERE vessel_id = ?
              AND lat IS NOT NULL AND lon IS NOT NULL
              AND pos_time >= DATE_SUB(NOW(), INTERVAL 12 HOUR)
            ORDER BY pos_time ASC
        ", [$id])->result_array();

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['ok'=>true,'points'=>$rows], JSON_UNESCAPED_UNICODE));
    }
}
