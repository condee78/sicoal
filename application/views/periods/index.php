<?php $this->load->view('layout/header',['title'=>'Periods','breadcrumb'=>['Periods'=>null]]); ?>
<div class="card shadow-sm">
  <div class="card-header bg-white d-flex justify-content-between align-items-center">
    <h6 class="m-0">Manajemen Periode Tahunan</h6>
    <form class="d-flex gap-2" method="post" action="<?= site_url('periods/create') ?>">
      <input type="number" name="year" class="form-control form-control-sm" placeholder="Tahun" min="2015" max="<?= date('Y')+5 ?>" required>
      <button class="btn btn-sm btn-primary"><i class="bi bi-plus-lg"></i> Tambah</button>
    </form>
  </div>
  <div class="card-body table-responsive">
    <table class="table table-sm align-middle">
      <thead><tr><th>Tahun</th><th>Aktif</th><th>Locked</th><th>Aksi</th></tr></thead>
      <tbody>
        <?php foreach($rows as $r): ?>
        <tr>
          <td class="fw-semibold"><?= $r['year'] ?></td>
          <td><?= $r['is_active'] ? '<span class="badge bg-success">Active</span>' : '-' ?></td>
          <td><?= $r['is_locked'] ? '<span class="badge bg-danger">Locked</span>' : '<span class="badge bg-secondary">Open</span>' ?></td>
          <td class="d-flex gap-2">
            <?php if(!$r['is_active']): ?>
              <a class="btn btn-sm btn-outline-primary" href="<?= site_url('periods/set_active/'.$r['year']) ?>">Set Active</a>
            <?php endif; ?>
            <?php if(!$r['is_locked']): ?>
              <a class="btn btn-sm btn-outline-danger" href="<?= site_url('periods/lock/'.$r['year']) ?>">Lock</a>
            <?php else: ?>
              <a class="btn btn-sm btn-outline-success" href="<?= site_url('periods/unlock/'.$r['year']) ?>">Unlock</a>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach;?>
      </tbody>
    </table>
  </div>
</div>
<?php $this->load->view('layout/footer'); ?>
