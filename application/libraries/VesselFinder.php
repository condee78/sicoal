<?php defined('BASEPATH') OR exit('No direct script access allowed');

class VesselFinder
{
    protected $CI;
    protected $base;
    protected $key;
    protected $timeout;
    protected $ua;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->config('vesselfinder');

        $this->base    = rtrim($this->CI->config->item('vf_base_url'), '/');
        $this->key     = $this->CI->config->item('vf_api_key');
        $this->timeout = (int)$this->CI->config->item('vf_timeout');
        $this->ua      = $this->CI->config->item('vf_useragent');
    }
    
    

    public function expected_arrivals($locode, $intervalMinutes = 1440, $extraData = 'master,voyage', $limit = null)
{
    $params = [
        'userkey'  => $this->key,      // sesuai docs: userkey :contentReference[oaicite:2]{index=2}
        'format'   => 'json',
        'locode'   => $locode,
        'interval' => (int)$intervalMinutes,
    ];
    if (!empty($extraData)) $params['extradata'] = $extraData; // voyage,master :contentReference[oaicite:3]{index=3}
    if (!empty($limit)) $params['limit'] = (int)$limit;

    // expectedarrivals endpoint
    return $this->request('/expectedarrivals', $params);
}


    protected function request($path, $params=[])
    {
        if (!$this->key) {
            return ['ok'=>false,'error'=>'API key belum di-set','data'=>null];
        }

        $params['userkey'] = $this->key;
        $url = $this->base . '/' . ltrim($path, '/');
        $url .= (strpos($url,'?')===false ? '?' : '&') . http_build_query($params);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => $this->timeout,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_USERAGENT      => $this->ua,
        ]);
        $raw  = curl_exec($ch);
        $err  = curl_error($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch); 

        if ($raw === false) {
            return ['ok'=>false,'error'=>$err ?: 'cURL error','data'=>null, 'http'=>$code, 'raw'=>null];
        }

        $json = json_decode($raw, true);

        if ($code >= 400) {
            return ['ok'=>false,'error'=>"HTTP $code", 'data'=>$json, 'http'=>$code, 'raw'=>$raw];
        }

        // kalau bukan JSON, tetap balikin raw
        return ['ok'=>true,'error'=>null,'data'=>$json ?: $raw,'http'=>$code,'raw'=>$raw];
    }
    
      protected function requestx($path, $params=[])
    {
        if (!$this->userkey) {
            return ['ok'=>false,'error'=>'userkey belum diset','data'=>null];
        }

        $params['userkey'] = $this->userkey;
        if (!isset($params['format'])) $params['format'] = 'json';

        $url = $this->base . '/' . ltrim($path, '/');
        $url .= (strpos($url,'?')===false ? '?' : '&') . http_build_query($params);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => $this->timeout,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_USERAGENT      => $this->ua,
        ]);
        $raw  = curl_exec($ch);
        $err  = curl_error($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($raw === false) return ['ok'=>false,'error'=>$err ?: 'cURL error','data'=>null,'http'=>$code,'raw'=>null];

        $json = json_decode($raw, true);
        if ($code >= 400) return ['ok'=>false,'error'=>"HTTP $code",'data'=>$json,'http'=>$code,'raw'=>$raw];

        return ['ok'=>true,'error'=>null,'data'=>$json ?: $raw,'http'=>$code,'raw'=>$raw];
    }

    // Vessels by IMO list
    public function vessels_by_imo($imoList, $extra='voyage,master', $interval=120, $sat=0)
    {
        $imo = is_array($imoList) ? implode(',', $imoList) : $imoList;
        
        return $this->request('/vessels', [
            'imo'       => $imo,
            'extradata' => $extra,     // voyage,master :contentReference[oaicite:2]{index=2}
          //  'interval'  => (int)$interval,
            'sat'       => (int)$sat
        ]);
    }


    /**
     * NOTE: Nama endpoint berbeda tergantung paket VesselFinder.
     * Kamu tinggal sesuaikan path & param.
     */
    public function vessels_by_mmsix($mmsiList)
    {
        // contoh: list MMSI dipisah koma
        // path/param ini sering berupa dataset: AIS/Voyage/Master
        $mmsi = is_array($mmsiList) ? implode(',', $mmsiList) : $mmsiList;

        // TODO: SESUAIKAN endpoint sesuai paket kamu
        // contoh generik:
        return $this->request('/vessels', [
            'mmsi' => $mmsi,
            'dataset' => 'AIS,VOYAGE,MASTER'
        ]);
    }
    
        // Vessels by MMSI list
    public function vessels_by_mmsi($mmsiList, $extra='voyage,master', $interval=120, $sat=0)
    {
        $mmsi = is_array($mmsiList) ? implode(',', $mmsiList) : $mmsiList;
        return $this->request('/vessels', [
            'mmsi'      => $mmsi,
            'extradata' => $extra,
            //'interval'  => (int)$interval,
            'sat'       => (int)$sat
        ]);
    }


    public function search_vessel_by_name($name)
    {
        // TODO: SESUAIKAN endpoint sesuai paket kamu
        return $this->request('/search', ['q' => $name]);
    }
}
