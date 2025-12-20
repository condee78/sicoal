<!DOCTYPE html>

<html lang="en">



<head>

    <meta charset="utf-8">

    <meta content="width=device-width, initial-scale=1.0" name="viewport">



    <title>Login - CoalSM - Lestari Banten Energi</title>

    <meta content="" name="description">

    <meta content="" name="keywords">



    <!-- Favicons -->

    <link href="<?= base_url(); ?>assets/img/logo.png" rel="icon">

    <link href="<?= base_url(); ?>assets/img/logo.png" rel="apple-touch-icon">



    <!-- Google Fonts -->

    <link href="https://fonts.gstatic.com" rel="preconnect">

    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">



    <!-- Vendor CSS Files -->

    <link href="<?= base_url(); ?>assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <link href="<?= base_url(); ?>assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">

    <link href="<?= base_url(); ?>assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">

    <link href="<?= base_url(); ?>assets/vendor/quill/quill.snow.css" rel="stylesheet">

    <link href="<?= base_url(); ?>assets/vendor/quill/quill.bubble.css" rel="stylesheet">

    <link href="<?= base_url(); ?>assets/vendor/remixicon/remixicon.css" rel="stylesheet">

    <link href="<?= base_url(); ?>assets/vendor/simple-datatables/style.css" rel="stylesheet">



    <!-- Template Main CSS File -->

    <link href="<?= base_url(); ?>assets/css/style.css" rel="stylesheet">

    <style>

  /* default untuk desktop */

  body {

    /* [C] change bg login LBE
    background-image: url('<?= base_url(); ?>assets/img/bg.png');
    */
    background-image: url('<?= base_url(); ?>assets/img/bg_login_LBE.jpeg');

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

</style>

</head>



<body>



    <main>

        <div class="container">



            <section class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">

                <div class="container">

                    <div class="row justify-content-center">

                        <div class="col-lg-4 col-md-6 d-flex flex-column align-items-center justify-content-center">



                            <div class="d-flex justify-content-center py-4">

                                <a href="index.html" class="logo d-flex align-items-center w-auto">

                                    <img src="<?= base_url(); ?>assets/img/logo.png" alt="">

                                    <span class="d-none d-lg-block">Coal-SM</span>

                                </a>

                            </div><!-- End Logo -->



                            <div class="card mb-3">



                                <div class="card-body">



                                    <div class="pt-4 pb-2">

                                        <h5 class="card-title text-center pb-0 fs-4">Masuk ke akun Anda</h5>

                                        <?php echo $this->session->flashdata('message'); ?>

                                        <p class="text-center small">

                                            Masukkan nama pengguna & kata sandi Anda untuk masuk

                                        </p>

                                    </div>



                                    <form class="row g-3 needs-validation" novalidate method="post" action="<?= site_url('auth'); ?>">



                                        <div class="col-12">

                                            <label for="yourUsername" class="form-label">Username</label>

                                            <input type="text" name="username" class="form-control" id="yourUsername" required>

                                            <div class="invalid-feedback">Silakan masukkan nama pengguna Anda.</div>

                                        </div>



                                        <div class="col-12">

                                            <label for="yourPassword" class="form-label">Password</label>

                                            <input type="password" name="password" class="form-control" id="yourPassword" required>

                                            <div class="invalid-feedback">Silakan masukkan password Anda!</div>

                                        </div>



                                        <div class="col-12">

                                            <div class="form-check">

                                                <input class="form-check-input" type="checkbox" name="remember" value="true" id="rememberMe">

                                                <label class="form-check-label" for="rememberMe">Ingat saya</label>

                                            </div>

                                        </div>

                                        <div class="col-12">

                                            <button class="btn btn-primary w-100" type="submit">Login</button>

                                        </div>

                                    </form>



                                </div>

                            </div>



                        </div>

                    </div>

                </div>



            </section>



        </div>

    </main><!-- End #main -->



    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>



    <!-- Vendor JS Files -->

    <script src="<?= base_url(); ?>assets/vendor/apexcharts/apexcharts.min.js"></script>

    <script src="<?= base_url(); ?>assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <script src="<?= base_url(); ?>assets/vendor/chart.js/chart.min.js"></script>

    <script src="<?= base_url(); ?>assets/vendor/echarts/echarts.min.js"></script>

    <script src="<?= base_url(); ?>assets/vendor/quill/quill.min.js"></script>

    <script src="<?= base_url(); ?>assets/vendor/simple-datatables/simple-datatables.js"></script>

    <script src="<?= base_url(); ?>assets/vendor/tinymce/tinymce.min.js"></script>

    <script src="<?= base_url(); ?>assets/vendor/php-email-form/validate.js"></script>



    <!-- Template Main JS File -->

    <script src="<?= base_url(); ?>assets/js/main.js"></script>



</body>



</html>