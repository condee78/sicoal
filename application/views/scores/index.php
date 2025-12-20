<?php

$this->load->view('layout/header', ['title'=>'Shipper score','breadcrumb'=>['Shipper Performance'=>null]]);



?>

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

<form class="row g-2 mb-3" method="get" action="<?= site_url('scores/') ?>">



  <div class="col-6 col-md-4">

    <label class="form-label small">Filter Shipper</label>

    <select class="form-select" name="rekanan_kode">

      <option value="">Semua</option>

      <?php $sel=$filter['rekanan_kode']??''; foreach($shippers as $s): ?>

        <option value="<?= $s['kode'] ?>" <?= $sel===$s['kode']?'selected':'' ?>><?= htmlentities($s['nama_perusahaan']) ?></option>

      <?php endforeach; ?>

    </select>

  </div>

  <div class="col-12 col-md-2 d-grid">

    <label class="form-label small">&nbsp;</label>

    <button class="btn btn-dark"><i class="bi bi-filter"></i> Terapkan</button>

  </div>
  
  <!-- [C] add performance guidance -->
  <div class="col-12 col-md-6 d-flex align-items-end justify-content-end gap-2">

      <a href="<?= base_url(); ?>assets/Vendor Performance Variable.pdf" target="_blank" class="btn btn-outline-secondary">Panduan Vendor Performance</a>

  </div>

</form>


<div class="row g-3 mt-1">
  <!-- Grafik detail score (On-Time, Volume, COA) -->
  <div class="col-12 col-lg-8">
    <div class="card shadow-sm h-100">
      <div class="card-header bg-white d-flex align-items-center justify-content-between">
        <h6 class="m-0">Rata-Rata Score Vendor (On-Time, Volume, COA)</h6>
        <small class="text-muted">Semakin tinggi semakin baik</small>
      </div>
      <div class="card-body">
        <div id="chartScoreDetail"></div>
      </div>
    </div>
  </div>

  <!-- Grafik score total -->
  <div class="col-12 col-lg-4">
    <div class="card shadow-sm h-100">
      <div class="card-header bg-white d-flex align-items-center justify-content-between">
        <h6 class="m-0">Avg Total Score per Vendor</h6>
        <small class="text-muted">Skala 0–5 (misal)</small>
      </div>
      <div class="card-body">
        <div id="chartScoreTotal"></div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3">







  <div class="col-12">

    <div class="card">
        
        <div class="row g-3 mt-3">
  <!-- Grafik 1: Shipment Plan vs Actual -->
  <div class="col-12 col-lg-6">
    <div class="card shadow-sm h-100">
      <div class="card-header bg-white">
        <h6 class="m-0">Jumlah Shipment per Vendor (Plan vs Actual)</h6>
      </div>
      <div class="card-body">
        <div id="chartShipmentsVendor"></div>
      </div>
    </div>
  </div>

  <!-- Grafik 2: Volume Plan vs Actual -->
  <div class="col-12 col-lg-6">
    <div class="card shadow-sm h-100">
      <div class="card-header bg-white">
        <h6 class="m-0">Volume per Vendor (Plan vs Actual)</h6>
      </div>
      <div class="card-body">
        <div id="chartVolumeVendor"></div>
      </div>
    </div>
  </div>
</div>


  <div class="card-header bg-white">

    <h6 class="m-0">Ringkasan Nilai per Shipper</h6>

  </div>

  <div class="card-body">

  <div class="table-responsive">

    <table class="table table-hover table-sm align-middle datatable">

      <thead class="table-light">

        <tr>

          <th>#</th>

          <th>Vendor (Kode)</th>

          <th>Nama Perusahaan</th>

          <th class="text-center">Shipments</th>
           <th class="text-center">Shipments Actual</th>

          <th class="text-center">Avg On-Time</th>

          <th class="text-center">Avg Volume</th>

          <th class="text-center">Avg COA</th>

          <th class="text-center">Avg Total</th>

          <th class="text-end">Plan Vol (MT)</th>

          <th class="text-end">Actual Vol (MT)</th>

          <th></th>

        </tr>

      </thead>

      <tbody>

        <?php $i=1; foreach($rows as $r): ?>

        <tr>

          <td><?= $i++ ?></td>

          <td><code><?= htmlentities($r['rekanan_kode']) ?></code></td>

          <td><?= htmlentities($r['nama_perusahaan']) ?></td>

          <td class="text-center"><?= (int)$r['count_shipments'] ?></td>
           <td class="text-center"><?= (int)$r['count_shipments_act'] ?></td>

          <td class="text-center"><?= number_format($r['avg_s1'],2) ?></td>

          <td class="text-center"><?= number_format($r['avg_s2'],2) ?></td>

          <td class="text-center"><?= number_format($r['avg_s3'],2) ?></td>

          <td class="text-center">

            <span class="badge <?= ($r['avg_total']>=4?'bg-success':($r['avg_total']>=3?'bg-warning text-dark':'bg-secondary')) ?>">

              <?= number_format($r['avg_total'],2) ?>

            </span>

          </td>

          <td class="text-end"><?= number_format($r['total_plan_vol'],2) ?></td>

          <td class="text-end"><?= number_format($r['total_actual_vol'],2) ?></td>

          <td class="text-end">

            <a class="btn btn-sm btn-outline-primary"

               href="<?= site_url('scores/shipper/'.$r['rekanan_kode']) ?>">

              Detail

            </a>

          </td>

        </tr>

        <?php endforeach; if(empty($rows)): ?>

          <tr><td colspan="11" class="text-center text-muted py-4">Tidak ada data.</td></tr>

        <?php endif; ?>

      </tbody>

    </table>

  </div>

  </div>
