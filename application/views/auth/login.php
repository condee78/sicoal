<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sign In · Coal Shipping</title>

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Nunito:wght@600;700&family=Poppins:wght@400;600&display=swap" rel="stylesheet">

  <!-- Bootstrap 5.2 & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <!-- Boxicons & Remix Icon (opsional) -->
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">

  <!-- Template CSS kalau ada -->
  <link href="<?= base_url('assets/css/style.css') ?>" rel="stylesheet">

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
<body class="min-vh-100 d-flex align-items-center">

<div class="container">
  <div class="card login-card">
    <div class="card-body p-4 p-md-5">
      <div class="text-center mb-4">
        <div class="d-inline-flex align-items-center gap-2">
          <i class="ri-ship-2-line fs-3 text-primary"></i>
          <h5 class="brand m-0 fw-bold">Coal Shipping</h5>
        </div>
        <div class="text-muted small mt-1">Sign in to continue</div>
      </div>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger py-2"><?= $error ?></div>
      <?php endif; ?>

      <form method="post" action="<?= current_url().(!empty($_GET['next'])?'?next='.rawurlencode($_GET['next']):'') ?>">
        <div class="mb-3">
          <label class="form-label">Username</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-person"></i></span>
            <input type="text" name="username" class="form-control" autocomplete="username" required autofocus>
          </div>
        </div>
        <div class="mb-2">
          <label class="form-label">Password</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" name="password" class="form-control" autocomplete="current-password" required>
          </div>
        </div>
        <input type="hidden" name="next" value="<?= htmlspecialchars($_GET['next'] ?? '', ENT_QUOTES) ?>">
        <div class="d-grid mt-3">
          <button class="btn btn-primary"><i class="bi bi-box-arrow-in-right me-1"></i> Masuk</button>
        </div>
      </form>
<a href='<?=site_url('forgot');?>' >Forgot Password</a>
      <div class="text-center text-muted small mt-3">
        © <?= date('Y') ?> Coal Shipping · LBE
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= base_url('assets/js/main.js') ?>"></script>
</body>
</html>
