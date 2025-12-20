<?php
$title = ($mode==='edit'?'Edit':'Tambah').' Vendor';
$this->load->view('layout/header',['title'=>$title,'breadcrumb'=>['Database Vendor'=>site_url('rekanan'),$title=>null]]);
?>
<?php if($this->session->flashdata('err')): ?>
  <div class="alert alert-danger py-2"><?= $this->session->flashdata('err') ?></div>
<?php endif; ?>

<form method="post" action="<?= site_url('rekanan/save'.($mode==='edit'?'/'.$row['id_rekanan']:'')) ?>" class="row g-3">
  <div class="col-12 col-md-3">
    <label class="form-label">Kode</label>
    <input type="text" name="kode" class="form-control" value="<?= set_value('kode',$row['kode']??'') ?>" required maxlength="32">
    <div class="form-text">Harus unik (dipakai di shipments).</div>
  </div>
  <div class="col-12 col-md-9">
    <label class="form-label">Nama Perusahaan</label>
    <input type="text" name="nama_perusahaan" class="form-control" value="<?= set_value('nama_perusahaan',$row['nama_perusahaan']??'') ?>" required maxlength="200">
  </div>

  <div class="col-12">
    <label class="form-label">Alamat Perusahaan</label>
    <textarea name="alamat_perusahaan" class="form-control" rows="2" required><?= set_value('alamat_perusahaan',$row['alamat_perusahaan']??'') ?></textarea>
  </div>

  <div class="col-12 col-md-4">
    <label class="form-label">Email</label>
    <input type="text" name="email" class="form-control" value="<?= set_value('email',$row['email']??'') ?>" required>
  </div>
  <div class="col-12 col-md-4">
    <label class="form-label">Telepon</label>
    <input type="text" name="telp" class="form-control" value="<?= set_value('telp',$row['telp']??'') ?>" required>
  </div>
  <div class="col-12 col-md-4">
    <label class="form-label">WA</label>
    <input type="text" name="wa" class="form-control" value="<?= set_value('wa',$row['wa']??'') ?>">
  </div>

  <div class="col-12 col-md-6">
    <label class="form-label">Nama Kontak</label>
    <input type="text" name="ttd_nama" class="form-control" value="<?= set_value('ttd_nama',$row['ttd_nama']??'') ?>" required>
  </div>
  <div class="col-12 col-md-6">
    <label class="form-label">Jabatan</label>
    <input type="text" name="ttd_jabatan" class="form-control" value="<?= set_value('ttd_jabatan',$row['ttd_jabatan']??'') ?>" required>
  </div>

  <hr class="mt-2">

  <div class="col-12 col-md-4">
    <label class="form-label">Username (ci_users)</label>
    <input type="text" name="username" class="form-control" value="<?= set_value('username',$row['kode']??'') ?>" maxlength="100">
    <div class="form-text">Default pakai <b>kode</b> jika kosong.</div>
  </div>
  <div class="col-12 col-md-4">
    <label class="form-label"><?= $mode==='edit'?'Ganti':'Set' ?> Password (opsional)</label>
    <input type="text" name="set_password" class="form-control" value="">
    <div class="form-text">Kosongkan jika tidak ingin mengubah.</div>
  </div>
  <div class="col-12 col-md-4 d-flex align-items-end">
    <div class="form-check">
      <input class="form-check-input" type="checkbox" name="send_mail" id="send_mail" value="1">
      <label class="form-check-label" for="send_mail">Kirim email kredensial</label>
    </div>
  </div>

  <div class="col-12 d-grid d-md-flex gap-2">
    <button class="btn btn-primary"><i class="bi bi-save me-1"></i> Simpan</button>
    <a class="btn btn-outline-secondary" href="<?= site_url('rekanan') ?>"><i class="bi bi-arrow-left"></i> Kembali</a>
  </div>
</form>

<?php $this->load->view('layout/footer'); ?>
