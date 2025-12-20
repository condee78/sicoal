<?php $this->load->view('layout/header', ['title'=>'Dashboard','breadcrumb'=>['Dashboard'=>null]]); ?>

<div class="card mb-3">
  <div class="card-body">
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

    <form class="row g-2" id="frmPeriod">
      <div class="col-12 col-md-3">
        <label class="form-label">Dari Tanggal</label>
        <input type="date" class="form-control" name="date_from" value="<?= htmlentities($date_from) ?>">
      </div>
      <div class="col-12 col-md-3">
        <label class="form-label">Sampai Tanggal</label>
        <input type="date" class="form-control" name="date_to" value="<?= htmlentities($date_to) ?>">
      </div>
      <div class="col-12 col-md-2 d-flex align-items-end">
        <button class="btn btn-primary w-100"><i class="bi bi-arrow-repeat me-1"></i> Tampilkan</button>
      </div>
      <div class="col-12 col-md-4 d-flex align-items-end justify-content-end gap-2">
        <button class="btn btn-outline-secondary" type="button" id="btnThisYear">Tahun Ini</button>
        <button class="btn btn-outline-secondary" type="button" id="btnLast12">12 Bulan Terakhir</button>
      </div>
    </form>
  </div>
</div>

<div class="row g-3">
  <!-- Kiri: Volume -->
  <div class="col-12 col-lg-6">
    <div class="card h-100">
      <div class="card-header bg-white">
        <h6 class="m-0">Volume (Plan vs Actual)</h6>
      </div>
      <div class="card-body">
        <div id="chartVolume"></div>
      </div>
    </div>
  </div>

  <!-- Kanan: Shipments -->
  <div class="col-12 col-lg-6">
    <div class="card h-100">
      <div class="card-header bg-white">
        <h6 class="m-0">Jumlah Shipment (Plan vs Actual)</h6>
      </div>
      <div class="card-body">
        <div id="chartShipments"></div>
      </div>
    </div>
  </div>
</div>
<div class="row g-3 mt-3">
  <div class="col-12">
    <div class="card h-100">
      <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="m-0">Tabel Volume & Shipments (Plan vs Actual)</h6>
        <small class="text-muted">Sesuai range/filter di grafik</small>
      </div>
      <div class="card-body table-responsive">
        <table class="table table-sm table-bordered align-middle" id="tblVolSummary">
          <thead class="table-light">
            <tr class="text-center">
              <th rowspan="2">Bulan</th>
              <th colspan="2">Volume (MT)</th>
              <th colspan="2">Jumlah Shipment</th>
            </tr>
            <tr class="text-center">
              <th>Plan</th>
              <th>Actual</th>
              <th>Plan</th>
              <th>Actual</th>
            </tr>
          </thead>
          <tbody>
            <!-- akan diisi via JS -->
          </tbody>
          <tfoot class="table-light">
            <tr class="fw-bold text-end">
              <td class="text-center">TOTAL</td>
              <td id="totVolPlan">0</td>
              <td id="totVolAct">0</td>
              <td id="totCntPlan">0</td>
              <td id="totCntAct">0</td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>
</div>



