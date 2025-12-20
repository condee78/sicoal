<div class="pagetitle">
    <h1>Form Konfigurasi Sistem</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= base_url() ?>">Home</a></li>
            <li class="breadcrumb-item active">Konfigurasi Sistem</li>
        </ol>
    </nav>
</div><!-- End Page Title -->
<section class="section">
    <div class="row">

        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <!-- Floating Labels Form -->
                    <form class="row g-3 needs-validation" novalidate method="post" action="<?= site_url('config/save/'); ?>" enctype="multipart/form-data">

                        <h5 class="card-title">Konfigurasi Sistem</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="nama_perusahaan" placeholder="Nama Perusahaan" name="nama_perusahaan" required value="<?php echo @$row->nama_perusahaan ?>">
                                    <label for="nama_perusahaan">Nama Perusahaan</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="nama_perusahaan" placeholder="Email Penerima Notifikasi" name="email" required value="<?php echo @$row->email ?>">
                                    <label for="nama_perusahaan">Email Penerima Notifikasi</label>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="alamat_perusahaan" placeholder="Alamat Perusahaan" name="alamat_perusahaan" required value="<?php echo @$row->alamat_perusahaan ?>">
                                    <label for="alamat_perusahaan">Alamat Perusahaan</label>
                                </div>
                            </div>


                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="quota_fa" placeholder="Quota Fly Ash" name="quota_fa" required value="<?php echo @$row->quota_fa ?>">
                                    <label for="quota_fa">Quota Fly Ash</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="quota_ba" placeholder="Quota Buttom Ash" name="quota_ba" required value="<?php echo @$row->quota_ba ?>">
                                    <label for="quota_ba">Quota Bottom Ash</label>
                                </div>
                            </div>



                        </div>

                        <h5 class="card-title">Penandatangan BAPL dan DI Checked by</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="ttd_nama" placeholder="TTD Nama" name="ttd_nama" value="<?php echo @$row->ttd_nama ?>">
                                    <label for="ttd_nama">TTD Nama</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="ttd_jabatan" placeholder="TTD Jabatan" name="ttd_jabatan" value="<?php echo @$row->ttd_jabatan ?>">
                                    <label for="ttd_jabatan">TTD Jabatan</label>
                                </div>
                            </div>


                            <div class="col-md-4">
                                <div class="form-floating">
                                    <input type="file" class="form-control" id="file_ttd" placeholder="File TTD" name="formFile[]" value="<?php echo @$row->file_ttd ?>">
                                    <label for="file_ttd">File TTD</label>
                                </div>

                            </div>
                            <div class="col-md-4">
                                <img width='140' src='<?php echo base_url('_berkas/' . @$row->file_ttd); ?>'>
                            </div>
                        </div>

                        <h5 class="card-title">Penandatangan DI Accepted by</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="ttd_nama" placeholder="TTD Nama 2" name="ttd_nama2" value="<?php echo @$row->ttd_nama2 ?>">
                                    <label for="ttd_nama">TTD Nama 2</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="ttd_jabatan" placeholder="TTD Jabatan 2" name="ttd_jabatan2" value="<?php echo @$row->ttd_jabatan2 ?>">
                                    <label for="ttd_jabatan">TTD Jabatan 2</label>
                                </div>
                            </div>


                            <div class="col-md-4">
                                <div class="form-floating">
                                    <input type="file" class="form-control" id="file_ttd" placeholder="File TTD 2" name="formFile[]" value="<?php echo @$row->file_ttd2 ?>">
                                    <label for="file_ttd">File TTD 2</label>
                                </div>

                            </div>
                            <div class="col-md-4">
                                <img width='140' src='<?php echo base_url('_berkas/' . @$row->file_ttd2); ?>'>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <button type="reset" class="btn btn-secondary">Reset</button>
                        </div>
                    </form><!-- End floating Labels Form -->

                </div>
            </div>

        </div>
    </div>
</section>