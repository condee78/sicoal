<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Cron_shipments extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Shipment_model', 'shipments');
    }

    public function map_nominated()
    {
        $mapped = $this->shipments->map_all_nominated_vessels(1000);
        echo "OK mapped={$mapped}\n";
    }
    
    public function map_vessel($shipmentId)
{
    $shipmentId = (int)$shipmentId;

    $this->load->model('Shipment_model', 'shipments');
    $vesselId = $this->shipments->map_nominated_vessel($shipmentId);

    // balik ke halaman sebelumnya
    $ref = $this->input->server('HTTP_REFERER');
    if ($vesselId) {
        // kalau sukses, langsung buka peta vessel
        redirect('vessels/detail/'.$vesselId);
        return;
    }

    // kalau gagal mapping, arahkan ke master vessel search
    // (biar admin bisa tambah/isi MMSI)
    redirect('admin_vessels?q='.urlencode(''.$this->db->get_where('shipments',['id'=>$shipmentId],1)->row('nominated_vessel')));
}

 public function fetch_ais()
    {
        $runAt = date('Y-m-d H:i:s');
        $list  = $this->vessels->active_with_mmsi(200);

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

        for ($i=0; $i<$requested; $i+=$batchSize) {
            $batch = array_slice($mmsiList, $i, $batchSize);
            $res = $this->vf->vessels_by_mmsi($batch);

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
