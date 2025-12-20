<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Shipment_model extends CI_Model {


// di atas class, tambahkan:
private function is_period_locked_for_payload($payload){
  // Tentukan tahun periode dari eta_lbe atau period_year
 
  $year = null;
  if (!empty($payload['period_year'])) $year = (int)$payload['period_year'];
  elseif (!empty($payload['eta_lbe'])) $year = (int)date('Y', strtotime($payload['eta_lbe']));
  if (!$year) return false; // kalau belum bisa ditentukan → jangan blok

  $row = $this->db->get_where('periods',['year'=>$year])->row_array();
  
  return ($row && (int)$row['is_locked']===1);
}


private function _diff_changed_fields(array $before, array $after, array $limitKeys = null){
  $out = [];
  $keys = $limitKeys ? $limitKeys : array_unique(array_merge(array_keys($before), array_keys($after)));
  foreach ($keys as $k){
      if($k!='updated_at'){
    $b = array_key_exists($k, $before) ? (string)$before[$k] : null;
    $a = array_key_exists($k, $after)  ? (string)$after[$k]  : null;
    if ($b !== $a) $out[$k] = ['old'=>$before[$k] ?? null, 'new'=>$after[$k] ?? null];
      }
  }
  return $out;
}

private function require_doc_if_completed($shipment_id, $new_status){
  if (strtolower($new_status)!=='completed') return null;
  // Apakah sudah ada file AH_PROOF ?
  $exists = $this->db->select('1')->from('shipment_files')
            ->where(['shipment_id'=>$shipment_id,'doc_type'=>'AH_PROOF'])
            ->get()->row_array();
  return $exists ? null : 'Status Completed membutuhkan dokumen AH_PROOF.';
}


  // KPI Cards
  public function kpi_cards($periode){
    $cards = ['planned_far'=>0,'due_7days'=>0,'delayed'=>0,'completed'=>0];
    // planned_far
    $q = $this->db->query("SELECT 
      SUM(CASE WHEN eta_lbe > NOW() + INTERVAL 7 DAY AND shipment_completed=0 THEN 1 ELSE 0 END) planned_far,
      SUM(CASE WHEN eta_lbe BETWEEN NOW() AND NOW() + INTERVAL 7 DAY AND shipment_completed=0 THEN 1 ELSE 0 END) due_7days,
      SUM(CASE WHEN eta_lbe < NOW() AND shipment_completed=0 THEN 1 ELSE 0 END) `delayed`,
      SUM(CASE WHEN shipment_completed=1 THEN 1 ELSE 0 END) completed
    FROM shipments where period_year='$periode'");
    if ($row = $q->row_array()) $cards = array_merge($cards,$row);
    return $cards;
  }

  public function plan_actual_monthly($year){
    $sql = "SELECT MONTH(eta_lbe) m,
                   SUM(volume_plan_mt) plan,
                   SUM(COALESCE(volume_actual_mt,0)) actual
            FROM shipments
            WHERE YEAR(eta_lbe)=?
            GROUP BY MONTH(eta_lbe)
            ORDER BY m";
    return $this->db->query($sql, [$year])->result_array();
  }

  public function by_shipper_counts(){
    $sql = "SELECT r.nama_perusahaan, s.rekanan_kode, COUNT(*) shipments
            FROM shipments s
            JOIN m_rekanan r ON r.kode=s.rekanan_kode
            GROUP BY s.rekanan_kode, r.nama_perusahaan
            ORDER BY shipments DESC";
    return $this->db->query($sql)->result_array();
  }

  public function performance_tablex($periode=''){
    // pakai shipments_v untuk akses o_diff_days & volume_plan_mt
    $sql = "SELECT s.rekanan_kode, r.nama_perusahaan,
                   COUNT(*) shipments,
                   SUM(
    CASE
      WHEN NULLIF(s.actual_date, '0000-00-00 00:00:00') IS NOT NULL THEN 1
      ELSE 0
    END
  ) AS shipments_act,
                   SUM(COALESCE(s.volume_plan_mt,0)) vol,
                   SUM(COALESCE(s.volume_actual_mt,0)) vol_act,
                   AVG(
                     CASE
                       WHEN v.o_diff_days <= -1.01 THEN 2
                       WHEN v.o_diff_days BETWEEN -1.00 AND 1.00 THEN 1
                       WHEN v.o_diff_days >= 2.00 THEN -1
                       ELSE 0
                     END
                   ) AS score_avg
            FROM shipments s
            JOIN m_rekanan r ON r.kode=s.rekanan_kode
            JOIN shipments_v v ON v.id=s.id
            where s.period_year='$periode'
            GROUP BY s.rekanan_kode, r.nama_perusahaan
            ORDER BY score_avg DESC, vol DESC";
            
    return $this->db->query($sql)->result_array();
    
  }
  
  
  public function performance_table()
{
  $CI =& get_instance();
  $CI->load->library('Filter_mode');

  $st = $CI->filter_mode->get(); // ['mode','period_y','date_from','date_to']

  $where = [];
  $bind  = [];

  if ($st['mode'] === 'period') {
    $where[] = 's.period_year = ?';
    $bind[]  = (int)$st['period_y'];
  } else {
    $where[] = 'DATE(s.eta_lbe) BETWEEN ? AND ?';
    $bind[]  = date('Y-m-d', strtotime($st['date_from']));
    $bind[]  = date('Y-m-d', strtotime($st['date_to']));
  }

   $whereSql = $where ? ('WHERE '.implode(' AND ', $where)) : '';


 $sql = "SELECT s.rekanan_kode, r.nama_perusahaan,
                   COUNT(*) shipments,
                   SUM(
    CASE
      WHEN NULLIF(s.actual_date, '0000-00-00 00:00:00') IS NOT NULL THEN 1
      ELSE 0
    END
  ) AS shipments_act,
                   SUM(COALESCE(s.volume_plan_mt,0)) vol,
                   SUM(COALESCE(s.volume_actual_mt,0)) vol_act,
                   AVG(
                     CASE
                       WHEN v.o_diff_days <= -1.01 THEN 2
                       WHEN v.o_diff_days BETWEEN -1.00 AND 1.00 THEN 1
                       WHEN v.o_diff_days >= 2.00 THEN -1
                       ELSE 0
                     END
                   ) AS score_avg
            FROM shipments s
            JOIN m_rekanan r ON r.kode=s.rekanan_kode
            JOIN shipments_v v ON v.id=s.id
           $whereSql
            GROUP BY s.rekanan_kode, r.nama_perusahaan
            ORDER BY score_avg DESC, vol DESC";
            
 

  $q = $this->db->query($sql, $bind);
  return $q->result_array();
}

  public function list($filter, $role, $rekanan_kode=null){
    $this->db->select('s.*, r.nama_perusahaan');
    $this->db->from('shipments s');
    $this->db->join('m_rekanan r','r.kode=s.rekanan_kode','left');

    // filter by role (vendor hanya barisnya sendiri)
    if ($role==='vendor' && $rekanan_kode) $this->db->where('s.rekanan_kode', $rekanan_kode);

    if (!empty($filter['rekanan_kode'])) $this->db->where('s.rekanan_kode', $filter['rekanan_kode']);
    if (!empty($filter['shipment_status'])) $this->db->where('s.shipment_status', $filter['shipment_status']);

    // buckets
    if (!empty($filter['bucket'])) {
      if ($filter['bucket']=='planned_far')  $this->db->where("eta_lbe > NOW() + INTERVAL 7 DAY AND shipment_completed=0");
      if ($filter['bucket']=='due_7days')    $this->db->where("eta_lbe BETWEEN NOW() AND NOW() + INTERVAL 7 DAY AND shipment_completed=0");
      if ($filter['bucket']=='delayed')      $this->db->where("eta_lbe < NOW() AND shipment_completed=0");
      if ($filter['bucket']=='completed')    $this->db->where("shipment_completed", 1);
    }

// filter global mode (periode/range) via library
  $CI =& get_instance(); $CI->load->library('Filter_mode');
  $CI->filter_mode->apply_to_db_eta($this->db);
  


    $this->db->order_by('eta_lbe','DESC');
    return $this->db->get()->result_array();
  }
  
   public function list_v($filter, $role, $rekanan_kode=null){
    $this->db->select('s.*, r.nama_perusahaan');
    $this->db->from('shipments_v s');
    $this->db->join('m_rekanan r','r.kode=s.rekanan_kode','left');
    
    // FILTER MODE RANGE
  $CI =& get_instance(); $CI->load->library('Filter_mode');
  $CI->filter_mode->apply_to_db_eta($this->db);
  
    // filter by role (vendor hanya barisnya sendiri)
    if ($role==='vendor' && $rekanan_kode) $this->db->where('s.rekanan_kode', $rekanan_kode);

    if (!empty($filter['rekanan_kode'])) $this->db->where('s.rekanan_kode', $filter['rekanan_kode']);
    
    
    if (!empty($filter['shipment_status'])) $this->db->where('s.shipment_status', $filter['shipment_status']);

    // buckets
    if (!empty($filter['bucket'])) {
      if ($filter['bucket']=='planned_far')  $this->db->where("eta_lbe > NOW() + INTERVAL 7 DAY AND shipment_completed=0");
      if ($filter['bucket']=='due_7days')    $this->db->where("eta_lbe BETWEEN NOW() AND NOW() + INTERVAL 7 DAY AND shipment_completed=0");
      if ($filter['bucket']=='delayed')      $this->db->where("eta_lbe < NOW() AND shipment_completed=0");
      if ($filter['bucket']=='completed')    $this->db->where("shipment_completed", 1);
    }

    if (!empty($filter['from'])) $this->db->where('DATE(eta_lbe) >=', $filter['from']);
    if (!empty($filter['to']))   $this->db->where('DATE(eta_lbe) <=', $filter['to']);

    $this->db->order_by('eta_lbe','DESC');
    return $this->db->get()->result_array();
  }

  public function find($id, $role){
    $row = $this->db->get_where('shipments',['id'=>$id])->row_array();
    return $row;
  }

  public function detail_v($id, $role){
    return $this->db->get_where('shipments_v',['id'=>$id])->row_array();
  }

  public function save_role_based($id, $payload, $role, $user_id){
      // === NOTIF: siapkan model notifikasi sekali ===
$this->load->model('Notification_model','notif');

    $allowed = [];

    if ($role==='vendor'){
      $allowed = ['shipment_no','mmsi','rekanan_kode','nominated_vessel','loading_port',
                  'eta_loading_port','actual_arrival_load_port','commence_loading',
                  'complete_loading','actual_departure','actual_date','eta_lbe','dt_load_sample','complete_disc',
                  'dt_coa_delivery'];
    } elseif ($role==='staff' || $role==='admin' || $role==='super'){
      $allowed = ['shipment_no','mmsi','rekanan_kode','nominated_vessel','loading_port',
                  'eta_loading_port','actual_arrival_load_port','commence_loading',
                  'complete_loading','actual_departure','actual_date','eta_lbe',
                  'commence_disch','complete_disch','volume_plan_mt','volume_actual_mt',
                  'dt_ba_bm','dt_coa_received','dt_sample_received',
                  'dt_inv_delivery_soft','dt_inv_delivery_hard','dt_inv_received',
                  'dt_payment','status_coal_supplier','shipment_status','dt_disch_port',
                  'remarks_aj','dt_sample_request','dt_sample_received2','dt_coa_delivery'];
    }

    $data = array_intersect_key($payload, array_flip($allowed));
    
      // ====== PERIOD LOCK ENFORCEMENT ======

     if ($this->is_period_locked_for_payload($data) && !is_superadmin()){
    show_error('Periode terkunci. Hubungi Admin/Super untuk membuka.', 403);
  }
  
    
    $now = date('Y-m-d H:i:s');
     $this->load->model('Audit_model','audit');


// application/controllers/Shipments.php (di method save)


# pre-check manual sebelum insert/update

         $this->db->where('rekanan_kode', $payload['rekanan_kode']);
         $this->db->where('shipment_no',  @$payload['shipment_no']);
if ($id) $this->db->where('id !=', $id);
$query=$this->db->get('shipments');
 $num_cek = $query->num_rows();
 $this->session->set_flashdata('dup_error', [
    'msg'          => 'ok'
  ]);


if ($num_cek > 0) {
  $this->session->set_flashdata('dup_error', [
    'rekanan_kode' => $payload['rekanan_kode'],
    'shipment_no'  => $payload['shipment_no'],
    'msg'          => 'Shipment dengan kode tersebut untuk vendor ini sudah ada.'
  ]);
  redirect('shipments/create'); // atau balik ke edit
  return;
}




  if ($id){
      
          // ====== AH_PROOF ENFORCEMENT WHEN COMPLETED ======

    if (isset($data['shipment_status'])){
    ///  $err = $this->require_doc_if_completed($id, $data['shipment_status']);
      ///if ($err) show_error($err, 400);
    }
    
    
    
    $before = $this->db->get_where('shipments',['id'=>$id])->row_array();
    // NOTIFIKASI

$data['updated_by'] = $user_id;
$data['updated_at'] = date('Y-m-d H:i:s');
$this->db->update('shipments', $data, ['id'=>$id]);

$after = $this->db->get_where('shipments',['id'=>$id])->row_array();
$this->audit->log('shipment', $id, 'update', $before, $after, ['via'=>'form']);

// === NOTIF: tentukan field berubah & jalankan rule 2-5 ===
$changed = $this->_diff_changed_fields($before, $after, array_keys($data));

$rk = $after['rekanan_kode'] ?? $before['rekanan_kode'] ?? '';

/*
// (2) notifikasi perubahan shipment → vendor terdampak
if (!empty($changed)){
  $this->notif->on_shipment_changed($id, (string)$rk, $changed, (int)$user_id);
}
*/

// (3) jika ADMIN/STAF set sample received / actual → notif ke vendor
// sesuaikan nama kolom yang dipakai di sistemmu
$keys_admin_set = [];
if (array_key_exists('dt_sample_received', $changed)) $keys_admin_set[] = 'Sample Received';
if (array_key_exists('actual_date',        $changed)) $keys_admin_set[] = 'Actual Date';
if (!empty($keys_admin_set) && in_array($role, ['admin','staff','super'])) {
  $this->notif->on_sample_or_actual_set($payload['shipment_no'], (string)$rk, (int)$user_id, $keys_admin_set);
        $this->notif->run();

}

// (4) kalau VENDOR set actual_departure → minta upload COA & COW
if (in_array($role, ['vendor']) && array_key_exists('actual_departure', $changed) && !empty($after['actual_departure'])) {
  $this->notif->on_vendor_set_departure($payload['shipment_no'], (string)$rk, (int)$user_id);
        $this->notif->run();

}

// (5) setiap perubahan oleh VENDOR → kirim ringkasan ke ADMIN
if (in_array($role, ['vendor']) && !empty($changed)) {
   
  $this->notif->on_vendor_change_to_admin($payload['shipment_no'], (string)$rk, $changed, (int)$user_id);
        $this->notif->run();

}

    
    //END NOTIFIKASI
     
     
      
       $data['updated_by'] = $user_id;
    $data['updated_at'] = date('Y-m-d H:i:s');
    $this->db->update('shipments', $data, ['id'=>$id]);

    $after = $this->db->get_where('shipments',['id'=>$id])->row_array();
    $this->audit->log('shipment', $id, 'update', $before, $after, ['via'=>'form']);
    
    } else {
        
            // set period_year default dari eta_lbe / active year
if (empty($data['period_year'])){
        $this->load->model('Period_model');

      $active = $this->Period_model->active_year();
      $data['period_year'] =  $active;
    }
    if ($this->is_period_locked_for_payload($data) && !is_superadmin()){
        
      show_error('Periode terkunci. Hubungi Admin/Super untuk membuka.', 403);
    }
    

      
        $now = date('Y-m-d H:i:s');
    $data['created_by'] = $user_id;
    $data['created_at'] = $now;
    $this->db->insert('shipments', $data);
    $newId = (int)$this->db->insert_id();

    $after = $this->db->get_where('shipments',['id'=>$newId])->row_array();
    $this->audit->log('shipment', $newId, 'create', [], $after, ['via'=>'form']);
    }
  }
  
  private function find_by_shipper_and_no($rekanan_kode, $shipment_no){
  return $this->db->get_where('shipments', [
   
     'rekanan_kode' => trim((string)$rekanan_kode),   // no cast int
    'shipment_no'  => trim((string)$shipment_no)
  ])->row_array();
}

public function import_plan(array $rows, $user_id){
  $inserted=0; $updated=0; $skipped=0;
// === NOTIF: kumpulkan vendor yang terlibat di file import ini
  $vendors_in_file = [];
  
  $this->db->trans_start();
  foreach ($rows as $r){
    $rk = $r['rekanan_kode'] ?? null;
    $sn = $r['shipment_no'] ?? null;
    if (!$rk || !$sn){ $skipped++; continue; }

    // catat vendor
    $vendors_in_file[] = (string)$rk;
    
    
    $payload = [
      'rekanan_kode'     => $rk,
      'shipment_no'      => $sn,
      'nominated_vessel' => $r['nominated_vessel'] ?? null,
      'loading_port'     => $r['loading_port'] ?? null,
      'eta_lbe'     => !empty($r['eta_lbe']) ? date('Y-m-d', strtotime($r['eta_lbe'])) : null,
      'eta_loading_port' => !empty($r['eta_loading_port']) ? date('Y-m-d', strtotime($r['eta_loading_port'])) : null,
      'volume_plan_mt'   => isset($r['volume_plan_mt']) ? (float)$r['volume_plan_mt'] : null,
      'volume_actual_mt'   => isset($r['volume_actual_mt']) ? (float)$r['volume_actual_mt'] : null,
    ];

    $exist = $this->find_by_shipper_and_no($rk, $sn);
    $now = date('Y-m-d H:i:s');
    
    $exists = $this->db->select('1')->from('m_rekanan')
           ->where('kode', $rk)->get()->row_array();
        if (!$exists){ $skipped++; continue; }


   $this->load->model('Period_model');

      $active = $this->Period_model->active_year();
      $payload['period_year'] = $active;

    if ($exist){
      $payload['updated_by'] = $user_id;
      $payload['updated_at'] = $now;
      $this->db->update('shipments', $payload, ['id'=>$exist['id']]);
      $updated++;
    } else {
      $payload['created_by'] = $user_id;
      $payload['created_at'] = $now;
      $this->db->insert('shipments', $payload);
      $inserted++;
    }
  }
  
  $this->db->trans_complete();
  
   // === NOTIF: setelah import selesai → kabari vendor terkait
  $this->load->model('Notification_model','notif');
  if (!empty($vendors_in_file)){
    $vendors_in_file = array_values(array_unique($vendors_in_file));
    $this->notif->on_import($vendors_in_file, (int)$user_id, (int)$inserted, (int)$updated);
          $this->notif->run();

  }
  
  return ['inserted'=>$inserted,'updated'=>$updated,'skipped'=>$skipped];
}

public function kode_by_user($userid){
  $r = $this->by_user($userid);
  return $r ? (string)$r['kode'] : null;
}

public function import_revise(array $rows, $user_id){
  $updated=0; $skipped=0;
  $vendors_in_file = [];
  
  $this->db->trans_start();
  foreach ($rows as $r){
    $rk = isset($r['rekanan_kode']) ? trim((string)$r['rekanan_kode']) : null;
    $sn = isset($r['shipment_no'])  ? trim((string)$r['shipment_no'])  : null;
    if (!$rk || !$sn){ $skipped++; continue; }

    $exist = $this->find_by_shipper_and_no($rk, $sn);
    if (!$exist){ $skipped++; continue; }
    if ((int)$exist['shipment_completed'] === 1){ $skipped++; continue; } // hanya yang belum completed

$vendors_in_file[] = (string)$rk;


    // kolom yang boleh direvisi lewat import oleh Admin:
    $allow = [
      'eta_lbe','commence_disch','complete_disch','volume_plan_mt','shipment_status'
    ];
    $payload = [];
    foreach ($allow as $k){
      if (array_key_exists($k, $r) && $r[$k]!=='' && $r[$k]!==null){
        if (in_array($k, ['commence_disch'])) $payload[$k] = date('Y-m-d', strtotime($r[$k]));
        elseif (in_array($k, ['complete_disch','eta_lbe'])) $payload[$k] = date('Y-m-d H:i:s', strtotime($r[$k]));
        elseif ($k=='volume_plan_mt') $payload[$k] = (float)$r[$k];
        else $payload[$k] = $r[$k];
      }
    }
    if (empty($payload)){ $skipped++; continue; }

// === NOTIF: diff sebelum-sesudah untuk field yang berubah
    $before = $exist; // sudah ada datanya
    $payload['updated_by'] = $user_id;
    $payload['updated_at'] = date('Y-m-d H:i:s');
  
  /*$exists = $this->db->select('1')->from('m_rekanan')
       ->where('kode', $rk)->get()->row_array();
    if (!$exists){ $skipped++; continue; }
    */
        

       $this->db->update('shipments', $payload, ['id'=>$exist['id']]);
    $after = $this->db->get_where('shipments',['id'=>$exist['id']])->row_array();


     $changed = $this->_diff_changed_fields($before, $after, array_keys($payload));
    if (!empty($changed)){
      // admin import → kirim ke vendor terdampak (rule 2)
      $this->load->model('Notification_model','notif');
      $this->notif->on_shipment_changed((int)$exist['id'], (string)$rk, $changed, (int)$user_id);
      $this->notif->run();
    }


    $updated++;
  }
  $this->db->trans_complete();
  
    if (!empty($vendors_in_file)){
    $vendors_in_file = array_values(array_unique($vendors_in_file));
    $this->load->model('Notification_model','notif');
    $this->notif->on_import($vendors_in_file, (int)$user_id, 0+(int)$updated, 0); // revise = update saja
          $this->notif->run();

  }
  
  
  return ['updated'=>$updated,'skipped'=>$skipped];
}

/** ====== EXPORT ====== */
public function export_query($filter, $role='admin', $rekanan_kode=null){
  // ambil dari VIEW untuk kolom turunan (K,O,R,S,T,V, status received kalkulasi)
  $this->db->select('s.*, r.nama_perusahaan');
  $this->db->from('shipments_v s');
  $this->db->join('m_rekanan r','r.kode=s.rekanan_kode','left');

  if ($role==='vendor' && $rekanan_kode) $this->db->where('s.rekanan_kode', $rekanan_kode);
  if (!empty($filter['rekanan_kode'])) $this->db->where('s.rekanan_kode', $filter['rekanan_kode']);
  if (!empty($filter['shipment_status'])) $this->db->where('s.shipment_status', $filter['shipment_status']);

  if (!empty($filter['bucket'])) {
    if ($filter['bucket']=='planned_far')  $this->db->where("s.eta_lbe > NOW() + INTERVAL 7 DAY AND s.shipment_completed=0");
    if ($filter['bucket']=='due_7days')    $this->db->where("s.eta_lbe BETWEEN NOW() AND NOW() + INTERVAL 7 DAY AND s.shipment_completed=0");
    if ($filter['bucket']=='delayed')      $this->db->where("s.eta_lbe < NOW() AND s.shipment_completed=0");
    if ($filter['bucket']=='completed')    $this->db->where("s.shipment_completed", 1);
  }

 // if (!empty($filter['from'])) $this->db->where('DATE(s.eta_lbe) >=', $filter['from']);
 // if (!empty($filter['to']))   $this->db->where('DATE(s.eta_lbe) <=', $filter['to']);
    // FILTER MODE RANGE
  $CI =& get_instance(); $CI->load->library('Filter_mode');
  $CI->filter_mode->apply_to_db_eta($this->db);
  
  $this->db->order_by('s.eta_lbe','DESC');
  return $this->db->get()->result_array();
}


public function period_summary(string $date_from, string $date_to): array
{
    // Normalisasi tanggal (YYYY-MM-DD)
    $df = date('Y-m-d', strtotime($date_from));
    $dt = date('Y-m-d', strtotime($date_to));

    // Plan: kelompok per bulan dari eta_lbe
    $planRows = $this->db->query("
      SELECT DATE_FORMAT(eta_lbe, '%Y-%m') ym,
             SUM(COALESCE(volume_plan_mt,0)) vol_plan,
             COUNT(CASE WHEN COALESCE(volume_plan_mt,0) > 0 THEN 1 END) cnt_plan
      FROM shipments
      WHERE eta_lbe IS NOT NULL
        AND DATE(eta_lbe) BETWEEN ? AND ?
      GROUP BY ym
      ORDER BY ym
    ", [$df, $dt])->result_array();

    // Actual: kelompok per bulan dari actual_date
    $actualRows = $this->db->query("
      SELECT DATE_FORMAT(actual_date, '%Y-%m') ym,
             SUM(COALESCE(volume_actual_mt,0)) vol_actual,
             COUNT(
               CASE WHEN (COALESCE(volume_actual_mt,0) > 0)
                     OR (shipment_status='Completed') THEN 1 END
             ) cnt_actual
      FROM shipments
      WHERE actual_date IS NOT NULL
        AND actual_date BETWEEN ? AND ?
      GROUP BY ym
      ORDER BY ym
    ", [$df, $dt])->result_array();

    // Index-kan untuk merge
    $planIdx = []; foreach ($planRows as $r)  $planIdx[$r['ym']]   = $r;
    $actIdx  = []; foreach ($actualRows as $r) $actIdx[$r['ym']]   = $r;

    // Build deret bulan dari from..to
    $labels = [];
    $cur = new DateTime($df); $end = new DateTime($dt); $cur->modify('first day of this month'); $end->modify('first day of next month');
    while ($cur < $end) {
        $labels[] = $cur->format('Y-m');
        $cur->modify('+1 month');
    }

    $out = [
      'labels'      => [],
      'vol_plan'    => [],
      'vol_actual'  => [],
      'cnt_plan'    => [],
      'cnt_actual'  => [],
      'date_from'   => $df,
      'date_to'     => $dt,
    ];

    foreach ($labels as $ym) {
      $out['labels'][]     = $ym;
      $out['vol_plan'][]   = isset($planIdx[$ym]) ? (float)$planIdx[$ym]['vol_plan'] : 0.0;
      $out['vol_actual'][] = isset($actIdx[$ym])  ? (float)$actIdx[$ym]['vol_actual'] : 0.0;
      $out['cnt_plan'][]   = isset($planIdx[$ym]) ? (int)$planIdx[$ym]['cnt_plan'] : 0;
      $out['cnt_actual'][] = isset($actIdx[$ym])  ? (int)$actIdx[$ym]['cnt_actual'] : 0;
    }

    return $out;
}



}
