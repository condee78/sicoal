<?php $this->load->view('layout/header',['title'=>'Manajemen Admin & Staff']); ?>
<div class="card mb-3">
  <div class="card-body">
    <?php if($this->session->flashdata('ok')): ?>
      <div class="alert alert-success"><?= $this->session->flashdata('ok'); ?></div>
    <?php endif; if($this->session->flashdata('err')): ?>
      <div class="alert alert-danger"><?= $this->session->flashdata('err'); ?></div>
    <?php endif; ?>

    <form class="row g-2 mb-2" method="get">
      <div class="col-12 col-md-3">
        <label class="form-label">Filter Role</label>
        <select name="role" class="form-select">
          <option value="">Semua</option>
          <option value="0" <?= ($role==='0'?'selected':'') ?>>Super Admin</option>
          <option value="1" <?= ($role==='1'?'selected':'') ?>>Admin LBE</option>
          <option value="2" <?= ($role==='2'?'selected':'') ?>>Staff LBE</option>
        </select>
      </div>
      <div class="col-12 col-md-4">
        <label class="form-label">Cari</label>
        <input type="text" name="q" value="<?= htmlentities($q) ?>" class="form-control" placeholder="username / nama / email / telp">
      </div>
      <div class="col-12 col-md-3 d-flex align-items-end">
        <button class="btn btn-primary me-2"><i class="bi bi-filter"></i> Terapkan</button>
        <a class="btn btn-outline-secondary" href="<?= site_url('users') ?>">Reset</a>
      </div>
      <div class="col-12 col-md-2 d-flex align-items-end justify-content-end">
        <a href="<?= site_url('users/create') ?>" class="btn btn-success"><i class="bi bi-plus-lg"></i> Tambah</a>
      </div>
    </form>

    <div class="table-responsive">
      <table class="table table-sm table-striped align-middle">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Username</th>
            <th>Nama</th>
            <th>Email</th>
            <th>Telp</th>
            <th>Role</th>
            <th>Kode</th>
            <th class="text-end">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php $i=1; foreach($rows as $r): ?>
          <tr>
            <td><?= $i++ ?></td>
            <td><code><?= htmlentities($r['username']) ?></code></td>
            <td><?= htmlentities($r['nama']) ?></td>
            <td><?= htmlentities($r['email']) ?></td>
            <td><?= htmlentities($r['telp']) ?></td>
            <td>
              <?php
                echo ((int)$r['groupid']===0?'Super Admin':((int)$r['groupid']===1?'Admin LBE':'Staff LBE'));
              ?>
            </td>
            <td><?= htmlentities($r['kode']) ?></td>
            <td class="text-end">
              <a href="<?= site_url('users/edit/'.$r['userid']) ?>" class="btn btn-sm btn-outline-primary">Edit</a>
              <a href="<?= site_url('users/reset/'.$r['userid']) ?>" class="btn btn-sm btn-outline-warning"
                 onclick="return confirm('Reset password user ini? Password baru akan ditampilkan sebagai notifikasi.');">Reset</a>
              <a href="<?= site_url('users/delete/'.$r['userid']) ?>" class="btn btn-sm btn-outline-danger"
                 onclick="return confirm('Hapus user ini? Tindakan tidak dapat dibatalkan.');">Hapus</a>
            </td>
          </tr>
          <?php endforeach; if(empty($rows)): ?>
          <tr><td colspan="8" class="text-center text-muted py-4">Tidak ada data.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php $this->load->view('layout/footer'); ?>