</div>

    

 

    

  </div>

</div>


<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
(function(){
  // Data dari PHP
  const shipperRows = <?= json_encode($rows ?? []) ?>;

  // Kalau tidak ada data, jangan render chart
  if (!Array.isArray(shipperRows) || shipperRows.length === 0) {
    return;
  }

  // Kategori X: Plan & Actual
  const categories = ['Plan', 'Actual'];

  // Series untuk grafik jumlah shipment (plan vs actual) per vendor
  const shipmentSeries = shipperRows.map(r => ({
    name: (r.nama_perusahaan || r.rekanan_kode || 'Vendor'),
    data: [
      Number(r.count_shipments ?? 0),      // Plan
      Number(r.count_shipments_act ?? 0),  // Actual
    ]
  }));

  // Series untuk grafik volume (plan vs actual) per vendor
  const volumeSeries = shipperRows.map(r => ({
    name: (r.nama_perusahaan || r.rekanan_kode || 'Vendor'),
    data: [
      Number(r.total_plan_vol ?? 0),       // Plan
      Number(r.total_actual_vol ?? 0),     // Actual
    ]
  }));

  // Palette warna – Apex akan putar otomatis, tiap vendor beda warna
  const colorPalette = [
    '#008FFB', '#00E396', '#FEB019', '#FF4560',
    '#775DD0', '#3F51B5', '#03A9F4', '#4CAF50',
    '#F9A825', '#E91E63', '#9C27B0', '#FF9800'
  ];

  // GRAFIK SHIPMENTS
  const shipOpt = {
    chart: {
      type: 'bar',
      height: 380,
      toolbar: { show: true },
      stacked: false
    },
    series: shipmentSeries,
    colors: colorPalette,
    plotOptions: {
      bar: {
        horizontal: false,
        columnWidth: '40%',
        borderRadius: 4
      }
    },
    dataLabels: { enabled: false },
    xaxis: {
      categories: categories,
      title: { text: '' }
    },
    yaxis: {
      title: { text: 'Jumlah Shipments' },
      min: 0,
      forceNiceScale: true
    },
    legend: {
      position: 'bottom',
      markers: { width: 10, height: 10, radius: 12 }
      ,
    onItemClick: { toggleDataSeries: false }
    },
    tooltip: {
      shared: true,
      intersect: false
    }
  };

  const chartShip = new ApexCharts(
    document.querySelector('#chartShipmentsVendor'),
    shipOpt
  );
  chartShip.render();

  // GRAFIK VOLUME
  const volOpt = {
    chart: {
      type: 'bar',
      height: 380,
      toolbar: { show: true },
      stacked: false
    },
    series: volumeSeries,
    colors: colorPalette,
    plotOptions: {
      bar: {
        horizontal: false,
        columnWidth: '40%',
        borderRadius: 4
      }
    },
    dataLabels: {
      enabled: false
    },
    xaxis: {
      categories: categories,
      title: { text: '' }
    },
    yaxis: {
      title: { text: 'Volume (MT)' },
      min: 0,
      forceNiceScale: true,
      labels: {
        formatter: function(val) {
          // biar lebih pendek kalau besar (dalam ribuan / jutaan)
          if (Math.abs(val) >= 1_000_000) return (val/1_000_000).toFixed(1) + ' M';
          if (Math.abs(val) >= 1_000)     return (val/1_000).toFixed(1) + ' K';
          return val.toFixed(0);
        }
      }
    },
    legend: {
      position: 'bottom',
      markers: { width: 10, height: 10, radius: 12 }
      ,
    onItemClick: { toggleDataSeries: false }
    },
    tooltip: {
      shared: true,
      intersect: false,
      y: {
        formatter: function(val) {
          return new Intl.NumberFormat('id-ID', {
            maximumFractionDigits: 2
          }).format(val) + ' MT';
        }
      }
    }
  };

  const chartVol = new ApexCharts(
    document.querySelector('#chartVolumeVendor'),
    volOpt
  );
  chartVol.render();
})();
</script>