<!-- ApexCharts -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
(function(){
  let chartVol = null, chartCnt = null;
  const elVol = document.getElementById('chartVolume');
  const elCnt = document.getElementById('chartShipments');

  // formatter angka Indonesia
  const nf0 = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 });
  const nf2 = new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

  function fetchData(params){
    const qs = new URLSearchParams(params).toString();
    return fetch('<?= site_url('dashboard/period_data') ?>?'+qs, {headers:{'X-Requested-With':'XMLHttpRequest'}})
      .then(r=>r.json());
  }

  function renderVolume(data){
    const labels = data.labels.map(ym => ym); // 'YYYY-MM'
    const opt = {
      chart: { type:'bar', height: 380, toolbar: { show:true } },
      plotOptions: { bar: { columnWidth: '45%', borderRadius: 4, dataLabels: { position:'top' } } },
      dataLabels: { enabled:false },
      xaxis: { categories: labels },
      yaxis: { title: { text: 'Volume (MT)' } },
      series: [
        { name:'Plan Volume (MT)',   data: data.vol_plan },
        { name:'Actual Volume (MT)', data: data.vol_actual }
      ],
      tooltip: { shared:true, intersect:false }
    };
    if (chartVol) chartVol.destroy();
    chartVol = new ApexCharts(elVol, opt);
    chartVol.render();
  }

  function renderShipments(data){
    const labels = data.labels.map(ym => ym);
    const opt = {
      chart: { type:'line', height: 380, toolbar: { show:true } },
      stroke: { width: 3, curve:'smooth' },
      markers: { size: 3 },
      dataLabels: { enabled:false },
      xaxis: { categories: labels },
      yaxis: { title: { text: 'Jumlah Shipments' }, min: 0, forceNiceScale: true },
      series: [
        { name:'Plan Shipments',   data: data.cnt_plan },
        { name:'Actual Shipments', data: data.cnt_actual }
      ],
      tooltip: { shared:true, intersect:false }
    };
    if (chartCnt) chartCnt.destroy();
    chartCnt = new ApexCharts(elCnt, opt);
    chartCnt.render();
  }

  // === TABEL RINGKASAN DI BAWAH GRAFIK ===
  function renderTable(data){
    const tbody = document.querySelector('#tblVolSummary tbody');
    if (!tbody) return;

    let rowsHtml = '';
    let totVolPlan = 0, totVolAct = 0, totCntPlan = 0, totCntAct = 0;

    // asumsi: panjang labels, vol_plan, vol_actual, cnt_plan, cnt_actual sama
    data.labels.forEach((label, i) => {
      const vPlan = Number(data.vol_plan?.[i] ?? 0);
      const vAct  = Number(data.vol_actual?.[i] ?? 0);
      const cPlan = Number(data.cnt_plan?.[i] ?? 0);
      const cAct  = Number(data.cnt_actual?.[i] ?? 0);

      totVolPlan += vPlan;
      totVolAct  += vAct;
      totCntPlan += cPlan;
      totCntAct  += cAct;

      // optional: konversi 'YYYY-MM' jadi nama bulan Indonesia
      let bulan = label;
      if (/^\d{4}-\d{2}$/.test(label)) {
        const [y, m] = label.split('-');
        const d = new Date(Number(y), Number(m)-1, 1);
        bulan = d.toLocaleString('id-ID', { month:'short', year:'numeric' });
      }

      rowsHtml += `
        <tr class="text-end">
          <td class="text-start">${bulan}</td>
          <td>${nf2.format(vPlan)}</td>
          <td>${nf2.format(vAct)}</td>
          <td>${nf0.format(cPlan)}</td>
          <td>${nf0.format(cAct)}</td>
        </tr>
      `;
    });

    tbody.innerHTML = rowsHtml;

    // isi total di tfoot
    const elTotVolPlan = document.getElementById('totVolPlan');
    const elTotVolAct  = document.getElementById('totVolAct');
    const elTotCntPlan = document.getElementById('totCntPlan');
    const elTotCntAct  = document.getElementById('totCntAct');

    if (elTotVolPlan) elTotVolPlan.textContent = nf2.format(totVolPlan);
    if (elTotVolAct)  elTotVolAct.textContent  = nf2.format(totVolAct);
    if (elTotCntPlan) elTotCntPlan.textContent = nf0.format(totCntPlan);
    if (elTotCntAct)  elTotCntAct.textContent  = nf0.format(totCntAct);
  }

  function updateCharts(){
    const fd = new FormData(document.getElementById('frmPeriod'));
    const params = Object.fromEntries(fd.entries());
    fetchData(params).then(json=>{
      if (!json.ok) return alert(json.msg||'Gagal memuat data');
      renderVolume(json.data);
      renderShipments(json.data);
      renderTable(json.data);   // <- PENTING: panggil di sini
    });
  }

  // form submit
  document.getElementById('frmPeriod').addEventListener('submit', function(e){
    e.preventDefault(); updateCharts();
  });

  // preset buttons
  document.getElementById('btnThisYear').addEventListener('click', ()=>{
    const d = new Date();
    document.querySelector('[name="date_from"]').value = d.getFullYear()+'-01-01';
    document.querySelector('[name="date_to"]').value   = d.getFullYear()+'-12-31';
    updateCharts();
  });
  document.getElementById('btnLast12').addEventListener('click', ()=>{
    const end = new Date();
    const start = new Date(end.getFullYear(), end.getMonth()-11, 1);
    document.querySelector('[name="date_from"]').value = start.toISOString().slice(0,10);
    document.querySelector('[name="date_to"]').value   = end.toISOString().slice(0,10);
    updateCharts();
  });

  // auto load awal
  updateCharts();
})();
</script>


