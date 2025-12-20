<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<!doctype html>

<html lang="id">

<head>

  <meta charset="utf-8">

  <meta name="viewport" content="width=device-width,initial-scale=1">

  <title><?= isset($title)?$title:'CoalSM' ?></title>


  <!-- [C] add favicon -->
  <link rel="apple-touch-icon" sizes="180x180" href="<?= base_url(); ?>assets/img/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="<?= base_url(); ?>assets/img/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="<?= base_url(); ?>assets/img/favicon-16x16.png">

  <!-- Google Fonts -->

  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700|Nunito:300,400,600,700|Poppins:300,400,500,600,700" rel="stylesheet">



  <!-- Bootstrap 5.2 -->

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet">



  <!-- Icons -->

  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">

  <link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">



  <!-- Simple-DataTables -->

  <link href="https://cdn.jsdelivr.net/npm/simple-datatables@9.0.0/dist/style.css" rel="stylesheet">

  <!-- [C] add this style for Shipment Progress Step abd Tab -->
  <link href="<?= base_url(); ?>assets/css/shipment-progress.css" rel="stylesheet">


  <!-- Custom main.css (opsional) -->

  <style>

    body { font-family: "Open Sans", sans-serif; }

    .sidebar {

      width: 280px; max-width: 85vw;

    }

    .content-wrap {

      min-height: 100vh; display: flex; flex-direction: column;

    }

    .main {

      flex: 1 0 auto; padding: 1rem;

    }

    .card-kpi .icon {

      width: 48px; height: 48px; display:flex; align-items:center; justify-content:center;

      border-radius: 10px; background: rgba(13,110,253,.08);

    }

    @media (min-width: 992px) {

      .main { padding: 1.5rem 2rem; }

    }

  </style>

</head>


<body class="bg-light">

  <!-- Topbar -->

<nav class="navbar navbar-expand-lg bg-white shadow-sm sticky-top">

  <div class="container-fluid">

    <!-- Toggler hanya untuk mobile -->

    <button class="btn btn-outline-secondary me-2 d-lg-none" 

            data-bs-toggle="offcanvas" data-bs-target="#offcanvasNav" aria-controls="offcanvasNav">

      <i class="bi bi-list"></i>

    </button>



    <a class="navbar-brand fw-bold" href="<?= site_url('dashboard') ?>">

      <i class="ri-ship-2-line me-1"></i> CoalSM

    </a>



    <!-- MENU DESKTOP -->

    <ul class="navbar-nav ms-3 d-none d-lg-flex">

       <?php if (is_admin_lbe() || is_superadmin()): ?>

       <li class="nav-item"><a class="nav-link" href="<?= site_url('dashboard') ?>"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>

       <?php endif; ?>

      <li class="nav-item"><a class="nav-link" href="<?= site_url('shipments') ?>"><i class="bi bi-truck-front me-1"></i>Shipments</a></li>

      <li class="nav-item"><a class="nav-link" href="<?= site_url('scores') ?>"><i class="bi bi-trophy me-1"></i>Shipper Performance</a></li>

      <?php if (is_admin_lbe() || is_superadmin()): ?>

    <li class="nav-item"><a class="nav-link" href="<?= site_url('shipments/import') ?>"><i class="bi bi-file-earmark-spreadsheet me-1"></i>Import Excel</a></li>



    <li class="nav-item"><a class="nav-link" href="<?= site_url('rekanan') ?>"><i class="bi bi-file-earmark-spreadsheet me-1"></i>Database Vendor</a></li>
<li class="nav-item"><a class="nav-link" href="<?= site_url('users') ?>"><i class="bi bi-file-earmark-spreadsheet me-1"></i>DB Admin LBE</a></li>
    <li class="nav-item"><a class="nav-link" href="<?= site_url('periods') ?>"><i class="bi bi-file-earmark-spreadsheet me-1"></i>Set Period</a></li>



      

<?php endif; ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<li class="nav-item dropdown">
  <a class="nav-link position-relative" href="#" data-bs-toggle="dropdown" aria-expanded="false" id="notifBell">
    <i class="bi bi-bell"></i>
    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notifCount" style="display:none">0</span>
  </a>
  <ul class="dropdown-menu dropdown-menu-end p-0" style="min-width:360px;">
    <li class="px-3 py-2 border-bottom"><strong>Notifikasi</strong></li>
    <li>
      <div id="notifList" style="max-height:320px; overflow:auto;">
        <div class="p-3 text-center text-muted">Memuat...</div>
      </div>
    </li>
  </ul>
</li>