<script>
(function(){
  const vendorScores = <?= json_encode($rows ?? []) ?>;

  if (!Array.isArray(vendorScores) || vendorScores.length === 0) {
    console.warn('vendor_scores kosong, grafik score tidak dirender');
    return;
  }

  // --- helper warna ---
  const baseColors = [
    '#008FFB', // biru
    '#00E396', // hijau
    '#FEB019', // kuning/oranye
    '#FF4560', // merah
    '#775DD0', // ungu
    '#3F51B5', // indigo
    '#03A9F4', // biru muda
    '#4CAF50', // hijau tua
    '#F9A825', // emas
    '#E91E63', // pink
  ];

  function hexToRgb(hex) {
    hex = hex.replace('#', '');
    if (hex.length === 3) {
      hex = hex.split('').map(c => c + c).join('');
    }
    const num = parseInt(hex, 16);
    return {
      r: (num >> 16) & 255,
      g: (num >> 8) & 255,
      b: num & 255
    };
  }

  // mix dengan putih -> factor 0 = warna asli, 1 = putih
  function lighten(hex, factor) {
    const rgb = hexToRgb(hex);
    const r = Math.round(rgb.r + (255 - rgb.r) * factor);
    const g = Math.round(rgb.g + (255 - rgb.g) * factor);
    const b = Math.round(rgb.b + (255 - rgb.b) * factor);
    return `rgb(${r},${g},${b})`;
  }

  // --- siapkan data ---
  const vendors   = vendorScores.map(r => r.nama_perusahaan || r.rekanan_kode || 'Vendor');
  const categories = ['On-Time', 'Volume', 'COA']; // di sumbu X

  // tiap vendor jadi 1 series, dengan 3 titik (On-Time, Volume, COA)
  const seriesDetail = vendorScores.map(r => ({
    name: r.nama_perusahaan || r.rekanan_kode || 'Vendor',
    data: [
      Number(r.avg_s1 ?? 0),   // On-Time
      Number(r.avg_s2 ?? 0),   // Volume
      Number(r.avg_s3 ?? 0)    // COA
    ]
  }));

  const avgTotal = vendorScores.map(r => Number((r.avg_total ?? r.score_avg) ?? 0));

  // ---------- Grafik 1: On-Time, Volume, COA per vendor ----------
  const optDetail = {
    chart: {
      type: 'bar',
      height: 360,
      toolbar: { show: true }
    },
    series: seriesDetail,
    xaxis: {
      categories: categories,
      labels: { rotate: 0 }
    },
    stroke: {
      width: 3,
      curve: 'smooth'
    },
    markers: {
      size: 4
    },
    dataLabels: { enabled: false },
    legend: {
      position: 'bottom',
      onItemClick: { toggleDataSeries: false }
    },
    yaxis: {
      title: { text: 'Score Rata-Rata' },
      min: 0,
      max: 5, // kalau skala 0-5
      forceNiceScale: true
    },
    tooltip: {
      shared: true,
      intersect: false,
      y: { formatter: val => val.toFixed(2) }
    },
    // warna: per vendor beda, per jenis (dataPoint) turunan/lebih terang
    colors: [function(opts) {
      const seriesIndex    = opts.seriesIndex;
      const dataPointIndex = opts.dataPointIndex; // 0=On-Time, 1=Volume, 2=COA
      const base = baseColors[seriesIndex % baseColors.length];

      // turunan warna:
      // On-Time  : paling gelap (factor 0)
      // Volume   : sedikit lebih terang
      // COA      : lebih terang lagi
      const factors = [0.0, 0.25, 0.45];
      const f = factors[dataPointIndex] ?? 0.0;

      return lighten(base, f);
    }]
  };

  new ApexCharts(
    document.querySelector('#chartScoreDetail'),
    optDetail
  ).render();

  // ---------- Grafik 2: Avg Total Score per vendor ----------
  const optTotal = {
    chart: {
      type: 'line',
      height: 320,
      toolbar: { show: true }
    },
    series: [
      {
        name: 'Avg Total Score',
        data: avgTotal
      }
    ],
    xaxis: {
      categories: vendors,
      labels: {
        rotate: -25,
        trim: true
      }
    },
    plotOptions: {
      bar: {
        columnWidth: '45%',
        borderRadius: 4
      }
    },
    dataLabels: { enabled: false },
    yaxis: {
      title: { text: 'Total Score' },
      min: 0,
      max: 5,
      forceNiceScale: true
    },
    tooltip: {
      y: { formatter: val => val.toFixed(2) }
    },
    legend: {
      position: 'top',
      onItemClick: { toggleDataSeries: false }
    },
    colors: baseColors // masing-masing vendor punya warna dasar yang sama dengan chart detail
  };

  new ApexCharts(
    document.querySelector('#chartScoreTotal'),
    optTotal
  ).render();
})();
</script>




<?php $this->load->view('layout/footer'); ?>

