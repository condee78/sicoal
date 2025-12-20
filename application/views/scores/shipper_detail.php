<?php $this->load->view('layout/header', [

  'title'=>'Detail Skor — '.$nama_perusahaan.' ('.$rekanan_kode.')'

]); ?>



<div class="d-flex justify-content-between align-items-center mb-2">

  <h6 class="m-0">Detail Skor — <?= htmlentities($nama_perusahaan) ?> <small class="text-muted">(<?= htmlentities($rekanan_kode) ?>)</small></h6>

  <div>

    <a href="<?= site_url('scores?date_from='.$date_from.'&date_to='.$date_to) ?>" class="btn btn-sm btn-outline-secondary">

      &larr; Kembali

    </a>

  </div>

</div>





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



   

  </div>

</div>



<div class="card">
  <div class="card-body">

  <div class="table-responsive">

    <table class="table table-hover table-sm align-middle datatable">

      <thead class="table-light">

        <tr>

          <th>#</th>

          <th>Shipment No</th>

          <th>ETA LBE</th>

          <th>Actual Date</th>

          <th class="text-center">O (hari)</th>

          <th class="text-center">Skor On-Time</th>

          <th class="text-end">Plan (MT)</th>

          <th class="text-end">Actual (MT)</th>

          <th class="text-center">Ratio %</th>

          <th class="text-center">Skor Volume</th>

          <th>Departure</th>

          <th>COA Submit</th>

          <th class="text-center">COA Δ (hari)</th>

          <th class="text-center">Skor COA</th>

          <th class="text-center">Total</th>

          <th></th>

        </tr>

      </thead>

      <tbody>

        <?php $i=1; foreach($rows as $r): ?>

          <?php

            $badge = ($r['score_total']>=4?'bg-success':($r['score_total']>=3?'bg-warning text-dark':'bg-secondary'));

          ?>

          <tr>

            <td><?= $i++ ?></td>

            <td><code><?= htmlentities($r['shipment_no']) ?></code></td>

            <td><?= $r['eta_lbe'] ? date('Y-m-d', strtotime($r['eta_lbe'])):'' ?></td>

            <td>- <?= tgl_time($r['actual_date']) ?></td>

            <td class="text-center"><?= ($r['o_diff_days']!==null ? (int)$r['o_diff_days'] : '-') ?></td>

            <td class="text-center"><?= (int)$r['score_on_time'] ?></td>

            <td class="text-end"><?= number_format((float)$r['volume_plan_mt'],2) ?></td>

            <td class="text-end"><?= number_format((float)$r['volume_actual_mt'],2) ?></td>

            <td class="text-center"><?= $r['v_ratio_pct']!==null ? number_format($r['v_ratio_pct'],2).'%' : '-' ?></td>

            <td class="text-center"><?= (int)$r['score_volume'] ?></td>

            <td><?= $r['actual_departure'] ? tgl_time($r['actual_departure']):'' ?></td>

            <td><?= $r['dt_coa_delivery'] ? tgl_time($r['dt_coa_delivery']):'' ?></td>

            <td class="text-center"><?= ($r['coa_diff_days']!==null ? (int)$r['coa_diff_days'] : '-') ?></td>

            <td class="text-center"><?= (int)$r['score_coa'] ?></td>

            <td class="text-center"><span class="badge <?= $badge ?>"><?= number_format($r['score_total'],2) ?></span></td>

            <td class="text-end">

              <a class="btn btn-sm btn-outline-primary" href="<?= site_url('shipments/detail/'.$r['id']) ?>">

                Detail Shipment

              </a>

            </td>

          </tr>

        <?php endforeach; if(empty($rows)): ?>

          <tr><td colspan="16" class="text-center text-muted py-4">Tidak ada shipment pada periode ini.</td></tr>

        <?php endif; ?>

      </tbody>

    </table>

  </div>

  </div>

</div>



<?php $this->load->view('layout/footer'); ?>

