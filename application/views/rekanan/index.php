<?php $this->load->view('layout/header',['title'=>'Database Vendor','breadcrumb'=>['Database Vendor'=>null]]); ?>

<div class="d-flex justify-content-between align-items-center mb-2">
  <form class="d-flex gap-2" method="get">
    <input class="form-control form-control-sm" name="q" placeholder="Cari kode / nama / email" value="<?= htmlentities($q ?? '') ?>">
    <button class="btn btn-sm btn-outline-primary"><i class="bi bi-search"></i></button>
  </form>
  <a href="<?= site_url('rekanan/create') ?>" class="btn btn-sm btn-success"><i class="bi bi-plus-circle"></i> Tambah</a>
</div>

<?php if($this->session->flashdata('ok')): ?>
  <div class="alert alert-success py-2"><?= $this->session->flashdata('ok') ?></div>
<?php endif; if($this->session->flashdata('err')): ?>
  <div class="alert alert-danger py-2"><?= $this->session->flashdata('err') ?></div>
<?php endif; ?>

<div class="card shadow-sm">
  <div class="card-body table-responsive">
    <table class="table table-sm table-hover align-middle">
      <thead><tr>
        <th>Kode</th><th>Perusahaan</th><th>Email</th><th>Telp/WA</th><th>Kontak</th><th class="text-end">Aksi</th>
      </tr></thead>
      <tbody>
        <?php foreach($rows as $r): ?>
          <tr>
            <td class="fw-semibold"><?= htmlentities($r['kode']) ?></td>
            <td><?= htmlentities($r['nama_perusahaan']) ?><br><span class="text-muted small"><?= nl2br(htmlentities($r['alamat_perusahaan'])) ?></span></td>
            <td><?= htmlentities($r['email']) ?></td>
            <td><?= htmlentities($r['telp']) ?><br><span class="text-muted small"><?= htmlentities($r['wa']) ?></span></td>
            <td><?= htmlentities($r['ttd_nama']) ?><br><span class="text-muted small"><?= htmlentities($r['ttd_jabatan']) ?></span></td>
            <td class="text-end">
              <a class="btn btn-sm btn-outline-primary" href="<?= site_url('rekanan/edit/'.$r['id_rekanan']) ?>"><i class="bi bi-pencil-square"></i></a>
              <a class="btn btn-sm btn-outline-danger" href="<?= site_url('rekanan/delete/'.$r['id_rekanan']) ?>" onclick="return confirm('Hapus shipper ini?')"><i class="bi bi-trash"></i></a>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($rows)): ?>
          <tr><td colspan="6" class="text-center text-muted">Belum ada data.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php $this->load->view('layout/footer'); ?>
