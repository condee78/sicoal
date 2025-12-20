<?php $this->load->view('layout/header', ['title'=>'Import Excel','breadcrumb'=>['Shipments'=>site_url('shipments'),'Import'=>null]]); ?>

<div class="row g-3">
  <div class="col-12 col-lg-7">
    <div class="card shadow-sm">
      <div class="card-header bg-white"><h6 class="m-0"><i class="bi bi-file-earmark-spreadsheet me-1"></i> Import Excel</h6></div>
      <div class="card-body">
        <?php if($this->session->flashdata('ok')): ?>
          <div class="alert alert-success"><?= $this->session->flashdata('ok') ?></div>
        <?php endif; ?>
        <?php if($this->session->flashdata('err')): ?>
          <div class="alert alert-danger"><?= $this->session->flashdata('err') ?></div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
          <div class="mb-3">
            <label class="form-label">Tipe Import</label>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="mode" id="mode_plan" value="plan" checked>
              <label class="form-check-label" for="mode_plan">PLAN (insert/update awal tahun/rolling)</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="mode" id="mode_revise" value="revise">
              <label class="form-check-label" for="mode_revise">REVISE (hanya baris belum Completed)</label>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">File Excel (.xlsx/.xls)</label>
            <input type="file" class="form-control" name="excel" accept=".xlsx,.xls" required>
            <div class="form-text">Header ada di baris 5 (A5..), isi data mulai baris 6.</div>
          </div>

          <div class="d-grid d-sm-flex gap-2">
            <button class="btn btn-primary"><i class="bi bi-upload me-1"></i> Proses Import</button>
            <a class="btn btn-outline-secondary" href="<?= site_url('shipments') ?>">Kembali</a>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-5">
    <div class="card shadow-sm">
      <div class="card-header bg-white"><h6 class="m-0">Template</h6></div>
      <div class="card-body small">
        <ul class="mb-2">
          <li>Template PLAN: <a href="/import_template_plan.xlsx">Download</a></li>
          <li>Template REVISE: <a href="/import_template_revise.xlsx">Download</a></li>
        </ul>
        <p class="mb-1"><b>PLAN minimal kolom:</b></p>
        <ul>
          <li>Shipper Code (B$)</li><li>Shipment No. (C$)</li><li>Nominated Vessel (D$)</li>
          <li>Loading Port (E$)</li><li>ETA Loading Port (F$)</li><li>ETA at LBE (M$)</li><li>Volume Plan (MT) (L$)</li>
        </ul>
        <p class="mb-1"><b>REVISE minimal kolom:</b></p>
        <ul>
          <li>Shipper Code (B$), Shipment No. (C$) + kolom target revisi (mis. ETA at LBE, Commence/Complete Disch, Volume Plan, Shipment Status)</li>
        </ul>
        <p class="text-muted">Format tanggal gunakan format Excel (bukan teks).</p>
      </div>
    </div>
  </div>
</div>

<?php $this->load->view('layout/footer'); ?>
