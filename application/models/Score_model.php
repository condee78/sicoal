<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Score_model extends CI_Model {
  /* ====== SCORING RULES ====== */

  // 1) On-time score berdasarkan o_diff_days (0=tepat)
  public function score_on_time($o_diff_days){
    if ($o_diff_days === null || $o_diff_days === '') return 0;
    $d = (int)$o_diff_days;
    $ad = abs($d);
    if ($ad === 0) return 5;
    if ($ad === 1) return 4;
    if ($ad === 2) return 3;
    if ($ad === 3) return 2;
    return 1; // >3 lebih/kurang
  }

  // 2) Volume score berdasarkan persentase (actual/plan) * 100
  public function score_volume($ratio_pct){
    if ($ratio_pct === null) return 0;
    $p = (float)$ratio_pct;
    if ($p >= 100.0)                 return 5;
    if ($p >= 97.5 && $p < 100.0)    return 4;
    if ($p >= 95.0  && $p < 97.5)    return 3;
    if ($p >= 92.5  && $p < 95.0)    return 2;
    if ($p >= 90.0  && $p < 92.5)    return 1;
    return 0; // di bawah 90%
  }

  // 3) COA score berdasarkan hari setelah departure (coa_diff_days)
  public function score_coa($coa_diff_days){
    if ($coa_diff_days === null) return 0;
    $d = (int)$coa_diff_days;
    if ($d < 3)  return 5;     // <3 hari
    if ($d == 4) return 4;
    if ($d == 5) return 3;
    if ($d == 6) return 2;
    if ($d == 7) return 1;
    return 0; // >7 hari atau nilai lain
  }

  // Hitung skor untuk satu ROW shipment (array), kembalikan kolom skor + total
  public function compute_row_scores(array $row){

    // derive o_diff_days
    $o = isset($row['o_diff_days']) ? $row['o_diff_days'] : null;

    // derive v_ratio_pct
    $ratio = null;
    if (!empty($row['volume_actual_mt'])) {
      $ratio = ($row['volume_actual_mt'] ?? 0) / (float)$row['volume_plan_mt'] * 100.0;
    }

    // derive coa_diff_days
    $coa = isset($row['coa_diff_days']) ? $row['coa_diff_days'] : null ;

    $s1 = $this->score_on_time($o);
    $s2 = $this->score_volume($ratio);
    $s3 = $this->score_coa($coa);

    $total = round( ($s1 + $s2 + $s3) / 3, 2 );

    return [
      'o_diff_days'   => ($o !== null ? (int)$o : null),
      'v_ratio_pct'   => ($ratio !== null ? round($ratio,2) : null),
      'coa_diff_days' => ($coa !== null ? (int)$coa : null),
      'score_on_time' => $s1,
      'score_volume'  => $s2,
      'score_coa'     => $s3,
      'score_total'   => $total,
    ];
  }

  /* ====== DATA FETCH ====== */

  // Ambil ringkasan per-shipper (periode)
  public function summary_by_shipper($rekanan_kode){
  
    // ambil minimal field yang dibutuhkan
     $this->db->select('s.rekanan_kode, r.nama_perusahaan, s.eta_lbe, s.actual_date, s.volume_plan_mt, s.volume_actual_mt, s.dt_coa_delivery, s.actual_departure,s.o_diff_days,s.coa_diff_days')
                  ->from('shipments_v s')
                  ->join('m_rekanan r','r.kode = s.rekanan_kode','left');
                      if (!empty($rekanan_kode)) $this->db->where('s.rekanan_kode', $rekanan_kode);


                

                             // FILTER MODE RANGE
  $CI =& get_instance(); $CI->load->library('Filter_mode');
  $CI->filter_mode->apply_to_db_eta($this->db);
    $q = $this->db->get()->result_array();

    // agregasi manual (karena ada fungsi skor per-bar is)
    $agg = []; // kode => ['nama'=>..,'n'=>.., sums...]
    foreach($q as $row){
      $scores = $this->compute_row_scores($row);
      $kode = $row['rekanan_kode'] ?? '-';
      if (!isset($agg[$kode])){
        $agg[$kode] = [
          'rekanan_kode' => $kode,
          'nama_perusahaan' => $row['nama_perusahaan'] ?? '',
          'count_shipments' => 0,
          'count_shipments_act' => 0,
          'sum_s1' => 0, 'sum_s2' => 0, 'sum_s3' => 0, 'sum_total' => 0,
          'sum_plan_vol' => 0.0, 'sum_actual_vol' => 0.0
        ];
      }
      if($row['actual_date']!='0000-00-00 00:00:00' and $row['actual_date']!=null){
       $agg[$kode]['count_shipments_act']++;
      }
       
      $agg[$kode]['count_shipments']++;
      $agg[$kode]['sum_s1'] += $scores['score_on_time'];
      $agg[$kode]['sum_s2'] += $scores['score_volume'];
      $agg[$kode]['sum_s3'] += $scores['score_coa'];
      $agg[$kode]['sum_total'] += $scores['score_total'];
      $agg[$kode]['sum_plan_vol']   += (float)($row['volume_plan_mt'] ?? 0);
      $agg[$kode]['sum_actual_vol'] += (float)($row['volume_actual_mt'] ?? 0);
    }

    // finalize
    $out = [];
    foreach($agg as $a){
      $n = max(1, $a['count_shipments']);
      $out[] = [
        'rekanan_kode' => $a['rekanan_kode'],
        'nama_perusahaan' => $a['nama_perusahaan'],
        'count_shipments' => $a['count_shipments'],
         'count_shipments_act' => $a['count_shipments_act'],
        'avg_s1' => round($a['sum_s1'] / $n, 2),
        'avg_s2' => round($a['sum_s2'] / $n, 2),
        'avg_s3' => round($a['sum_s3'] / $n, 2),
        'avg_total' => round($a['sum_total'] / $n, 2),
        'total_plan_vol' => round($a['sum_plan_vol'],2),
        'total_actual_vol' => round($a['sum_actual_vol'],2),
      ];
    }
    // sort: terbaik dulu
    usort($out, fn($x,$y)=> $y['avg_total'] <=> $x['avg_total']);
    return $out;
  }

  // Ambil detail shipment untuk satu shipper (periode)
  public function shipments_of_shipper($rekanan_kode){
  

    $this->db->select('s.*, r.nama_perusahaan')
                     ->from('shipments_v s')
                     ->join('m_rekanan r','r.kode=s.rekanan_kode','left')
                     ->where('s.rekanan_kode', $rekanan_kode)
                     ->order_by('s.eta_lbe','asc');
                     
                         // FILTER MODE RANGE
  $CI =& get_instance(); $CI->load->library('Filter_mode');
  $CI->filter_mode->apply_to_db_eta($this->db);
  $rows = $this->db->get()->result_array();

    // tambahkan skor per row
    foreach($rows as &$row){
      $scores = $this->compute_row_scores($row);
      $row = array_merge($row, $scores);
    }
    return $rows;
  }



  private function score_case_sql($alias = 'v'){
    // Skor per shipment dari O$ (o_diff_days)
    return "CASE
      WHEN {$alias}.o_diff_days <= -1.01 THEN 2
      WHEN {$alias}.o_diff_days BETWEEN -1.00 AND 1.00 THEN 1
      WHEN {$alias}.o_diff_days >= 2.00 THEN -1
      ELSE 0
    END";
  }

  public function ranking($year,$rekanan_kode){
    $score = $this->score_case_sql('v');
    $w_rekanan='';
     if ($rekanan_kode) $w_rekanan="and v.rekanan_kode='$rekanan_kode'";
    $sql = "SELECT v.rekanan_kode, r.nama_perusahaan,
            COUNT(*) shipments,
            SUM(COALESCE(v.volume_plan_mt,0)) vol_plan,
            SUM(COALESCE(v.volume_actual_mt,0)) vol_actual,
            AVG($score) score_avg,
            AVG(CASE WHEN v.o_diff_days BETWEEN -1.00 AND 1.00 THEN 1 ELSE 0 END) ontime_rate
          FROM shipments_v v
          JOIN m_rekanan r ON r.kode=v.rekanan_kode 
          WHERE (v.period_year) = ? $w_rekanan
          GROUP BY v.rekanan_kode, r.nama_perusahaan
          ORDER BY score_avg DESC, vol_plan DESC";
    return $this->db->query($sql, [$year])->result_array();
  }

  public function trend_monthly($year, $rekanan_kode = null){
    $score = $this->score_case_sql('v');
    $this->db->select("MONTH(v.eta_lbe) m,
                       COUNT(*) shipments,
                       AVG($score) score_avg,
                       SUM(CASE WHEN v.o_diff_days <= -1.01 THEN 1 ELSE 0 END) early,
                       SUM(CASE WHEN v.o_diff_days BETWEEN -1.00 AND 1.00 THEN 1 ELSE 0 END) ontime,
                       SUM(CASE WHEN v.o_diff_days >= 2.00 THEN 1 ELSE 0 END) late");
    $this->db->from('shipments_v v');
    $this->db->where('(v.period_year)', (int)$year);
    if ($rekanan_kode) $this->db->where('v.rekanan_kode', $rekanan_kode);
    $this->db->group_by('MONTH(v.eta_lbe)');
    $this->db->order_by('m','ASC');
    return $this->db->get()->result_array();
  }

  public function breakdown($year, $rekanan_kode = null){
    // Total early/ontime/late sepanjang tahun
    $this->db->select("
      SUM(CASE WHEN v.o_diff_days <= -1.01 THEN 1 ELSE 0 END) early,
      SUM(CASE WHEN v.o_diff_days BETWEEN -1.00 AND 1.00 THEN 1 ELSE 0 END) ontime,
      SUM(CASE WHEN v.o_diff_days >= 2.00 THEN 1 ELSE 0 END) late
    ");
    $this->db->from('shipments_v v');
    $this->db->where('(v.period_year)', (int)$year);
    if ($rekanan_kode) $this->db->where('v.rekanan_kode', $rekanan_kode);
    return $this->db->get()->row_array() ?: ['early'=>0,'ontime'=>0,'late'=>0];
  }
}
