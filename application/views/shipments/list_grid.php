<?php
$this->load->view('layout/header', ['title'=>'Shipments (Grid)','breadcrumb'=>['Shipments'=>null]]);
?>
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
            <a href="<?= site_url('shipments?view=cards') ?>" class="btn btn-sm btn-outline-primary">
        <i class="bi bi-grid-3x3-gap"></i> Standard
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
    <!-- Toggle Kolom -->
    <div class="mb-2">
      <div class="small text-muted">Tampilkan/sembunyikan kolom:</div>
      <div class="d-flex flex-wrap gap-2" id="colToggles"></div>
      <div class="mt-2">
        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnShowCommon">Kolom Umum</button>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnShowAll">Semua Kolom</button>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnHideAll">Sembunyikan Semua</button>
      </div>
    </div>

    <div class="table-responsive" style="max-height:70vh; overflow:auto;">
      <table class="table table-sm table-bordered align-middle" id="gridTable" style="white-space:nowrap;">
        <thead class="table-light sticky-top">
          <tr>
            <th>#</th>
            <th>A: ID</th>
            <th>B: Kode Shipper</th>
            <th>Nama Shipper</th>
            <th>C: Shipment No</th>
            <th>D: Vessel</th>
            <th>E: Loading Port</th>
            <th>F: ETA Load Port (date)</th>
            <th>G: Arrival Load Port (dt)</th>
            <th>H: Commence Loading (dt)</th>
            <th>I: Complete Loading (dt)</th>
            <th>J: Actual Departure (dt)</th>
            <th>K: Loading Days (calc)</th>
            <th>L: Plan (MT)</th>
            <th>M: Actual Date (date)</th>
            <th>N: ETA LBE (dt)</th>
            <th>O: N-M (calc)</th>
            <th>P: Commence Disch (date)</th>
            <th>Q: Complete Disch (dt)</th>
            <th>R: P-N (calc)</th>
            <th>S: Q-P (calc)</th>
            <th>T: L/S (calc)</th>
            <th>U: Actual (MT)</th>
            <th>V: U-L (calc)</th>
            <th>W: BA/BM (dt)</th>
            <th>X: COA Delivery (dt)</th>
            <th>Y: COA Received (dt)</th>
            <th>Z: Load Sample (dt)</th>
            <th>AA: Sample Received (dt)</th>
            <th>AB: Receiving Status</th>
            <th>AC: Inv Soft (dt)</th>
            <th>AD: Inv Hard (dt)</th>
            <th>AE: Inv Received (dt)</th>
            <th>AF: Payment (date)</th>
            <th>AG: Supplier Status</th>
            <th>AH: Shipment Status</th>
            <th>AI: Disch Port Date</th>
            <th>AJ: Remarks</th>
            <th>AK: Sample Request</th>
            <th>AL: 2nd Sample Received</th>
            <th>Created</th>
            <th>Updated</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php $i=1; foreach($rows as $r): ?>
          <tr>
            <td><?= $i++ ?></td>
            <td><?= (int)$r['id'] ?></td>
            <td><?= htmlentities($r['rekanan_kode']) ?></td>
            <td><?= htmlentities($r['nama_perusahaan'] ?? '') ?></td>
            <td><?= htmlentities($r['shipment_no']) ?></td>
            <td><?= htmlentities($r['nominated_vessel']) ?></td>
            <td><?= htmlentities($r['loading_port']) ?></td>
            <td><?= $r['eta_loading_port'] ? date('Y-m-d', strtotime($r['eta_loading_port'])) : '' ?></td>
            <td><?= $r['actual_arrival_load_port'] ? date('Y-m-d H:i', strtotime($r['actual_arrival_load_port'])) : '' ?></td>
            <td><?= $r['commence_loading'] ? date('Y-m-d H:i', strtotime($r['commence_loading'])) : '' ?></td>
            <td><?= $r['complete_loading'] ? date('Y-m-d H:i', strtotime($r['complete_loading'])) : '' ?></td>
            <td><?= $r['actual_departure'] ? date('Y-m-d H:i', strtotime($r['actual_departure'])) : '' ?></td>
            <td><?= is_null($r['k_total_loading_days'])?'':number_format($r['k_total_loading_days'],2) ?></td>
            <td><?= number_format($r['volume_plan_mt'] ?? 0,2) ?></td>
            <td><?= $r['actual_date'] ? date('Y-m-d', strtotime($r['actual_date'])) : '' ?></td>
            <td><?= $r['eta_lbe'] ? date('Y-m-d H:i', strtotime($r['eta_lbe'])) : '' ?></td>
            <td><?= is_null($r['o_diff_days'])?'':number_format($r['o_diff_days'],2) ?></td>
            <td><?= $r['commence_disch'] ? date('Y-m-d', strtotime($r['commence_disch'])) : '' ?></td>
            <td><?= $r['complete_disch'] ? date('Y-m-d H:i', strtotime($r['complete_disch'])) : '' ?></td>
            <td><?= is_null($r['r_diff_days'])?'':number_format($r['r_diff_days'],2) ?></td>
            <td><?= is_null($r['s_diff_days'])?'':number_format($r['s_diff_days'],2) ?></td>
            <td><?= is_null($r['t_throughput'])?'':number_format($r['t_throughput'],2) ?></td>
            <td><?= number_format($r['volume_actual_mt'] ?? 0,2) ?></td>
            <td><?= is_null($r['v_variance'])?'':number_format($r['v_variance'],2) ?></td>
            <td><?= $r['dt_ba_bm'] ? date('Y-m-d H:i', strtotime($r['dt_ba_bm'])) : '' ?></td>
            <td><?= $r['dt_coa_delivery'] ? date('Y-m-d H:i', strtotime($r['dt_coa_delivery'])) : '' ?></td>
            <td><?= $r['dt_coa_received'] ? date('Y-m-d H:i', strtotime($r['dt_coa_received'])) : '' ?></td>
            <td><?= $r['dt_load_sample'] ? date('Y-m-d H:i', strtotime($r['dt_load_sample'])) : '' ?></td>
            <td><?= $r['dt_sample_received'] ? date('Y-m-d H:i', strtotime($r['dt_sample_received'])) : '' ?></td>
            <td><?= htmlentities($r['received_status_calc'] ?? $r['status_shipment'] ?? '') ?></td>
            <td><?= $r['dt_inv_delivery_soft'] ? date('Y-m-d H:i', strtotime($r['dt_inv_delivery_soft'])) : '' ?></td>
            <td><?= $r['dt_inv_delivery_hard'] ? date('Y-m-d H:i', strtotime($r['dt_inv_delivery_hard'])) : '' ?></td>
            <td><?= $r['dt_inv_received'] ? date('Y-m-d H:i', strtotime($r['dt_inv_received'])) : '' ?></td>
            <td><?= $r['dt_payment'] ? date('Y-m-d', strtotime($r['dt_payment'])) : '' ?></td>
            <td><?= htmlentities($r['status_coal_supplier'] ?? '') ?></td>
            <td>
              <span class="badge <?= ($r['shipment_status']=='Completed'?'bg-success':($r['shipment_status']=='On process'?'bg-warning text-dark':'bg-secondary')) ?>">
                <?= $r['shipment_status'] ?: '-' ?>
              </span>
            </td>
            <td><?= $r['dt_disch_port'] ? date('Y-m-d', strtotime($r['dt_disch_port'])) : '' ?></td>
            <td class="text-truncate" style="max-width:280px"><?= htmlentities($r['remarks_aj'] ?? '') ?></td>
            <td><?= $r['dt_sample_request'] ? date('Y-m-d', strtotime($r['dt_sample_request'])) : '' ?></td>
            <td><?= $r['dt_sample_received2'] ? date('Y-m-d', strtotime($r['dt_sample_received2'])) : '' ?></td>
            <td><?= $r['created_at'] ?></td>
            <td><?= $r['updated_at'] ?></td>
            <td class="text-end">
              <a class="btn btn-sm btn-outline-secondary" href="<?= site_url('shipments/detail/'.$r['id']) ?>"><i class="bi bi-eye"></i></a>
              <?php if (is_superadmin() || is_admin_lbe() || is_staff_lbe()): ?>
                <a class="btn btn-sm btn-outline-primary" href="<?= site_url('shipments/edit/'.$r['id']) ?>"><i class="bi bi-pencil-square"></i></a>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php if (empty($rows)): ?>
        <div class="text-center text-muted py-4">Tidak ada data.</div>
      <?php endif; ?>
    </div>
  </div>
