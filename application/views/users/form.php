<?php
$is_edit = !empty($row);
$title = $is_edit ? 'Ubah Pengguna' : 'Tambah Pengguna';
$this->load->view('layout/header',['title'=>$title]);
?>
<div class="card">
  <div class="card-header bg-white"><strong><?= $title; ?></strong></div>
  <div class="card-body">
    <?php if($this->session->flashdata('err')): ?>
      <div class="alert alert-danger"><?= $this->session->flashdata('err'); ?></div>
    <?php endif; ?>

    <form method="post" action="<?= site_url('users/save') ?>" class="row g-3">
      <input type="hidden" name="userid" value="<?= $row['userid'] ?? 0 ?>">
      <div class="col-12 col-md-4">
        <label class="form-label">Username *</label>
        <input type="text" name="username" required class="form-control"
               value="<?= htmlentities($row['username'] ?? '') ?>">
      </div>
      <div class="col-12 col-md-4">
        <label class="form-label">Nama *</label>
        <input type="text" name="nama" required class="form-control"
               value="<?= htmlentities($row['nama'] ?? '') ?>">
      </div>
      <div class="col-12 col-md-4">
        <label class="form-label">Role *</label>
        <select name="groupid" class="form-select" required>
          <?php
            $me = (int)$me_group;
            $curr = isset($row['groupid']) ? (int)$row['groupid'] : 2;
            // jika admin LBE, sembunyikan super admin
            if ($me===1){
              $roles = [1=>'Admin LBE', 2=>'Staff LBE'];
            } else {
              $roles = [0=>'Super Admin', 1=>'Admin LBE', 2=>'Staff LBE'];
            }
            foreach($roles as $gid=>$label){
              $sel = ($curr===$gid)?'selected':'';
              echo "<option value=\"{$gid}\" {$sel}>{$label}</option>";
            }
          ?>
        </select>
      </div>

      <div class="col-12 col-md-4">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control"
               value="<?= htmlentities($row['email'] ?? '') ?>">
      </div>
      <div class="col-12 col-md-4">
        <label class="form-label">Telp</label>
        <input type="text" name="telp" class="form-control"
               value="<?= htmlentities($row['telp'] ?? '') ?>">
      </div>
      <div class="col-12 col-md-4">
        <label class="form-label">Kode (opsional)</label>
        <input type="text" name="kode" class="form-control"
               value="<?= htmlentities($row['kode'] ?? '') ?>">
      </div>

      <div class="col-12 col-md-4">
        <label class="form-label"><?= $is_edit?'Password (opsional)':'Password *' ?></label>
        <input type="password" name="password" class="form-control" <?= $is_edit?'':'required' ?>>
      </div>
      <div class="col-12 col-md-4">
        <label class="form-label"><?= $is_edit?'Konfirmasi Password (opsional)':'Konfirmasi Password *' ?></label>
        <input type="password" name="password2" class="form-control" <?= $is_edit?'':'required' ?>>
      </div>

      <div class="col-12 d-flex justify-content-between">
        <a href="<?= site_url('users') ?>" class="btn btn-outline-secondary">Kembali</a>
        <button class="btn btn-primary"><i class="bi bi-save"></i> Simpan</button>
      </div>
    </form>
  </div>
</div>
<?php $this->load->view('layout/footer'); ?>
