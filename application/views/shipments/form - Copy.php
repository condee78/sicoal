
<?php

  $this->load->view('layout/header', [

    'title'=> ($mode=='create'?'Tambah':'Ubah').' Shipment',

    'breadcrumb'=>['Shipments'=>site_url('shipments'), ($mode=='create'?'Tambah':'Ubah')=>null]

  ]);
  
  



  // role flags from helper

  $isVendor = is_vendor();

  $isStaff  = is_staff_lbe();

  $isAdmin  = is_admin_lbe() || is_superadmin();

?>

<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

      <!-- [C] add Shipment Progress Step -->
	    <div class="row justify-content-center">
	    	<div class="col-10">
		    	<div class="progressbar">
		        <div class="progress" id="progress"></div>
		   <!--  //progress-step-active progress-step-completed -->
		        <div class="progress-step  <?php if(isset($data['actual_arrival_load_port']) and $data['actual_arrival_load_port']!='0000-00-00 00:00:00') echo "progress-step-completed"; ?>" data-title="Loading Port"></div>
		        <div class="progress-step <?php if(isset($data['commence_loading']) and $data['actual_arrival_load_port']!='0000-00-00 00:00:00' and isset($data['complete_loading']) and $data['complete_loading']!='0000-00-00 00:00:00')echo "progress-step-completed"; ?>" data-title="Commence & Loading"></div>
		        <div class="progress-step <?php if(isset($data['actual_departure']) and $data['actual_departure']!='0000-00-00 00:00:00')echo "progress-step-completed";  ?>"  data-title="Departure"></div>
		        <div class="progress-step <?php if(isset($data['dt_load_sample']) and $data['dt_load_sample']!='0000-00-00 00:00:00') echo "progress-step-completed"; ?>"  data-title="Sample & Actual Received"></div>
		        <div class="progress-step <?php if(isset($data['complete_disc']) and $data['complete_disc']!='0000-00-00 00:00:00')echo "progress-step-completed"; ?>" data-title="Unloading"></div>
		        <div class="progress-step <?php if(isset($data['dt_coa_delivery']) and $data['dt_coa_delivery']!='0000-00-00 00:00:00')echo "progress-step-completed"; ?>" data-title="Documents"></div>
		        <div class="progress-step <?php if(isset($data['invoice_doc']) and $data['invoice_doc']!='0000-00-00 00:00:00')echo "progress-step-completed"; ?>" data-title="Completed Shipment"></div>
		        <div class="progress-step <?php if(isset($data['invoice_doc']) and $data['invoice_doc']!='0000-00-00 00:00:00')echo "progress-step-completed"; ?>" data-title="Invoicing"></div>
		        <div class="progress-step <?php if(isset($data['invoice_doc']) and $data['invoice_doc']!='0000-00-00 00:00:00')echo "progress-step-completed"; ?>" data-title="Completed Invoicing"></div>
		      </div>
	      </div>
	    </div>

<?php if ($dup = $this->session->flashdata('dup_error')): ?>
<script>
Swal.fire({
  icon: 'error',
  title: 'Duplikat Data',
  html: `<div class="text-start">
          <div><b>Vendor</b>: <?= htmlspecialchars($dup['rekanan_kode']) ?></div>
          <div><b>Kode Shipment</b>: <?= htmlspecialchars($dup['shipment_no']) ?></div>
          <div class="mt-2 text-danger"><?= htmlspecialchars($dup['msg']) ?></div>
        </div>`,
  confirmButtonText: 'Perbaiki',
  confirmButtonColor: '#d33',
  background: '#fff8f8',
  showClass: { popup: 'animate__animated animate__shakeX' }
});
</script>
<style>
  /* sorot field */
  #field_rekanan_kode, #field_shipment_no { box-shadow: 0 0 0 .2rem rgba(220,53,69,.25); border-color:#dc3545; }
