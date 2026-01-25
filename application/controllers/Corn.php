<?php defined('BASEPATH') OR exit('No direct script access allowed');
/*

cPanel cron:

php /home/USER/public_html/index.php cron/fetch_ais

atau hit via URL (kalau belum bisa CLI):

/cron/fetch_ais?token=...
*/
class Cron extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        // proteksi sederhana (opsional): token
        // if ($this->input->get('token') !== 'TOKENRAHASIA') show_404();

        $this->load->model('Vessels_model', 'vessels');
        $this->load->library('VesselFinder', null, 'vf');
        $this->load->database();
    }

    public function fetch_ais()
    {
        $runAt = date('Y-m-d H:i:s');
        $list  = $this->vessels->active_with_mmsi(1);

        $mmsiList = array_values(array_filter(array_map(function($r){ return $r['mmsi']; }, $list)));
        $requested = count($mmsiList);

        if ($requested === 0) {
            $this->db->insert('ais_fetch_logs', [
                'run_at'=>$runAt, 'vessels_requested'=>0, 'vessels_updated'=>0,
                'status'=>'OK', 'message'=>'Tidak ada vessel aktif yang punya MMSI'
            ]);
            echo "OK: no vessels\n";
            return;
        }

        // batch biar hemat & aman limit
        $batchSize = 50;
        $updated = 0;
        print_r($requested);
        
exit;
        for ($i=0; $i<$requested; $i+=$batchSize) {
            $batch = array_slice($mmsiList, $i, $batchSize);
            $res = $this->vf->vessels_by_mmsi($batch);
print_r($res);
exit;
            if (!$res['ok']) {
                $this->db->insert('ais_fetch_logs', [
                    'run_at'=>$runAt, 'vessels_requested'=>$requested, 'vessels_updated'=>$updated,
                    'status'=>'ERR', 'message'=>substr($res['error'] ?: 'Unknown error', 0, 250)
                ]);
                echo "ERR: ".$res['error']."\n";
                return;
            }

            // NOTE: struktur JSON tergantung API. Sesuaikan parsing di sini.
            // Anggap contoh: $res['data'] adalah array vessels.
            $items = is_array($res['data']) ? $res['data'] : [];
            if (isset($res['data']['vessels']) && is_array($res['data']['vessels'])) {
                $items = $res['data']['vessels'];
            }

            foreach ($items as $it) {
                // mapping minimal
                $mmsi = $it['mmsi'] ?? null;
                if (!$mmsi) continue;

                $v = $this->db->get_where('vessels', ['mmsi'=>$mmsi], 1)->row_array();
                if (!$v) continue;

                $now = date('Y-m-d H:i:s');

                $row = [
                    'vessel_id'   => (int)$v['id'],
                    'lat'         => $it['lat'] ?? null,
                    'lon'         => $it['lon'] ?? null,
                    'sog'         => $it['sog'] ?? null,
                    'cog'         => $it['cog'] ?? null,
                    'heading'     => $it['heading'] ?? null,
                    'nav_status'  => $it['nav_status'] ?? null,
                    'destination' => $it['destination'] ?? null,
                    'eta'         => !empty($it['eta']) ? date('Y-m-d H:i:s', strtotime($it['eta'])) : null,
                    'source'      => $it['source'] ?? null,
                    'pos_time'    => !empty($it['pos_time']) ? date('Y-m-d H:i:s', strtotime($it['pos_time'])) : null,
                    'fetched_at'  => $now,
                    'raw_json'    => json_encode($it, JSON_UNESCAPED_UNICODE)
                ];

                // upsert last_position
                $exists = $this->db->get_where('vessel_last_position', ['vessel_id'=>(int)$v['id']], 1)->row_array();
                if ($exists) {
                    $this->db->where('vessel_id', (int)$v['id'])->update('vessel_last_position', $row);
                } else {
                    $this->db->insert('vessel_last_position', $row);
                }

                // insert history (opsional)
                $this->db->insert('vessel_positions', $row);

                $updated++;
            }
        }

        $this->db->insert('ais_fetch_logs', [
            'run_at'=>$runAt, 'vessels_requested'=>$requested, 'vessels_updated'=>$updated,
            'status'=>'OK', 'message'=>'Fetch success'
        ]);

        echo "OK: requested=$requested updated=$updated\n";
    }
}