<script>
(function(){
  async function loadNotif(){
    try{
      const r = await fetch('<?= site_url('notifications/unread') ?>', {headers:{'X-Requested-With':'XMLHttpRequest'}});
      const j = await r.json();
      const cnt = document.getElementById('notifCount');
      const list = document.getElementById('notifList');
      cnt.textContent = j.count || 0;
      cnt.style.display = (j.count>0)?'inline-block':'none';

      if (!j.items || !j.items.length){
        list.innerHTML = '<div class="p-3 text-center text-muted">Tidak ada notifikasi baru.</div>';
        return;
      }
      list.innerHTML = j.items.map(it=> `
        <div class="p-3 border-bottom">
          <div class="d-flex justify-content-between">
            <div class="fw-semibold">${escapeHtml(it.title)}</div>
            <a href="#" class="small text-decoration-none" data-read="${it.target_id}">Tandai dibaca</a>
          </div>
          <div class="small text-muted">${escapeHtml(it.type)} • ${it.created_at}</div>
          <div class="mt-1">${escapeHtml(it.body||'')}</div>
          ${it.shipment_id ? `<div class="mt-2"><a href="<?= site_url('shipments/detail/') ?>${it.shipment_id}" class="btn btn-sm btn-outline-primary">Lihat Shipment</a></div>`:''}
        </div>`).join('');
      list.querySelectorAll('[data-read]').forEach(a=>{
        a.addEventListener('click', async (e)=>{
          e.preventDefault();
          const id=a.getAttribute('data-read');
          await fetch('<?= site_url('notifications/read/') ?>'+id, {headers:{'X-Requested-With':'XMLHttpRequest'}});
          loadNotif();
        });
      });
    }catch(e){
      console.error(e);
    }
  }
  function escapeHtml(s){return (s||'').replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));}
  // load awal + refresh tiap 60s
  loadNotif(); setInterval(loadNotif, 60000);
})();
</script>

    </ul>







<?php if (is_logged_in()): ?>

  <div class="dropdown ms-auto">

    <button class="btn btn-sm btn-light dropdown-toggle" data-bs-toggle="dropdown">

      <i class="bi bi-person-circle me-1"></i><?= htmlentities($this->session->userdata('username')) ?>

    </button>

    <ul class="dropdown-menu dropdown-menu-end">

      <li><a class="dropdown-item" href="<?= site_url('account/password') ?>"><i class="bi bi-key me-1"></i> Ganti Password</a></li>

      <li><a class="dropdown-item" href="<?= site_url('logout') ?>"><i class="bi bi-box-arrow-right me-1"></i> Logout</a></li>

    </ul>

  </div>

<?php endif; ?>





  </div>

</nav>





<!-- Offcanvas tetap untuk mobile -->

<div class="offcanvas offcanvas-start sidebar" tabindex="-1" id="offcanvasNav" aria-labelledby="offcanvasNavLabel">

  <div class="offcanvas-header">

    <h5 class="offcanvas-title" id="offcanvasNavLabel"><i class="ri-dashboard-2-line me-1"></i> Menu</h5>

    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>

  </div>

  <div class="offcanvas-body p-0">

    <div class="list-group list-group-flush">

      <a href="<?= site_url('dashboard') ?>" class="list-group-item list-group-item-action"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>

      <a href="<?= site_url('shipments') ?>" class="list-group-item list-group-item-action"><i class="bi bi-truck-front me-2"></i>Shipments</a>

      <a href="<?= site_url('scores') ?>" class="list-group-item list-group-item-action"><i class="bi bi-trophy me-2"></i>Shipper Performance</a>

      <?php if (is_admin_lbe() || is_superadmin()): ?>

            <a href="<?= site_url('shipments/import') ?>" class="list-group-item list-group-item-action"><i class="bi bi-file-earmark-spreadsheet me-2"></i>Import Excel</a>



       <a href="<?= site_url('rekanan') ?>" class="list-group-item list-group-item-action">

    <i class="bi bi-journal-text me-2"></i> Vendor / Shipper

  </a>



  <a href="<?= site_url('periods') ?>" class="list-group-item list-group-item-action">

    <i class="bi bi-journal-text me-2"></i> Periods

  </a>

<?php endif; ?>



    </div>

  </div>

</div>





  <!-- Content -->

  <div class="content-wrap">

    <div class="container-fluid main">

      <!-- Breadcrumb (opsional) -->

      <?php if (!empty($breadcrumb)): ?>

        <nav aria-label="breadcrumb">

          <ol class="breadcrumb">

            <?php foreach ($breadcrumb as $label => $url): ?>

              <?php if ($url): ?>

                <li class="breadcrumb-item"><a href="<?= $url ?>"><?= $label ?></a></li>

              <?php else: ?>

                <li class="breadcrumb-item active" aria-current="page"><?= $label ?></li>

              <?php endif; ?>

            <?php endforeach; ?>

          </ol>

        </nav>

      <?php endif; ?>

      

      <?php

 //   $this->load->model('Period_model');

 // $activeY = $this->Period_model->active_year();

?>
<!--<span class="badge bg-info-subtle text-dark ms-2">Period: <?= $activeY ?></span>-->

