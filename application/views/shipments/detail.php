<?php
  $title = 'Shipment Detail #'.$data['id'];
  $this->load->view('layout/header', ['title'=>$title,'breadcrumb'=>['Shipments'=>site_url('shipments'), $title=>null]]);
  $role = role_code();
  $receiving = $data['receiving_status'] ?? $data['received_status_calc'] ?? '-';
?>
<div class="row g-3">
  <div class="col-12 col-lg-8">
      <?php
  $this->load->model('Docreq_model','Docreq_model', true);
  $missing = $this->Docreq_model->missing_for_shipment($data['id'], role_code());
  if (!empty($missing)):
?>
<div class="alert alert-warning">
  <div class="fw-semibold mb-1">Dokumen wajib yang belum diunggah:</div>
  <ul class="mb-0">
    <?php foreach($missing as $m): ?>
      <li><?= htmlentities($m['label']) ?> <span class="text-muted">(<?= $m['doc_type'] ?>)</span></li>
    <?php endforeach; ?>
  </ul>
</div>
<?php endif; ?>


    <div class="card shadow-sm">
     
      <div class="card-header bg-white d-flex align-items-center justify-content-between">
  <h6 class="m-0"><?= htmlentities($data['shipment_no']) ?> — <?= htmlentities($data['nominated_vessel']) ?></h6>
  <div class="d-flex align-items-center gap-2">
    <?php if (is_admin_lbe() || is_superadmin()): ?>
      <a class="btn btn-sm btn-outline-secondary"
         href="<?= site_url('audit?entity=shipment&entity_id='.$data['id']) ?>">
        <i class="bi bi-clock-history"></i> Audit
      </a>
    <?php endif; ?>
    <span class="badge <?= ($data['shipment_status']=='Completed'?'bg-success':($data['shipment_status']=='On process'?'bg-warning text-dark':'bg-secondary')) ?>">
      <?= $data['shipment_status'] ?: '-' ?>
    </span>
  </div>
