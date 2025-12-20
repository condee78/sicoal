<?php $this->load->view('layout/header', ['title'=>'Shipments','breadcrumb'=>['Shipments'=>null]]); ?>

<div class="card shadow-sm">
    
   <div class="card-header bg-white">
       
<form method="get" class="row gy-2 gx-2 align-items-end mb-3">
  <div class="col-12 col-md-auto">
    <label class="form-label d-block mb-1">Mode</label>
    <div class="btn-group" role="group">
      <input type="radio" class="btn-check" name="mode" id="mdPeriod" value="period" <?= ($filter['mode']==='period'?'checked':'') ?>>
      <label class="btn btn-outline-primary" for="mdPeriod">Periode</label>
      <input type="radio" class="btn-check" name="mode" id="mdRange" value="range" <?= ($filter['mode']==='range'?'checked':'') ?>>
      <label class="btn btn-outline-primary" for="mdRange">Range Tanggal</label>
    </div>
  </div>

  <div class="col-6 col-md-2 mode-period">
    <label class="form-label">Tahun</label>
    <input type="number" class="form-control" name="period_y" value="<?= (int)$filter['period_y'] ?>">
  </div>

  <div class="col-6 col-md-2 mode-range">
    <label class="form-label">Dari (ETA LBE)</label>
    <input type="date" class="form-control" name="date_from" value="<?= htmlentities($filter['date_from']) ?>">
  </div>
  <div class="col-6 col-md-2 mode-range">
    <label class="form-label">Sampai</label>
    <input type="date" class="form-control" name="date_to" value="<?= htmlentities($filter['date_to']) ?>">
  </div>

  <div class="col-12 col-md-auto">
    <button class="btn btn-primary"><i class="bi bi-filter"></i> Terapkan</button>
  </div>
</form>

<script>
(function(){
  const mode = '<?= $filter['mode'] ?>';
  function toggle(){
    document.querySelectorAll('.mode-period').forEach(el=> el.style.display = (document.getElementById('mdPeriod').checked?'block':'none'));
    document.querySelectorAll('.mode-range').forEach(el=> el.style.display  = (document.getElementById('mdRange').checked?'block':'none'));
  }
  document.getElementById('mdPeriod').addEventListener('change', toggle);
  document.getElementById('mdRange').addEventListener('change', toggle);
  toggle();
})();
</script>

    <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
      <h6 class="m-0"><i class="bi bi-truck-front me-1"></i> Shipments - Grid</h6>
      <div class="d-flex flex-wrap gap-2">
            <a href="<?= site_url('shipments?view=grid') ?>" class="btn btn-sm btn-outline-primary">
        <i class="bi bi-grid-3x3-gap"></i> Grid (Excel)
      </a>
          <?php if (is_superadmin() || is_admin_lbe() || is_staff_lbe()): ?>
  <a href="<?= site_url('shipments/create') ?>" class="btn btn-sm btn-success">
    <i class="bi bi-plus-circle"></i> Tambah Shipment
  </a>
<?php endif; ?>
        <?php if (is_admin_lbe() || is_superadmin()): ?>
          <a href="<?= site_url('shipments/import') ?>" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-file-earmark-spreadsheet"></i> Import
          </a>
        <?php endif; ?>
        <a href="<?= site_url('shipments/export?'.http_build_query($filter??[])) ?>" class="btn btn-sm btn-primary">
          <i class="bi bi-box-arrow-up"></i> Export Excel
        </a>
      </div>
    </div>
  </div>
  <div class="card-body">
      <?php if($this->session->flashdata('ok')): ?>
  <div class="alert alert-success"><?= $this->session->flashdata('ok') ?></div>
<?php endif; ?>
<?php if($this->session->flashdata('err')): ?>
  <div class="alert alert-danger"><?= $this->session->flashdata('err') ?></div>
