<!doctype html><html lang="id"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Ganti Password · Coal Shipping</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
    .login-card{
      max-width: 420px; margin: 6vh auto; border:0; border-radius:24px;
      box-shadow: 0 10px 30px rgba(0,0,0,.06);
    }
      body {
    background-image: url('<?= base_url(); ?>assets/img/bg.png');
    background-repeat: no-repeat;
    background-size: 100% 100%;
  }

  /* override untuk layar ≤768px (bisa Anda sesuaikan breakpoint-nya) */
  @media (max-width: 768px) {
    body {
      background-image: url('<?= base_url(); ?>assets/img/bg-hp.png');
      /* Anda bisa pilih cover, contain, atau persentase lain */
      background-size: cover;
    }
  }
    .brand{
      font-family:'Poppins', sans-serif; letter-spacing:.3px;
    }
  </style>
</head>
<body class="min-vh-100" >
<div class="container py-4">
  <div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
      <div class="card shadow-sm" style="border-radius:20px">
        <div class="card-body p-4">
          <h5 class="mb-3"><i class="bi bi-key me-1 text-primary"></i> Ganti Password</h5>

          <?php if(!empty($error)): ?>
            <div class="alert alert-danger py-2"><?= $error ?></div>
          <?php endif; ?>
          <?php if($this->session->flashdata('ok')): ?>
            <div class="alert alert-success py-2"><?= $this->session->flashdata('ok') ?></div>
          <?php endif; ?>

          <form method="post" action="<?= current_url() ?>">
            <div class="mb-3">
              <label class="form-label">Password sekarang</label>
              <input type="password" name="current" class="form-control" required autocomplete="current-password">
            </div>
            <div class="mb-3">
              <label class="form-label">Password baru</label>
              <input type="password" name="password" id="pw" class="form-control" required minlength="8" autocomplete="new-password">
              <div class="form-text">Minimal 8 karakter. Disarankan kombinasi huruf besar/kecil, angka, simbol.</div>
            </div>
            <div class="mb-3">
              <label class="form-label">Konfirmasi password baru</label>
              <input type="password" name="confirm" class="form-control" required minlength="8" autocomplete="new-password">
            </div>
            <div class="d-grid d-sm-flex gap-2">
              <button class="btn btn-primary"><i class="bi bi-check2-circle me-1"></i> Simpan</button>
              <a class="btn btn-outline-secondary" href="<?= site_url('shipments') ?>"><i class="bi bi-arrow-left"></i> Kembali</a>
            </div>
          </form>

        </div>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"></script>
</body></html>
