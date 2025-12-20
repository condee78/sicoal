<div class="pagetitle">
    <h1>Reset Password</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= base_url() ?>">Home</a></li>
            <li class="breadcrumb-item active">Reset Password</li>
        </ol>
    </nav>
</div><!-- End Page Title -->
<section class="section">
    <div class="row">

        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <!-- Floating Labels Form -->
                    <?php echo validation_errors(); ?>
                    <form class="row g-3 needs-validation" novalidate method="post" action="<?= site_url('reset_password/save'); ?>">
                        <h5 class="card-header">User Login System</h5>

                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="floatingNama" placeholder="Nama" disabled readonly=readonly value="<?php echo @$row->nama ?>">
                                <label for="floatingNama">Nama</label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="floatingName" placeholder="User Name" disabled readonly=readonly value="<?php echo @$row->username ?>">
                                <label for="floatingName">User Name</label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="password" class="form-control" id="floatingPass1" placeholder="New Password" name="password1" value="<?php echo @$row->password ?>">
                                <label for="floatingPass1">New Password</label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="password" class="form-control" id="floatingPass2" placeholder="Confirm New Password" name="password2" value="<?php echo @$row->password ?>">
                                <label for="floatingPass2">Confirm New Password</label>
                            </div>
                        </div>
                        <input type="hidden" name="userid" value="<?php echo @$row->userid ?>">

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