<?php
  $this->load->view('layout/header', [
    'title'=>'Preview Import '.strtoupper($mode),
    'breadcrumb'=>['Shipments'=>site_url('shipments'),'Import'=>site_url('shipments/import'),'Preview'=>null]
  ]);
  $sum = $report['summary'];
?>
<div class="card shadow-sm">
  <div class="card-header bg-white d-flex align-items-center justify-content-between">
    <h6 class="m-0"><i class="bi bi-eye me-1"></i> Preview Import (<?= strtoupper($mode) ?>)</h6>
    <div class="d-flex gap-2">
      <span class="badge bg-success">Valid: <?= $sum['valid'] ?></span>
      <span class="badge bg-warning text-dark">Warnings: <?= $sum['warnings'] ?></span>
      <span class="badge bg-danger">Errors: <?= $sum['errors'] ?></span>
      <span class="badge bg-secondary">Total Rows: <?= $sum['total'] ?></span>
    </div>
  </div>
  <div class="card-body">

    <form method="post" action="<?= site_url('shipments/import/commit') ?>" class="mb-3">
      <input type="hidden" name="token" value="<?= $token ?>">
      <div class="row g-2 align-items-center">
        <div class="col-12 col-md-8">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="only_valid" id="only_valid" checked>
            <label class="form-check-label" for="only_valid">Import hanya baris <b>VALID</b> (abaikan baris yang punya errors)</label>
          </div>
          <div class="small text-muted">Baris dengan errors akan di-skip jika opsi di atas dicentang.</div>
        </div>
        <div class="col-12 col-md-4 d-grid d-md-flex justify-content-md-end gap-2">
          <a href="<?= site_url('shipments/import') ?>" class="btn btn-outline-secondary">Kembali</a>
          <button class="btn btn-primary"><i class="bi bi-cloud-arrow-up me-1"></i> Commit Import</button>
        </div>
      </div>
    </form>

    <div class="table-responsive">
      <table class="table table-sm table-hover align-middle datatable">
        <thead>
          <tr>
            <th>#</th>
            <th>Shipper Code</th>
            <th>Shipment No</th>
            <th>MMSI / IMO</th>
            <th>Vessel</th>
            
            <th>ETA Loading Port</th>
            <th>ETA LBE</th>
           <!-- <th>Commence Disch</th>
            <th>Complete Disch</th>-->
            <th>Volume Plan</th>
            <th>Volume Actual</th>
            <th>Issues</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($sample as $i=>$r):
            $rowrep = $report['rows'][$i] ?? ['errors'=>[],'warnings'=>[]];
            $bad = !empty($rowrep['errors']);
            $warn= !empty($rowrep['warnings']);
          ?>
          <tr class="<?= $bad?'table-danger':($warn?'table-warning':'') ?>">
            <td><?= $i+1 ?></td>
            <td><?= htmlentities($r['rekanan_kode'] ?? '') ?></td>
            <td><?= htmlentities($r['shipment_no'] ?? '') ?></td>
            <td><?= htmlentities($r['mmsi'] ?? '') ?></td>
           <td><?= htmlentities($r['nominated_vessel'] ?? '') ?></td> 
            <td><?= htmlentities($r['eta_loading_port'] ?? '') ?></td>
            <td><?= htmlentities($r['eta_lbe'] ?? '') ?></td>
            <!--<td><?= htmlentities($r['commence_disch'] ?? '') ?></td>
            <td><?= htmlentities($r['complete_disch'] ?? '') ?></td>-->
            <td><?= htmlentities($r['volume_plan_mt'] ?? '') ?></td>
            <td><?= htmlentities($r['volume_actual_mt'] ?? '') ?></td>
            <td>
              <?php foreach(($rowrep['errors']??[]) as $e): ?>
                <span class="badge bg-danger me-1 mb-1"><?= htmlentities($e) ?></span>
              <?php endforeach; ?>
              <?php foreach(($rowrep['warnings']??[]) as $w): ?>
                <span class="badge bg-warning text-dark me-1 mb-1"><?= htmlentities($w) ?></span>
              <?php endforeach; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php if ($sum['total']>count($sample)): ?>
        <div class="small text-muted mt-2">Menampilkan 50 baris pertama dari total <?= $sum['total'] ?> baris.</div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php $this->load->view('layout/footer'); ?>
