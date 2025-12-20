<!doctype html><html lang="id"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Lupa Password Â· Coal Shipping</title>
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

  /* override untuk layar ¡Ü768px (bisa Anda sesuaikan breakpoint-nya) */
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
<body class="min-vh-100 d-flex align-items-center" >
<div class="container">
  <div class="card mx-auto" style="max-width:480px;border-radius:20px;box-shadow:0 10px 30px rgba(0,0,0,.06)">
    <div class="card-body p-4 p-md-5">
      <h5 class="mb-2"><i class="bi bi-shield-lock me-1 text-primary"></i> Lupa Password</h5>
      <p class="text-muted small mb-3">Masukkan <b>email</b> atau <b>username</b> yang terdaftar. Jika ada, sistem akan mengirim link reset.</p>

      <?php if (!empty($ok)): ?>
        <div class="alert alert-success"><?= $ok ?></div>
      <?php endif; ?>
      <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
      <?php endif; ?>

      <form method="post" action="<?= current_url() ?>">
        <div class="mb-3">
          <label class="form-label">Email / Username</label>
          <input type="text" name="identifier" class="form-control" required autofocus>
        </div>
        <div class="d-grid">
          <button class="btn btn-primary"><i class="bi bi-envelope me-1"></i> Kirim Link Reset</button>
        </div>
        <div class="text-center mt-3">
          <a href="<?= site_url('login') ?>" class="small"><i class="bi bi-arrow-left"></i> Kembali ke Login</a>
        </div>
      </form>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"></script>
</body></html>