<div class="row g-3">
  <!-- KPI Cards -->
  <?php
    $kpi = [
      ['title'=>'Planned > 7 days','value'=>$cards['planned_far']??0,'icon'=>'bi-calendar2-week','class'=>'primary'],
      ['title'=>'Due in 7 days','value'=>$cards['due_7days']??0,'icon'=>'bi-hourglass-split','class'=>'warning'],
      ['title'=>'Delayed','value'=>$cards['delayed']??0,'icon'=>'bi-exclamation-triangle','class'=>'danger'],
      ['title'=>'Completed','value'=>$cards['completed']??0,'icon'=>'bi-check2-circle','class'=>'success'],
    ];
  ?>
  <?php foreach($kpi as $c): ?>
  <div class="col-6 col-md-3">
    <div class="card card-kpi shadow-sm h-100">
      <div class="card-body d-flex align-items-center justify-content-between">
        <div>
          <div class="text-muted small"><?= $c['title'] ?></div>
          <div class="fs-3 fw-bold"><?= number_format($c['value']) ?></div>
        </div>
        <div class="icon text-<?= $c['class'] ?>"><i class="bi <?= $c['icon'] ?> fs-4"></i></div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php /*
<div class="row g-3 mt-1">
  <div class="col-12 col-lg-8">
    <div class="card shadow-sm h-100">
      <div class="card-header bg-white d-flex align-items-center justify-content-between">
        <h6 class="m-0">Plan vs Actual (Monthly, <?= date('Y'); ?>)</h6>
        <div class="small text-muted">Volume (MT)</div>
      </div>
      <div class="card-body">
        <div id="chart_monthly"></div>
      </div>
    </div>
  </div>
  <div class="col-12 col-lg-4">
    <div class="card shadow-sm h-100">
      <div class="card-header bg-white">
        <h6 class="m-0">Shipments by Shipper</h6>
      </div>
      <div class="card-body">
        <div id="chart_pie_shipper"></div>
      </div>
    </div>
  </div>
</div>
*/?>

<div class="row g-3 mt-1">
  <div class="col-12">
    <div class="card shadow-sm">
      <div class="card-header bg-white d-flex align-items-center justify-content-between">
        <h6 class="m-0">Shipper Performance (Score)</h6>
        <a href="<?= site_url('scores') ?>" class="btn btn-sm btn-outline-primary">Detail</a>
      </div>
      <div class="card-body table-responsive">
        <table class="table table-sm table-hover align-middle datatable">
          <thead>
            <tr>
              <th>#</th>
              <th>Shipper</th>
              <th>Plant Shipments</th>
               <th>Actual Shipments</th>
              <th>Total Volume PLan (MT)</th>
               <th>Total Volume Actual (MT)</th>
                <th>Remaining</th>
                <th>Remaining %</th>
              <th>Avg Score</th>
            </tr>
          </thead>
          <tbody>
            <?php $i=1; foreach($performance as $row): ?>
              <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlentities($row['nama_perusahaan']) ?></td>
                <td><?= number_format($row['shipments']) ?></td>
                 <td><?= number_format($row['shipments_act']) ?></td>
                <td><?= number_format($row['vol'],2) ?></td>
                <td><?= number_format($row['vol_act'],2) ?></td>
                <td><?= number_format($row['vol']-$row['vol_act'],2) ?> </td>
                 <td><?= number_format(@($row['vol']/@$row['vol_act'])/100,2) ?>% </td>
                

                <td>
                  <span class="badge <?= ($row['score_avg']>=1?'bg-success':($row['score_avg']>=0?'bg-warning':'bg-danger')) ?>">
                    <?= number_format($row['score_avg'],2) ?>
                  </span>
                </td>
              </tr>
            <?php endforeach;?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
  // Data dari controller (encoded)
  const monthly = <?= json_encode($monthly ?? []) ?>; // [{m:1, plan:..., actual:...}, ...]
  const byShipper = <?= json_encode($by_shipper ?? []) ?>; // [{name:'ABC', shipments: 10},...]

  // Monthly Chart
  (function(){
    const cats = monthly.map(r => new Date(2020, (r.m||1)-1, 1).toLocaleString('id-ID',{month:'short'}));
    const plan = monthly.map(r => r.plan||0);
    const actual = monthly.map(r => r.actual||0);
    const options = {
      chart: { type: 'bar', height: 320, toolbar:{show:false} },
      series: [
        { name: 'Plan', data: plan },
        { name: 'Actual', data: actual }
      ],
      xaxis: { categories: cats },
      plotOptions: { bar: { columnWidth: '45%', borderRadius: 4 } },
      dataLabels: { enabled: false },
      legend: { position:'top' }
    };
    new ApexCharts(document.querySelector("#chart_monthly"), options).render();
  })();

  // Pie by Shipper
  (function(){
    const labels = byShipper.map(r => r.nama_perusahaan || r.name || 'Shipper');
    const data = byShipper.map(r => r.shipments || 0);
    const options = {
      chart: { type: 'donut', height: 280 },
      labels: labels,
      series: data,
      legend: { position:'bottom' }
    };
    new ApexCharts(document.querySelector("#chart_pie_shipper"), options).render();
  })();
</script>

<?php $this->load->view('layout/footer'); ?>
