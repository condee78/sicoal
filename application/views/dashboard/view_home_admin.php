<div class="pagetitle">
    <h1>Rekapitulasi Data Limbah Non B3 Berdasarkan Sumber</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= base_url(); ?>">Home</a></li>
            <li class="breadcrumb-item active">Dashboard Admin LBE</li>
        </ol>
    </nav>
</div><!-- End Page Title -->

<section class="section dashboard">
    <div class="row">
        <div class="col-md-12">
            <div class="card">

                <div class="card-body mt-3">
                    <form class="row">
                        <div class="col-md-4">
                            <div class="form-floating mb-3">
                                <select class="form-select" id="floatingSelect" aria-label="Bulan" name="bulan">
                                    <?php for ($month = 1; $month <= 12; $month++) { ?>
                                        <option value="<?= $month ?>" <?= ($month == $bulan) ? 'selected' : ''; ?>>
                                            <?= date('F', mktime(0, 0, 0, $month, 10)); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                                <label for="floatingSelect">Bulan</label>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-floating mb-3">
                                <select class="form-select" id="floatingSelect" aria-label="Tahun" name="tahun">
                                    <?php for ($year = date('Y'); $year > date('Y') - 5; $year--) { ?>
                                        <option value="<?= $year ?>" <?= ($year == $tahun) ? 'selected' : ''; ?>>
                                            <?= $year ?>
                                        </option>
                                    <?php } ?>
                                </select>
                                <label for="floatingSelect">Tahun</label>
                            </div>
                        </div>

                        <div class="col-md-4 mt-1">
                            <button type="submit" class="btn btn-lg btn-primary"><i class="bi bi-search"></i></button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <div class="row">

        <div class="col-xxl-3 col-md-3">
            <div class="card info-card sales-card">
                <div class="card-body">
                    <h5 class="card-title">Dihasilkan <span>| <?= date('F', mktime(0, 0, 0, $bulan, 10)) . ' ' . $tahun; ?></span></h5>

                    <div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                            <i class="bi bi-file-text"></i>
                        </div>
                        <div class="ps-3">
                            Fly Ash <span class="text-danger fw-bold"><?= number_format((@$row_total->total_fa / 1000), 2) ?></span> <span class="text-muted small pt-2 ps-1">ton</span>

                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                            <i class="bi bi-file-text"></i>
                        </div>
                        <div class="ps-3">
                            Bottom Ash <span class="text-danger fw-bold"><?= number_format((@$row_total->total_ba / 1000), 2) ?></span> <span class="text-muted small pt-2 ps-1">ton</span>

                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="col-xxl-3 col-md-3">
            <div class="card info-card customers-card">
                <div class="card-body">
                    <h5 class="card-title">Telah Dikelola Lanjut <span>| <?= date('F', mktime(0, 0, 0, $bulan, 10)) . ' ' . $tahun; ?></span></h5>

                    <!--<div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                            <i class="bi bi-truck"></i>
                        </div>
                        <div class="ps-3">
                            <span class="text-danger fw-bold"><?= $row_total->total / 1000 ?></span> <span class="text-muted small pt-2 ps-1">ton</span>
                        </div>
                    </div>-->
                    <div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                            <i class="bi bi-file-text"></i>
                        </div>
                        <div class="ps-3">
                            Fly Ash <span class="text-danger fw-bold"><?= number_format((@$row_total->total_fa / 1000), 2) ?></span> <span class="text-muted small pt-2 ps-1">ton</span>

                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                            <i class="bi bi-file-text"></i>
                        </div>
                        <div class="ps-3">
                            Bottom Ash <span class="text-danger fw-bold"><?= number_format((@$row_total->total_ba / 1000), 2) ?></span> <span class="text-muted small pt-2 ps-1">ton</span>

                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="col-xxl-3 col-md-3">
            <div class="card info-card revenue-card">
                <div class="card-body">
                    <h5 class="card-title">Disimpan di TPS <span>| <?= date('F', mktime(0, 0, 0, $bulan, 10)) . ' ' . $tahun; ?></span></h5>

                    <div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                            <i class="bi bi-file-text"></i>
                        </div>
                        <div class="ps-3">
                            Fly Ash <span class="text-danger fw-bold">0</span> <span class="text-muted small pt-2 ps-1">ton</span>

                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                            <i class="bi bi-file-text"></i>
                        </div>
                        <div class="ps-3">
                            Bottom Ash <span class="text-danger fw-bold">0</span> <span class="text-muted small pt-2 ps-1">ton</span>

                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="col-xxl-3 col-md-3">
            <div class="card info-card revenue-card">
                <div class="card-body">
                    <h5 class="card-title">Jumlah Ritase <span>| <?= date('F', mktime(0, 0, 0, $bulan, 10)) . ' ' . $tahun; ?></span></h5>

                    <div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                            <i class="bi bi-truck"></i>
                        </div>
                        <div class="ps-3">
                            <span class="text-success fw-bold"><?= $row_jumlah->total ?></span> <span class="text-muted small pt-2 ps-1">ritase</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <div class="row">

        <div class=" col-xxl-4 col-md-4">
            <a href="<?= site_url("lbe_request") ?>">
                <div class="card info-card sales-card">
                    <div class="card-body">
                        <h5 class="card-title">Request menunggu approval</h5>

                        <div class="d-flex align-items-center">
                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-clock"></i>
                            </div>
                            <div class="ps-3">
                                <span class="text-primary pt-1 fw-bold"><?= $jml_req ?></span> <span class="text-muted small pt-2 ps-1">request</span>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-xxl-4 col-md-4">
            <div class="card info-card customers-card">
                <div class="card-body">
                    <h5 class="card-title">Request disetujui <span>| <?= date('F', mktime(0, 0, 0, $bulan, 10)) . ' ' . $tahun; ?></span></h5>

                    <div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                            <i class="bi bi-truck"></i>
                        </div>
                        <div class="ps-3">
                            <span class="text-danger fw-bold"><?= $jml_approved ?></span> <span class="text-muted small pt-2 ps-1">request</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="col-xxl-4 col-md-4">
            <div class="card info-card revenue-card">
                <div class="card-body">
                    <h5 class="card-title">Request selesai <span>| <?= date('F', mktime(0, 0, 0, $bulan, 10)) . ' ' . $tahun; ?></span></h5>

                    <div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                            <i class="bi bi-check"></i>
                        </div>
                        <div class="ps-3">
                            <span class="text-success fw-bold"><?= $jml_completed ?></span> <span class="text-muted small pt-2 ps-1">request</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <div class="row">

        <div class="col-6">
            <div class="card top-selling overflow-auto">

                <div class="card-body pb-0">
                    <h5 class="card-title">Kontrak Pengangkut Hampir Expire<span> | kurang dari 30 hari</span></h5>

                    <table class="table table-borderless">
                        <thead>
                            <tr>
                                <th scope="col">No</th>
                                <th scope="col">Nama Pengangkut</th>
                                <th scope="col">Telp</th>
                                <th scope="col">Tgl Expire</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1;
                            foreach ($pengangkut_expire->result() as $rowp) { ?>
                                <tr>
                                    <th scope="row"><?= $no++ ?></th>
                                    <td><?= $rowp->nama_perusahaan ?></td>
                                    <td><?= $rowp->telp ?></td>
                                    <td class="fw-bold"><?= $rowp->tgl_expire ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>

                </div>

            </div>
        </div>

        <div class="col-6">
            <div class="card top-selling overflow-auto">

                <div class="card-body pb-0">
                    <h5 class="card-title">Kontrak Pemanfaat Hampir Expire<span> | kurang dari 30 hari</span></h5>


                    <table class="table table-borderless">
                        <thead>
                            <tr>
                                <th scope="col">No</th>
                                <th scope="col">Nama Pemanfaat</th>
                                <th scope="col">Telp</th>
                                <th scope="col">Tgl Expire</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pemanfaat_expire->result() as $rowp) { ?>
                                <tr>
                                    <th scope="row"><?= $no++ ?></th>
                                    <td><?= $rowp->nama_perusahaan ?></td>
                                    <td><?= $rowp->telp ?></td>
                                    <td class="fw-bold"><?= $rowp->tgl_expire ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>

                </div>

            </div>
        </div>

    </div>

</section>