</div>

<style>
  #gridTable th, #gridTable td { font-size: .84rem; }
  #gridTable thead th { position: sticky; top: 0; z-index: 2; }
</style>

<script>
// ===== Toggle Visibility Kolom =====
(function(){
  const table = document.getElementById('gridTable');
  if (!table) return;
  const toggles = document.getElementById('colToggles');

  // mapping index kolom → label ringkas (skip kolom #, Aksi)
  const labels = Array.from(table.tHead.rows[0].cells).map((th,i)=>({i, text: th.innerText}));
  const hiddenByDefault = new Set([ // kolom advanced disembunyikan default
    2, 6, 8, 9,10,11,12,17,18,20,21,24,25,26,27,30,31,32,33,34,38,39
  ]);

  // buat checkbox
  labels.forEach(({i,text})=>{
    if (i===0 || text==='Aksi') return; // skip #
    const id='col_'+i;
    const wrap=document.createElement('label');
    wrap.className='form-check form-check-inline';
    wrap.innerHTML=`
      <input class="form-check-input" type="checkbox" id="${id}" data-col="${i}">
      <span class="form-check-label small">${text}</span>`;
    toggles.appendChild(wrap);
  });

  // fungsi show/hide
  function setCol(i, show){
    Array.from(table.rows).forEach(row=>{
      const cell = row.cells[i];
      if (cell) cell.style.display = show? '' : 'none';
    });
  }

  // init visibility
  labels.forEach(({i})=>{
    const show = !hiddenByDefault.has(i);
    setCol(i, show);
    const cb = document.querySelector(`input[data-col="${i}"]`);
    if (cb) cb.checked = show;
  });

  // events
  toggles.addEventListener('change', (e)=>{
    if (e.target && e.target.matches('input[type="checkbox"][data-col]')){
      const idx = parseInt(e.target.getAttribute('data-col'),10);
      setCol(idx, e.target.checked);
    }
  });

  document.getElementById('btnShowCommon')?.addEventListener('click', ()=>{
    labels.forEach(({i})=>{
      const common = !hiddenByDefault.has(i);
      setCol(i, common);
      const cb = document.querySelector(`input[data-col="${i}"]`); if (cb) cb.checked = common;
    });
  });
  document.getElementById('btnShowAll')?.addEventListener('click', ()=>{
    labels.forEach(({i})=>{
      setCol(i, true);
      const cb = document.querySelector(`input[data-col="${i}"]`); if (cb) cb.checked = true;
    });
  });
  document.getElementById('btnHideAll')?.addEventListener('click', ()=>{
    labels.forEach(({i})=>{
      if (i===0 || labels[i].text==='Aksi') return;
      setCol(i, false);
      const cb = document.querySelector(`input[data-col="${i}"]`); if (cb) cb.checked = false;
    });
  });
})();
</script>

<?php $this->load->view('layout/footer'); ?>
