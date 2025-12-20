<div class="pagetitle">
    <h1>Petunjuk Pengguna</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= base_url() ?>">Home</a></li>
            <li class="breadcrumb-item active">Petunjuk Pengguna</li>
        </ol>
    </nav>
</div><!-- End Page Title -->

<section class="section">
    <div class="row align-items-top">

        <div class="col-lg-12">

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Download Petunjuk Penggunaan System</h5>
                    <p class="card-text">
                        <i class="bi bi-file-pdf"></i> untuk <?=$this->session->userdata('groupname')?>
                        <a target="_blank" href="<?= base_url() . 'dlug/'.$this->session->userdata('groupname').'.pdf'; ?>" class="btn btn-sm btn-primary">
                            <i class="bi bi-download"></i> Download
                        </a>
                    </p>
                    
                    <!--
                    <p class="card-text">
                        <i class="bi bi-file-pdf"></i> untuk Administrator System (Admin LBE)
                        <a target="_blank" href="<?= base_url() . 'dlug/admi1.pdf'; ?>" class="btn btn-sm btn-primary">
                            <i class="bi bi-download"></i> Download
                        </a>
                    </p>

                    <p class="card-text">
                        <i class="bi bi-file-pdf"></i> untuk Pemanfaat
                        <a target="_blank" href="<?= base_url() . 'dlug/pema4.pdf'; ?>" class="btn btn-sm btn-primary">
                            <i class="bi bi-download"></i> Download
                        </a>
                    </p>

                    <p class="card-text">
                        <i class="bi bi-file-pdf"></i> untuk Pengangkut
                        <a target="_blank" href="<?= base_url() . 'dlug/peng2.pdf'; ?>" class="btn btn-sm btn-primary">
                            <i class="bi bi-download"></i> Download
                        </a>
                    </p>

                    <p class="card-text">
                        <i class="bi bi-file-pdf"></i> untuk Operator Timbangan
                        <a target="_blank" href="<?= base_url() . 'dlug/timb3.pdf'; ?>" class="btn btn-sm btn-primary">
                            <i class="bi bi-download"></i> Download
                        </a>
                    </p>
                    -->
                </div>
            </div>

        </div>

    </div>
</section>