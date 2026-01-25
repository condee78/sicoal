<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_vessels extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        // TODO: pasang auth role admin
        $this->load->model('Vessels_model','vessels');
        $this->load->library('VesselFinder', null, 'vf');
    }

    public function index()
    {
        $q = $this->input->get('q');
        $data['q'] = $q;
        $data['rows'] = $q ? $this->vessels->search_by_name($q, 50) : $this->vessels->search_by_name('', 50);
        $this->load->view('vessels_index', $data);
    }

    public function save()
    {
        $post = $this->input->post();
        $data = [
            'id'         => $post['id'] ?? null,
            'vendor_kode'=> $post['vendor_kode'] ?? null,
            'name'       => $post['name'] ?? '',
             'callsign'       => $post['callsign'] ?? '',
            'mmsi'       => $post['mmsi'] ?? null,
            'imo'        => $post['imo'] ?? null,
            'vessel_type'=> $post['vessel_type'] ?? null,
            'flag'       => $post['flag'] ?? null,
            'is_active'  => !empty($post['is_active']) ? 1 : 0,
            'notes'      => $post['notes'] ?? null,
        ];
        $this->vessels->upsert($data);
        redirect('admin_vessels');
    }

    public function api_search_old()
    {
        $name = $this->input->get('name');
        if (!$name) {
            echo json_encode(['ok'=>false,'message'=>'name required']); return;
        }
        $res = $this->vf->search_vessel_by_name($name);
        header('Content-Type: application/json');
        echo json_encode($res);
    }
    
    public function api_search()
{
    $q = trim((string)$this->input->get('q', true));
    if ($q === '') {
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['ok'=>false,'message'=>'q required']));
    }

    $this->load->library('VesselFinder', null, 'vf');

    // bersihkan input untuk deteksi angka
    $q_num = preg_replace('/\D+/', '', $q);

    // IMO biasanya 7 digit, MMSI 9 digit (umum)
    if (strlen($q_num) === 7) {
        $res = $this->vf->vessels_by_imo($q_num);
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'ok' => (bool)$res['ok'],
                'mode' => 'imo',
                'query' => $q_num,
                'api' => $res
            ], JSON_UNESCAPED_UNICODE));
    }

    if (strlen($q_num) === 9) {
        $res = $this->vf->vessels_by_mmsi($q_num);
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'ok' => (bool)$res['ok'],
                'mode' => 'mmsi',
                'query' => $q_num,
                'api' => $res
            ], JSON_UNESCAPED_UNICODE));
    }

    // Kalau bukan IMO/MMSI, berarti nama: cari di DB lokal (karena /vessels tidak bisa search nama)
    $norm = vessel_name_norm($q);
    $rows = $this->db->query("
        SELECT id, name, mmsi, imo, vessel_type, flag, vendor_kode, is_active
        FROM vessels
        WHERE name_norm LIKE ?
        ORDER BY is_active DESC, name ASC
        LIMIT 30
    ", ['%'.$norm.'%'])->result_array();

    return $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode([
            'ok' => true,
            'mode' => 'local_name',
            'query' => $q,
            'results' => $rows
        ], JSON_UNESCAPED_UNICODE));
}

}