</style>
<?php endif; ?>
<form method="post"

      action="<?= site_url('shipments/save'.($mode==='edit'?'/'.$data['id']:'')) ?>"

      enctype="multipart/form-data"

      class="row g-3">

    

  <div class="col-12 col-lg-8">

    <div class="card shadow-sm">

      <div class="card-header bg-white"><h6 class="m-0">Informasi Utama</h6></div>

      <div class="card-body">

        <div class="row g-3">

          <div class="col-12 col-md-4">

            <label class="form-label">Shipment No (C$)</label>

            <input id="field_shipment_no" name="shipment_no" class="form-control" value="<?= set_value('shipment_no',$data['shipment_no']??'') ?>" required>

          </div>

          <div class="col-12 col-md-4">

            <label class="form-label">MMSI / IMO</label>

            <input name="mmsi" class="form-control" value="<?= set_value('mmsi',$data['mmsi']??'') ?>" required>

          </div>

          <div class="col-12 col-md-4">

            <label class="form-label">Shipper (B$)</label>

            <?php if($isVendor): ?>

              <input class="form-control" value="<?= htmlentities($current_shipper['nama_perusahaan']) ?>" disabled>

              <input id='field_rekanan_kode' type="hidden" name="rekanan_kode" value="<?= $current_shipper['kode'] ?>">

            <?php else: ?>
                <input id='field_rekanan_kode' type="hidden" name="rekanan_kode" value="<?= @$data['rekanan_kode'] ?>">
              <select <?php if(isset($data['rekanan_kode'])) echo "disabled";else echo "";?> name="rekanan_kode" class="form-select" required>

                <option value="">Pilih</option>

                <?php foreach($shippers as $s): ?>

                  <option value="<?= $s['kode'] ?>" <?= set_select('rekanan_kode',$s['kode'], ($data['rekanan_kode']??'')==$s['kode']) ?>>

                    <?= htmlentities($s['nama_perusahaan']) ?>

                  </option>

                <?php endforeach;?>

              </select>

                            



            <?php endif; ?>

          </div>



          <div class="col-12 col-md-6">

            <label class="form-label">Nominated Vessel (D$)</label>

            <input name="nominated_vessel" class="form-control" value="<?= set_value('nominated_vessel',$data['nominated_vessel']??'') ?>">

          </div>

          <div class="col-12 col-md-6">

            <label class="form-label">Loading Port (E$)</label>

            <input name="loading_port" class="form-control" value="<?= set_value('loading_port',$data['loading_port']??'') ?>">

          </div>



          <div class="col-6 col-md-4">

            <label class="form-label">ETA Loading Port (F$)</label>

            <input type="date" name="eta_loading_port" class="form-control" value="<?= set_value('eta_loading_port',$data['eta_loading_port']??'') ?>">

          </div>

          <div class="col-6 col-md-4">

            <label class="form-label">ETA at LBE (M$)</label>

            <input type="date" name="eta_lbe" class="form-control" value="<?= set_value('eta_lbe',$data['eta_lbe']??'') ?>">

          </div>
          
            <?php if ($isStaff || $isAdmin){ ?>

        <div class="col-6 col-md-4">

            <label class="form-label">Shipment Status (AH$)</label>

            <select name="shipment_status" class="form-select">

              <?php foreach(['On process','Completed','Not Completed'] as $st): ?>

                <option <?= set_select('shipment_status',$st, ($data['shipment_status']??'')==$st) ?>><?= $st ?></option>

              <?php endforeach;?>

            </select>

          </div>
          <?php } ?>


        

        </div>

      </div>

    </div>



    <!-- VENDOR SECTION -->

    <?php if ($isVendor): ?>

    <div class="card shadow-sm mt-3">

      <div class="card-header bg-white"><h6 class="m-0">Input Vendor/Shipper (G$,H$,I$,J$,M$,X$,Z$ input)</h6></div>

      <div class="card-body">

         <!-- Tabs and pill -->

        <!-- Nav Tabs -->

        <ul class="nav nav-tabs nav-justified" id="myTab" role="tablist">

          <li class="nav-item" role="presentation">

            <button class="nav-link active" id="loading_port-tab" data-bs-toggle="tab"

                    data-bs-target="#loading_port" type="button" role="tab">Loading Port</button>

          </li>

          <li class="nav-item" role="presentation">

            <button class="nav-link" id="commence-tab" data-bs-toggle="tab"

                    data-bs-target="#commence" type="button" role="tab">Commence</button>

          </li>

          <li class="nav-item" role="presentation">

            <button class="nav-link" id="loading_complete-tab" data-bs-toggle="tab"

                    data-bs-target="#loading_complete" type="button" role="tab">Loading Complete</button>

          </li>

          <li class="nav-item" role="presentation">

            <button class="nav-link" id="departure-tab" data-bs-toggle="tab"

                    data-bs-target="#departure" type="button" role="tab">Departure</button>

          </li>

          <li class="nav-item" role="presentation">

            <button class="nav-link" id="sample-tab" data-bs-toggle="tab"

                    data-bs-target="#sample" type="button" role="tab">Sample & AWB Doc</button>

          </li>

          <li class="nav-item" role="presentation">

            <button class="nav-link" id="coa_cow_doc-tab" data-bs-toggle="tab"

                    data-bs-target="#coa_cow_doc" type="button" role="tab">COA & COW Doc</button>

          </li>

        </ul>

        <!-- Tab Content -->

        <div class="tab-content" id="myTabContent">

          <div class="tab-pane fade show active" id="loading_port" role="tabpanel">

            <div class="row g-3">

                <div class="col-6 col-md-6">

                <label class="form-label">Arrival Load Port (G$)</label>

                <input type="datetime-local" name="actual_arrival_load_port" class="form-control" value="<?= set_value('actual_arrival_load_port', isset($data['actual_arrival_load_port'])?date('Y-m-d\TH:i',strtotime($data['actual_arrival_load_port'])):'') ?>">

                 </div>

            </div>

          </div>

          <div class="tab-pane fade" id="commence" role="tabpanel">

            <div class="row g-3">

           <div class="col-6 col-md-6">

            <label class="form-label">Commence Loading (H$)</label>

            <input type="datetime-local" name="commence_loading" class="form-control" value="<?= set_value('commence_loading', isset($data['commence_loading'])?date('Y-m-d\TH:i',strtotime($data['commence_loading'])):'') ?>">

          </div>

            </div>

          </div>

          <div class="tab-pane fade" id="loading_complete" role="tabpanel">

            <div class="row g-3">

             <div class="col-6 col-md-6">

            <label class="form-label">Complete Loading (I$)</label>

            <input type="datetime-local" name="complete_loading" class="form-control" value="<?= set_value('complete_loading', isset($data['complete_loading'])?date('Y-m-d\TH:i',strtotime($data['complete_loading'])):'') ?>">

          </div>

            </div>

          </div>

          <div class="tab-pane fade" id="departure" role="tabpanel">

            <div class="row g-3">

              <div class="col-6 col-md-6">

            <label class="form-label">Actual Departure (J$)</label>

            <input type="datetime-local" name="actual_departure" class="form-control" value="<?= set_value('actual_departure', isset($data['actual_departure'])?date('Y-m-d\TH:i',strtotime($data['actual_departure'])):'') ?>">

          </div>

            </div>

          </div>

          <div class="tab-pane fade" id="sample" role="tabpanel">

            <div class="row g-3">

            <div class="col-6 col-md-4">

                <label class="form-label">Load AWB buyer sample (Z$)</label>

                <input type="datetime-local" name="dt_load_sample" class="form-control" value="<?= set_value('dt_load_sample', isset($data['dt_load_sample'])?date('Y-m-d\TH:i',strtotime($data['dt_load_sample'])):'') ?>">

             </div>

            </div>

          </div>

          <div class="tab-pane fade" id="coa_cow_doc" role="tabpanel">

            <div class="row g-3">

             <div class="col-6 col-md-6">

            <label class="form-label">COA Delivery Date (X$)</label>

            <input type="datetime-local" name="dt_coa_delivery" class="form-control" value="<?= set_value('dt_coa_delivery', isset($data['dt_coa_delivery'])?date('Y-m-d\TH:i',strtotime($data['dt_coa_delivery'])):'') ?>">

          </div>

            </div>

          </div>

        </div>

    

      

      </div>

    </div>

    <?php endif; ?>



    <!-- STAFF/ADMIN SECTION -->

    <?php if ($isStaff || $isAdmin): ?>

    <div class="card shadow-sm mt-3">

      <div class="card-header bg-white"><h6 class="m-0">Input Staff/Admin (U$,W$,Y$,Z$,AA$, AC..AL, AG, AH)</h6></div>

      <div class="card-body">

          

           <!-- Tabs and pill -->

        <!-- Nav Tabs -->

        <ul class="nav nav-tabs nav-justified" id="myTab" role="tablist">

          <li class="nav-item" role="presentation">

            <button class="nav-link active" id="sample_received-tab" data-bs-toggle="tab"

                    data-bs-target="#sample_received" type="button" role="tab">Sample Request/Received</button>

          </li>

          <li class="nav-item" role="presentation">

            <button class="nav-link" id="actual_received-tab" data-bs-toggle="tab"

                    data-bs-target="#actual_received" type="button" role="tab">Actual Received</button>

          </li>

          <li class="nav-item" role="presentation">

            <button class="nav-link" id="commence_unloading-tab" data-bs-toggle="tab"

                    data-bs-target="#commence_unloading" type="button" role="tab">Commence Unloading</button>

          </li>

          <li class="nav-item" role="presentation">

            <button class="nav-link" id="unloading_complete-tab" data-bs-toggle="tab"

                    data-bs-target="#unloading_complete" type="button" role="tab">Unloading Complete</button>

          </li>

          <!--

          <li class="nav-item" role="presentation">

            <button class="nav-link" id="cast_off-tab" data-bs-toggle="tab"

                    data-bs-target="#cast_off" type="button" role="tab">Vessel Cast Off</button>

          </li>

        -->

          <li class="nav-item" role="presentation">

            <button class="nav-link" id="coa_doc-tab" data-bs-toggle="tab"

                    data-bs-target="#coa_doc" type="button" role="tab">COA & Berita Acara Doc</button>

          </li>

          <li class="nav-item" role="presentation">

            <button class="nav-link" id="invoice_doc-tab" data-bs-toggle="tab"

                    data-bs-target="#invoice_doc" type="button" role="tab">Invoice & Payment</button>

          </li>

          <li class="nav-item" role="presentation">

            <button class="nav-link" id="others-tab" data-bs-toggle="tab"

                    data-bs-target="#others" type="button" role="tab">Others</button>

          </li>

        </ul>



        <!-- Tab Content -->

        <div class="tab-content" id="myTabContent">

          <div class="tab-pane fade show active g-3" id="sample_received" role="tabpanel">

            <div class="row g-3">

           <div class="col-6 col-md-4">

            <label class="form-label">Buyer sample received by LBE Lab (AA$)</label>

            <input type="datetime-local" name="dt_sample_received" class="form-control" value="<?= set_value('dt_sample_received', isset($data['dt_sample_received'])?date('Y-m-d\TH:i',strtotime($data['dt_sample_received'])):'') ?>">

          </div>

            <div class="col-6 col-md-4">

            <label class="form-label">Sample Request (AK$)</label>

            <input type="date" name="dt_sample_request" class="form-control" value="<?= set_value('dt_sample_request',$data['dt_sample_request']??'') ?>">

          </div>

          <div class="col-6 col-md-4">

            <label class="form-label">2nd Sample Received (AL$)</label>

            <input type="date" name="dt_sample_received2" class="form-control" value="<?= set_value('dt_sample_received2',$data['dt_sample_received2']??'') ?>">

          </div>

          </div>

          </div>

          <div class="tab-pane fade" id="actual_received" role="tabpanel" >

            <div class="row g-3">

             <div class="col-12 col-md-4">

            <label class="form-label">Actual Arriving at LBE (N$)</label>

            <input type="datetime-local" name="actual_date" class="form-control" value="<?= set_value('actual_date', isset($data['actual_date'])?date('Y-m-d\TH:i',strtotime($data['actual_date'])):'') ?>">

          </div>

            </div>

          </div>

          <div class="tab-pane fade" id="commence_unloading" role="tabpanel">

            <div class="row g-3">

            <div class="col-6 col-md-6">

              <label class="form-label">Commence Discharge (P$)</label>

                          <input type="datetime-local" name="commence_disch" class="form-control" value="<?= set_value('commence_disch', isset($data['commence_disch'])?date('Y-m-d\TH:i',strtotime($data['commence_disch'])):'') ?>">



            </div>

            </div>

          </div>

          <div class="tab-pane fade" id="unloading_complete" role="tabpanel">

            <div class="row g-3">

            <div class="col-6 col-md-6">

              <label class="form-label">Complete Discharge (Q$)</label>

            <input type="datetime-local" name="complete_disc" class="form-control" value="<?= set_value('complete_disc', isset($data['complete_disc'])?date('Y-m-d\TH:i',strtotime($data['complete_disc'])):'') ?>">



             

            </div>

            </div> 

          </div>

          <!--<div class="tab-pane fade" id="cast_off" role="tabpanel"></div>-->

          <div class="tab-pane fade" id="coa_doc" role="tabpanel">

            <div class="row g-3">

              <div class="col-6 col-md-4">

            <label class="form-label">BA Bongkar Muat (W$)</label>

            <input type="datetime-local" name="dt_ba_bm" class="form-control" value="<?= set_value('dt_ba_bm', isset($data['dt_ba_bm'])?date('Y-m-d\TH:i',strtotime($data['dt_ba_bm'])):'') ?>">

          </div>

              </div>

            </div>

            <div class="tab-pane fade" id="invoice_doc" role="tabpanel">

              <div class="row g-3">

             

               <div class="col-6 col-md-4">

            <label class="form-label">Inv Softcopy (AC$)</label>

            <input type="datetime-local" name="dt_inv_delivery_soft" class="form-control" value="<?= set_value('dt_inv_delivery_soft', isset($data['dt_inv_delivery_soft'])?date('Y-m-d\TH:i',strtotime($data['dt_inv_delivery_soft'])):'') ?>">

          </div>

          <div class="col-6 col-md-4">

            <label class="form-label">Inv Hardcopy (AD$)</label>

            <input type="datetime-local" name="dt_inv_delivery_hard" class="form-control" value="<?= set_value('dt_inv_delivery_hard', isset($data['dt_inv_delivery_hard'])?date('Y-m-d\TH:i',strtotime($data['dt_inv_delivery_hard'])):'') ?>">

          </div>

          <div class="col-6 col-md-4">

            <label class="form-label">Inv Received (AE$)</label>

            <input type="datetime-local" name="dt_inv_received" class="form-control" value="<?= set_value('dt_inv_received', isset($data['dt_inv_received'])?date('Y-m-d\TH:i',strtotime($data['dt_inv_received'])):'') ?>">

          </div>

          <div class="col-6 col-md-4">

            <label class="form-label">Payment (AF$)</label>

            <input type="date" name="dt_payment" class="form-control" value="<?= set_value('dt_payment',$data['dt_payment']??'') ?>">

          </div>

              </div>

          </div>

          <div class="tab-pane fade" id="others" role="tabpanel">

            <div class="row g-3">

              <div class="col-6 col-md-4">

            <label class="form-label">Volume Plan (L$)</label>

            <input type="number" step="0.01" name="volume_plan_mt" class="form-control" value="<?= set_value('volume_plan_mt',$data['volume_plan_mt']??'') ?>">

          </div>

          <div class="col-6 col-md-4">

            <label class="form-label">Volume Actual (U$)</label>

            <input type="number" step="0.01" name="volume_actual_mt" class="form-control" value="<?= set_value('volume_actual_mt',$data['volume_actual_mt']??'') ?>">

          </div>

               <div class="col-6 col-md-4">

                <label class="form-label">COA Received (Y$)</label>

                <input type="datetime-local" name="dt_coa_received" class="form-control" value="<?= set_value('dt_coa_received', isset($data['dt_coa_received'])?date('Y-m-d\TH:i',strtotime($data['dt_coa_received'])):'') ?>">

              </div>

              <?php /*

               <div class="col-6 col-md-4">

                <label class="form-label">Load AWB buyer sample (Z$)</label>

                <input type="datetime-local" name="dt_load_sample" class="form-control" value="<?= set_value('dt_load_sample', isset($data['dt_load_sample'])?date('Y-m-d\TH:i',strtotime($data['dt_load_sample'])):'') ?>">

             </div>      

             */?>

                <div class="col-6 col-md-4">

                    <label class="form-label">Coal Supplier Status (AG$)</label>

                    <input name="status_coal_supplier" class="form-control" value="<?= set_value('status_coal_supplier',$data['status_coal_supplier']??'') ?>">

                  </div>



    

        
    

                <div class="col-6 col-md-4">

                    <label class="form-label">Discharging Port Date (AI$)</label>

                    <input type="date" name="dt_disch_port" class="form-control" value="<?= set_value('dt_disch_port',$data['dt_disch_port']??'') ?>">

                  </div>

                     <div class="col-12">

                    <label class="form-label">Remarks (AJ$)</label>

                    <textarea name="remarks_aj" rows="3" class="form-control"><?= set_value('remarks_aj',$data['remarks_aj']??'') ?></textarea>

                  </div>

            </div>

          

          </div>

          

        </div>

        <!-- Tabs and pill end -->

          

        <div class="row g-3">

         



          

         

          

          



         

      

         



       



          



          

        </div>

      </div>

    </div>

    <?php endif; ?>

  </div>



  <!-- Aside: Actions -->

  <div class="col-12 col-lg-4">

    <div class="card shadow-sm">

      <div class="card-header bg-white"><h6 class="m-0">Aksi</h6></div>

      <div class="card-body d-grid gap-2">

        <button class="btn btn-primary"><i class="bi bi-save me-1"></i> Simpan</button>

        <a class="btn btn-outline-secondary" href="<?= site_url('shipments') ?>">Batal</a>

      </div>

    </div>



    <div class="card shadow-sm mt-3">

      <div class="card-header bg-white"><h6 class="m-0">Info</h6></div>

      <div class="card-body small text-muted">

        <ul class="mb-0">

          <li>Field ditampilkan sesuai role.</li>

          <li>Fitur Upload Dokumen ada di Detail</li>

          <li>Kolom hitung (K,R,S,T,V,AB) muncul di report dari VIEW.</li>

        </ul>

      </div>

    </div>

  </div>

</form>



<?php $this->load->view('layout/footer'); ?>


