<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Cron_vessels extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Vessels_model', 'vessels');
        $this->load->library('VesselFinder', null, 'vf');
        $this->load->database();
    }

    /**
     * Seed banyak vessel dari port (ExpectedArrivals)
     * URL contoh:
     * /cron_vessels/seed_expected_arrivals?locode=IDMRK&interval=10080
     */
    public function seed_expected_arrivals()
    {
        $locode   = $this->input->get('locode', true);
        $interval = (int)($this->input->get('interval', true) ?: 1440);
        $limit    = $this->input->get('limit', true);
        $limit    = $limit ? (int)$limit : null;

        if (!$locode) {
            show_error('locode wajib. contoh: ?locode=BGVAR&interval=1440', 400);
            return;
        }

        $res = $this->vf->expected_arrivals($locode, $interval, 'master,voyage', $limit);

        if (empty($res['ok'])) {
            show_error('API error: '.($res['error'] ?? 'unknown'), 500);
            return;
        }

        $items = is_array($res['data']) ? $res['data'] : [];
        $saved = 0;

        foreach ($items as $it) {
            $ais = $it['AIS'] ?? null;
            if (!$ais) continue;

            $name = $ais['NAME'] ?? null;
            $mmsi = $ais['MMSI'] ?? null;
            $imo  = $ais['IMO'] ?? null;

            if (!$name && !$mmsi) continue;

            $master = $it['MASTERDATA'] ?? [];
            $type   = $master['TYPE'] ?? ($ais['TYPE'] ?? null);
            $flag   = $master['FLAG'] ?? null;

            $data = [
                'name'       => $name ?: ('MMSI '.$mmsi),
                'mmsi'       => $mmsi ? (string)$mmsi : null,
                'imo'        => $imo ? (string)$imo : null,
                'vessel_type'=> $type ? (string)$type : null,
                'flag'       => $flag ? (string)$flag : null,
                'is_active'  => 1,
                'notes'      => 'seed:expectedarrivals locode='.$locode,
            ];

            $this->vessels->upsert($data);
            $saved++;
        }

        echo "OK seed_expected_arrivals locode={$locode} interval={$interval} saved={$saved}\n";
    }
}