<?php endif; ?>
    <form class="row g-2 mb-3" method="get">
      <div class="col-12 col-sm-6 col-lg-3">
        <label class="form-label small">Shipper</label>
        <select name="rekanan_kode" class="form-select">
          <option value="">Semua</option>
          <?php foreach($shippers as $s): ?>
            <option value="<?= $s['kode'] ?>" <?= ($filter['rekanan_kode']??'')==$s['kode']?'selected':'' ?>>
              <?= htmlentities($s['nama_perusahaan']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-6 col-lg-2">
        <label class="form-label small">Status</label>
        <select name="shipment_status" class="form-select">
          <option value="">Semua</option>
          <?php foreach(['Completed','On process','Not Completed'] as $st): ?>
            <option <?= (($filter['shipment_status']??'')==$st)?'selected':'' ?>><?= $st ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-6 col-lg-2">
        <label class="form-label small">Bucket</label>
        <select name="bucket" class="form-select">
          <option value="">Semua</option>
          <option <?= (($filter['bucket']??'')=='planned_far')?'selected':'' ?> value="planned_far">Planned > 7d</option>
          <option <?= (($filter['bucket']??'')=='due_7days')?'selected':'' ?> value="due_7days">Due in 7d</option>
          <option <?= (($filter['bucket']??'')=='delayed')?'selected':'' ?> value="delayed">Delayed</option>
          <option <?= (($filter['bucket']??'')=='completed')?'selected':'' ?> value="completed">Completed</option>
        </select>
      </div>
      <div class="col-6 col-lg-2">
        <label class="form-label small">Periode (From)</label>
        <input type="date" name="from" value="<?= $filter['from']??'' ?>" class="form-control">
      </div>
      <div class="col-6 col-lg-2">
        <label class="form-label small">Periode (To)</label>
        <input type="date" name="to" value="<?= $filter['to']??'' ?>" class="form-control">
      </div>
      <div class="col-12 col-lg-1 d-grid">
        <label class="form-label small">&nbsp;</label>
        <button class="btn btn-dark"><i class="bi bi-search"></i></button>
      </div>
    </form>

    <div class="table-responsive">
      <table class="table table-hover table-sm align-middle datatable">
        <thead>
          <tr>
            <th>#</th>
            <th>Shipment No</th>
            <th>Shipper</th>
            <th>Vessel</th>
            <th>ETA LBE</th>
            <th>Plan (MT)</th>
            <th>Actual (MT)</th>
            <th>Status</th>
            <th class="text-end">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php $i=1; foreach($rows as $r): ?>
            <tr>
              <td><?= $i++ ?></td>
              <td><?= htmlentities($r['shipment_no']) ?></td>
              <td><?= htmlentities($r['nama_perusahaan']) ?></td>
              <td><?= htmlentities($r['nominated_vessel']) ?> <a class="open-modal" href='https://coal.lbebanten.com/vessel.php?mmsi=<?= htmlentities($r['mmsi']) ?>'> <?= htmlentities($r['mmsi']) ?></a></td>
              <td><?= $r['eta_lbe'] ? date('Y-m-d H:i', strtotime($r['eta_lbe'])) : '-' ?></td>
              <td><?= number_format($r['volume_plan_mt'],2) ?></td>
              <td><?= number_format($r['volume_actual_mt']??0,2) ?></td>
              <td>
                <span class="badge 
                  <?= ($r['shipment_status']=='Completed'?'bg-success':
                      ($r['shipment_status']=='On process'?'bg-warning text-dark':'bg-secondary')) ?>">
                  <?php $status=getShipmentStatus($r);
                 echo $status['last_completed_label'];
                  /*    'steps' => $results,
        'completed_steps' => $completedCount,
        'total_steps' => $totalSteps,
        'progress_percent' => round($percent, 2),
        'status_label' => ($completedCount == $totalSteps) ? 'Fully Completed' : 'In Progress'
        */
                  ?>
                </span>
              </td>
              
              <td class="text-end">
  <a class="btn btn-sm btn-outline-primary" href="<?= site_url('shipments/edit/'.$r['id']) ?>">
    <i class="bi bi-pencil-square"></i>
  </a>
  <a class="btn btn-sm btn-outline-secondary" href="<?= site_url('shipments/detail/'.$r['id']) ?>">
    <i class="bi bi-eye"></i>
  </a>
  <?php if (is_admin_lbe() || is_superadmin()): ?>
    <a class="btn btn-sm btn-outline-dark" title="Audit"
       href="<?= site_url('audit?entity=shipment&entity_id='.$r['id']) ?>">
      <i class="bi bi-clock-history"></i>
    </a>
  <?php endif; ?>
</td>

            </tr>
          <?php endforeach;?>
        </tbody>
      </table>
    </div>
  </div>
</div>
  <div class="modal fade" id="modalRemote" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalTitle">Memuat...</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body" id="modalBody">
          <div class="text-center p-4">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2">Sedang memuat...</p>
          </div>
        </div>
      </div>
    </div>
  </div>
  

<?php $this->load->view('layout/footer'); ?>
