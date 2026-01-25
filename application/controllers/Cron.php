<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Cron extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('Vessels_model', 'vessels');
        $this->load->library('VesselFinder', null, 'vf');
    }

    public function fetch_ais()
    {
        $runAt = date('Y-m-d H:i:s');

        // ambil vessel aktif yang punya MMSI
        $list = $this->db->query("
            SELECT id, mmsi, imo, name
            FROM vessels
            WHERE is_active=1
              AND mmsi IS NOT NULL AND mmsi <> ''
            ORDER BY id ASC
            LIMIT 500
        ")->result_array();

        $mmsiList = array_values(array_filter(array_map(function($r){
            return preg_replace('/\D+/', '', (string)$r['mmsi']);
        }, $list)));

        $requested = count($mmsiList);

        if ($requested === 0) {
            $this->db->insert('ais_fetch_logs', [
                'run_at'=>$runAt, 'vessels_requested'=>0, 'vessels_updated'=>0,
                'status'=>'OK', 'message'=>'Tidak ada vessel aktif dengan MMSI'
            ]);
            echo "OK: no MMSI\n";
            return;
        }

        $batchSize = 50;
        $updated = 0;

        for ($i=0; $i<$requested; $i+=$batchSize) {
            $batch = array_slice($mmsiList, $i, $batchSize);

            $res = $this->vf->vessels_by_mmsi($batch, 'voyage,master', 120, 0);

            if (empty($res['ok'])) {
                $this->db->insert('ais_fetch_logs', [
                    'run_at'=>$runAt, 'vessels_requested'=>$requested, 'vessels_updated'=>$updated,
                    'status'=>'ERR', 'message'=>substr(($res['error'] ?? 'API error'), 0, 250)
                ]);
                echo "ERR: ".($res['error'] ?? 'API error')."\n";
                return;
            }

            $items = is_array($res['data']) ? $res['data'] : [];
            foreach ($items as $it) {
                $ais = $it['AIS'] ?? null;
                if (!$ais) continue;

                $mmsi = isset($ais['MMSI']) ? (string)$ais['MMSI'] : '';
                if ($mmsi === '') continue;

                // temukan vessel berdasarkan MMSI
                $v = $this->db->get_where('vessels', ['mmsi'=>$mmsi], 1)->row_array();
                if (!$v) {
                    // kalau tidak ada, auto insert vessel baru (biar langsung tersimpan)
                    $newId = $this->vessels->upsert([
                        'name' => $ais['NAME'] ?? ('MMSI '.$mmsi),
                        'mmsi' => $mmsi,
                        'imo'  => isset($ais['IMO']) ? (string)$ais['IMO'] : null,
                        'callsign' => $ais['CALLSIGN'] ?? null,
                        'is_active' => 1,
                        'notes' => 'auto:fetch_ais',
                    ]);
                    $v = $this->db->get_where('vessels', ['id'=>$newId], 1)->row_array();
                }

                $master = $it['MASTERDATA'] ?? [];
                $voyage = $it['VOYAGE'] ?? [];

                // ====== UPDATE MASTERDATA ke vessels (lengkap) ======
                $vUpdate = [
                    'name'       => $master['NAME'] ?? ($ais['NAME'] ?? $v['name']),
                    'imo'        => isset($master['IMO']) ? (string)$master['IMO'] : (isset($ais['IMO']) ? (string)$ais['IMO'] : $v['imo']),
                    'callsign'   => $ais['CALLSIGN'] ?? $v['callsign'],
                    'flag'       => $master['FLAG'] ?? $v['flag'],
                    'vessel_type'=> $master['TYPE'] ?? $v['vessel_type'],
                    'built_year' => isset($master['BUILT']) ? (int)$master['BUILT'] : $v['built_year'],
                    'builder'    => $master['BUILDER'] ?? $v['builder'],
                    'owner'      => $master['OWNER'] ?? $v['owner'],
                    'manager'    => $master['MANAGER'] ?? $v['manager'],
                    'class_soc'  => $master['CLASS'] ?? $v['class_soc'],
                    'length_m'   => isset($master['LENGTH']) ? (float)$master['LENGTH'] : $v['length_m'],
                    'beam_m'     => isset($master['BEAM']) ? (float)$master['BEAM'] : $v['beam_m'],
                    'maxdraught_m'=> isset($master['MAXDRAUGHT']) ? (float)$master['MAXDRAUGHT'] : $v['maxdraught_m'],
                    'gt'         => isset($master['GT']) ? (int)$master['GT'] : $v['gt'],
                    'nt'         => isset($master['NT']) ? (int)$master['NT'] : $v['nt'],
                    'dwt'        => isset($master['DWT']) ? (int)$master['DWT'] : $v['dwt'],
                    'teu'        => isset($master['TEU']) ? (int)$master['TEU'] : $v['teu'],
                    'raw_master' => !empty($master) ? json_encode($master, JSON_UNESCAPED_UNICODE) : $v['raw_master'],
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
                $this->db->where('id', (int)$v['id'])->update('vessels', $vUpdate);

                // ====== SIMPAN AIS (lengkap) ke last_position & history ======
                $row = [
                    'vessel_id'   => (int)$v['id'],
                    'lat'         => $ais['LATITUDE'] ?? null,
                    'lon'         => $ais['LONGITUDE'] ?? null,
                    'sog'         => $ais['SPEED'] ?? null,
                    'cog'         => $ais['COURSE'] ?? null,
                    'heading'     => $ais['HEADING'] ?? null,
                    'nav_status'  => isset($ais['NAVSTAT']) ? (string)$ais['NAVSTAT'] : null,
                    'destination' => $ais['DESTINATION'] ?? null,
                    'callsign'    => $ais['CALLSIGN'] ?? null,
                    'vessel_type_code' => isset($ais['TYPE']) ? (string)$ais['TYPE'] : null,
                    'draught'     => $ais['DRAUGHT'] ?? null,
                    'locode'      => $ais['LOCODE'] ?? null,
                    'zone'        => $ais['ZONE'] ?? null,
                    'eca'         => isset($ais['ECA']) ? (int)(!!$ais['ECA']) : null,
                    'eta_ais'     => $ais['ETA_AIS'] ?? null,
                    'eta'         => vf_utc_to_mysql($ais['ETA'] ?? null),  // "2026-01-16 07:00:00"
                    'source'      => $ais['SRC'] ?? null,
                    'distance_remaining' => isset($ais['DISTANCE_REMAINING']) ? (string)$ais['DISTANCE_REMAINING'] : null,
                    'eta_predicted' => isset($ais['ETA_PREDICTED']) ? (string)$ais['ETA_PREDICTED'] : null,
                    'pos_time'    => vf_utc_to_mysql($ais['TIMESTAMP'] ?? null),
                    'fetched_at'  => date('Y-m-d H:i:s'),
                    // simpan FULL item (AIS+MASTERDATA+VOYAGE) biar “semua informasi” aman tersimpan
                    'raw_json'    => json_encode($it, JSON_UNESCAPED_UNICODE),
                ];

                // upsert last_position
                $exists = $this->db->get_where('vessel_last_position', ['vessel_id'=>(int)$v['id']], 1)->row_array();
                if ($exists) {
                    $this->db->where('vessel_id', (int)$v['id'])->update('vessel_last_position', $row);
                } else {
                    $this->db->insert('vessel_last_position', $row);
                }

                // insert history (opsional, tapi kamu minta semua info: ini bagus untuk audit)
                $this->db->insert('vessel_positions', $row);

                $updated++;
            }
        }

        $this->db->insert('ais_fetch_logs', [
            'run_at'=>$runAt,
            'vessels_requested'=>$requested,
            'vessels_updated'=>$updated,
            'status'=>'OK',
            'message'=>'Fetch success'
        ]);

        echo "OK: requested={$requested} updated={$updated}\n";
    }
}