</div>

      <div class="card-body">
        <div class="row g-2">
          <div class="col-4 col-md-4">
            <div class="small text-muted">Shipper</div>
            <div class="fw-semibold"><?= htmlentities($data['nama_perusahaan'] ?? $data['rekanan_kode']) ?></div>
          </div>
          <div class="col-4 col-md-4">
            <div class="small text-muted">Loading Port</div>
            <div class="fw-semibold"><?= htmlentities($data['loading_port'] ?? '-') ?></div>
          </div>
          <div class="col-4 col-md-4">
            <div class="small text-muted">ETA LBE (N$)</div>
            <div class="fw-semibold"><?= $data['eta_lbe'] ? tgl_time($data['eta_lbe']) : '-' ?></div>
          </div>
           <div class="col-4 col-md-4">
            <div class="small text-muted">Actual Arriving LBE (M$)</div>
            <div class="fw-semibold"><?= $data['actual_date'] ? tgl_time($data['actual_date']) : '-' ?></div>
          </div>
        </div>

        <hr>

        <div class="row g-3">
          <?php
            $metric = [
              ['label'=>'K: Loading Days','val'=>$data['k_total_loading_days'],'suffix'=>'days'],
              ['label'=>'O: N-M (days)','val'=>$data['o_diff_days'],'suffix'=>'days'],
              ['label'=>'R: P-N (days)','val'=>$data['r_diff_days'],'suffix'=>'days'],
              ['label'=>'S: Q-P (days)','val'=>$data['s_diff_days'],'suffix'=>'days'],
              ['label'=>'T: L/S','val'=>$data['t_throughput'],'suffix'=>''],
              ['label'=>'V: U-L (MT)','val'=>$data['v_variance'],'suffix'=>' MT'],
            ];
          ?>
          <?php foreach($metric as $m): ?>
          <div class="col-6 col-md-4">
            <div class="p-3 border rounded-3 bg-light h-100">
              <div class="small text-muted"><?= $m['label'] ?></div>
              <div class="fs-5 fw-bold"><?= is_null($m['val'])?'-':number_format($m['val'],2) ?><?= $m['suffix'] ?></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>

        <hr>

        <div class="row g-2">
          <div class="col-6 col-md-4">
            <div class="small text-muted">Volume Plan (L$)</div>
            <div class="fw-semibold"><?= number_format($data['volume_plan_mt'] ?? 0,2) ?> MT</div>
          </div>
          <div class="col-6 col-md-4">
            <div class="small text-muted">Volume Actual (U$)</div>
            <div class="fw-semibold"><?= number_format($data['volume_actual_mt'] ?? 0,2) ?> MT</div>
          </div>
          <div class="col-6 col-md-4">
            <div class="small text-muted">Receiving (AB$)</div>
            <div class="fw-semibold"><?= htmlentities($receiving) ?></div>
          </div>
        </div>

        <hr>

        <div class="row row-cols-1 row-cols-md-2 g-3">
          <?php
            $timeline = [
              ['label'=>'Arrival Load Port (G$)','val'=>$data['actual_arrival_load_port']],
              ['label'=>'Commence Loading (H$)','val'=>$data['commence_loading']],
              ['label'=>'Complete Loading (I$)','val'=>$data['complete_loading']],
              ['label'=>'Actual Departure (J$)','val'=>$data['actual_departure']],
              ['label'=>'COA Delivery (X$)','val'=>$data['dt_coa_delivery']],
              ['label'=>'COA Received (Y$)','val'=>$data['dt_coa_received']],
              ['label'=>'Load Sample (Z$)','val'=>$data['dt_load_sample']],
              ['label'=>'Sample Received (AA$)','val'=>$data['dt_sample_received']],
              ['label'=>'BA Bongkar Muat (W$)','val'=>$data['dt_ba_bm']],
              ['label'=>'Inv Softcopy (AC$)','val'=>$data['dt_inv_delivery_soft']],
              ['label'=>'Inv Hardcopy (AD$)','val'=>$data['dt_inv_delivery_hard']],
              ['label'=>'Invoice Received (AE$)','val'=>$data['dt_inv_received']],
              ['label'=>'Payment (AF$)','val'=>$data['dt_payment']],
              ['label'=>'Disch Port Date (AI$)','val'=>$data['dt_disch_port']],
              ['label'=>'Sample Request (AK$)','val'=>$data['dt_sample_request']],
              ['label'=>'2nd Sample Received (AL$)','val'=>$data['dt_sample_received2']],
            ];
          ?>
          <?php foreach($timeline as $t): ?>
          <div class="col">
            <div class="border-start ps-3">
              <div class="small text-muted"><?= $t['label'] ?></div>
              <div class="fw-semibold">
                <?= $t['val'] ? tgl_time($t['val']) : '-' ?>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>

        <?php if(!empty($data['remarks_aj'])): ?>
        <hr>
        <div>
          <div class="small text-muted">Remarks</div>
          <div><?= nl2br(htmlentities($data['remarks_aj'])) ?></div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Files -->
  <div class="col-12 col-lg-4">
    <div class="card shadow-sm">
      <div class="card-header bg-white d-flex align-items-center justify-content-between">
        <h6 class="m-0">Dokumen</h6>
      </div>
      <div class="card-body">
        <?php if($this->session->flashdata('ok')): ?>
          <div class="alert alert-success py-2"><?= $this->session->flashdata('ok') ?></div>
        <?php endif; ?>
        <?php if($this->session->flashdata('err')): ?>
          <div class="alert alert-danger py-2"><?= $this->session->flashdata('err') ?></div>
        <?php endif; ?>

        <!-- Upload form -->
        <form method="post" enctype="multipart/form-data" action="<?= site_url('files/upload/'.$data['id']) ?>" class="row g-2">
          <div class="col-12">
            <label class="form-label small">Jenis Dokumen</label>
            <select name="doc_type" class="form-select" required>
              <?php if (is_vendor()): ?>
                <option value="COA">COA</option>
                <option value="AWB">AWB</option>
                <option value="OTHER_VENDOR">Other (Vendor)</option>
              <?php else: ?>
                <option value="BA_BM">BA Bongkar Muat</option>
                <option value="LAB_RESULT">Lab Result</option>
                <option value="INVOICE_SC">Invoice Softcopy</option>
                <option value="INVOICE_HC">Invoice Hardcopy</option>
                <option value="AH_PROOF">Proof AH</option>
                <option value="COA">COA</option>
                <option value="AWB">AWB</option>
                <option value="OTHER_VENDOR">Other (Vendor)</option>
              <?php endif; ?>
            </select>
          </div>
          <div class="col-12">
            <input type="file" name="file" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx,.csv" required>
            <div class="form-text">Maks 10 MB</div>
          </div>
          <div class="col-12 d-grid">
            <button class="btn btn-primary btn-sm"><i class="bi bi-upload me-1"></i> Upload</button>
          </div>
        </form>

        <hr>

        <!-- List files -->
        <?php
          $files = $this->File_model->by_shipment($data['id']);
          if (!$files) echo '<div class="text-muted small">Belum ada dokumen.</div>';
        ?>
        <?php foreach($files as $f): ?>
          <div class="d-flex align-items-center justify-content-between border rounded-3 p-2 mb-2">
            <div class="me-2 small">
              <div class="fw-semibold"><?= htmlentities($f['doc_type']) ?></div>
              <div class="text-muted"><?= htmlentities($f['original_name']) ?></div>
              <div class="text-muted"><?= date('Y-m-d H:i', strtotime($f['uploaded_at'])) ?></div>
            </div>
            <div class="btn-group btn-group-sm">
              <a class="btn btn-outline-secondary" href="<?= site_url('files/download/'.$f['id']) ?>"><i class="bi bi-download"></i></a>
              <?php if (is_admin_lbe() || is_superadmin() || is_staff_lbe()): ?>
                <a class="btn btn-outline-danger" href="<?= site_url('files/delete/'.$f['id']) ?>" onclick="return confirm('Hapus file ini?')"><i class="bi bi-trash"></i></a>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<?php $this->load->view('layout/footer'); ?>
