<?php defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as XlsDate;

class ExcelReader {

  protected $CI;
  public function __construct(){
    $this->CI =& get_instance();
    // coba autoload composer
    $composer1 = FCPATH.'vendor/autoload.php';
    $composer2 = APPPATH.'third_party/vendor/autoload.php';
    if (file_exists($composer1)) require_once $composer1;
    elseif (file_exists($composer2)) require_once $composer2;
  }

  /**
   * Baca excel menjadi array asosiatif (header ada di baris ke-5 → index 4)
   * @param string $path
   * @param int $headerRowIndex 0-based (default 4 untuk A5)
   * @return array [rows=>[], headers=>[]]
   */
  public function read_assoc($path, $headerRowIndex=4){
    $spreadsheet = IOFactory::load($path);
    $sheet = $spreadsheet->getActiveSheet();
    $highestRow = $sheet->getHighestRow();
    $highestCol = $sheet->getHighestColumn();
    $highestColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestCol);

    // header
    $headers = [];
    for ($c=1; $c<=$highestColIndex; $c++){
      $val = trim((string)$sheet->getCellByColumnAndRow($c, $headerRowIndex+1)->getValue());
      $headers[] = $val;
    }

    // mapping header → key standard
    $map = $this->header_map();

    $keys = [];
    foreach($headers as $h){
      $norm = $this->normalize_header($h);
      $keys[] = $map[$norm] ?? $norm; // fallback ke norm
    }

    // data rows
    $rows = [];
    for ($r=$headerRowIndex+2; $r<=$highestRow; $r++){
      // stop jika baris kosong total
      $empty = true; $row = [];
      for ($c=1; $c<=$highestColIndex; $c++){
        $val = $sheet->getCellByColumnAndRow($c, $r)->getValue();
        if ($val !== null && $val !== ''){
          $empty = false;
        }
        // jika kolom volume_plan_mt maka langsung saja, karena numeric
        if($keys[$c-1]=='volume_plan_mt'or $keys[$c-1]=='volume_actual_mt'){
            $row[$keys[$c-1] ?? ('col_'.$c)] = ($val);
        }else{
            $row[$keys[$c-1] ?? ('col_'.$c)] = $this->normalize_value($val);
        }
        
      }
      if ($empty) continue;
      $rows[] = $row;
    }

    return ['rows'=>$rows, 'headers'=>$headers];
  }

  private function normalize_header($s){
    $s = strtolower(trim($s));
    $s = preg_replace('/\s+/', ' ', $s);
    $s = str_replace(['(mt)','(b$)','(c$)','(d$)','(e$)','(f$)','(l$)','(n$)','(p$)','(q$)','(u$)','(w$)','(x$)','(y$)','(z$)','(aa$)','(ah$)'], '', $s);
    return trim($s);
  }

  private function normalize_value($v){
    // numeric excel date?
    if (is_numeric($v) && $v > 20000 && $v < 60000){
      // kemungkinan excel serial date
      try {
        $ts = XlsDate::excelToTimestamp($v);
        return date('Y-m-d H:i:s', $ts);
      } catch (\Throwable $e) {}
    }
    if ($v instanceof \DateTime) return $v->format('Y-m-d H:i:s');
    if (is_string($v)){
      $v = trim($v);
      // coba parse tanggal jam
      $t = strtotime($v);
      if ($t !== false && $t > 0) return date('Y-m-d H:i:s', $t);
    }
    return $v === '' ? null : $v;
  }

  private function header_map(){
    // sinonim header → key DB
    return [
      'shipper code' => 'rekanan_kode',
       'shipper' => 'rekanan_kode',
      'shipment no.' => 'shipment_no',
      'shipment no'  => 'shipment_no',
      'nominated vessel' => 'nominated_vessel',
      'loading port' => 'loading_port',
      'eta loading port' => 'eta_loading_port',
      'eta at lbe' => 'eta_lbe',
      'plan date'  => 'actual_date',     // M$
      'commence disch' => 'commence_disch',
      'complete disch' => 'complete_disch', 
      'volume (mt)' => 'volume_plan_mt',
      'volume plan (mt)' => 'volume_plan_mt',
      'volume plan'    => 'volume_plan_mt',
     'MMSI'    => 'mmsi',
     
     
      'volume actual'  => 'volume_actual_mt',
      'volume actual (mt)' => 'volume_actual_mt',
      'ba bongkar muat' => 'dt_ba_bm',
      'coa delivery date' => 'dt_coa_delivery',
      'coa received date' => 'dt_coa_received',
      'load sample' => 'dt_load_sample',
      'sample received' => 'dt_sample_received',
      'invoice delivery date to lbe (softcopy)' => 'dt_inv_delivery_soft',
      'invoice delivery date to lbe (hardcopy)' => 'dt_inv_delivery_hard',
      'invoice receive by finance (hc)' => 'dt_inv_received',
      'payment by lbe' => 'dt_payment',
      'coal supplier confirmation' => 'status_coal_supplier',
      'shipment completed' => 'shipment_status',
      'discharging port analysis (as received analysis) date' => 'dt_disch_port',
      'as received analysis comment' => 'remarks_aj',
      '2nd split sample request' => 'dt_sample_request',
      '2nd split sample received' => 'dt_sample_received2',
      'shipment status' => 'shipment_status',
    ];
  }
}
