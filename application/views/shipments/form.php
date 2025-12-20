
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
       <?php 
       if(isset($data['id'])){
        $isLoadingPort = isset($data['actual_arrival_load_port']) && $data['actual_arrival_load_port']!='0000-00-00 00:00:00' ? true : false;
        $isCommenceLoading = isset($data['commence_loading']) && $data['actual_arrival_load_port']!='0000-00-00 00:00:00' && isset($data['complete_loading']) && $data['complete_loading']!='0000-00-00 00:00:00' ? true : false;
        $isDeparture = isset($data['actual_departure']) && $data['actual_departure']!='0000-00-00 00:00:00' ? true : false;
        $isSampleActualReceived = isset($data['actual_date']) && $data['actual_date']!='0000-00-00 00:00:00' ? true : false;
        $isUnloading = isset($data['complete_disch']) && $data['complete_disch']!='0000-00-00 00:00:00' ? true : false;
        $isDocuments = isset($data['dt_coa_received']) && $data['dt_coa_received']!='0000-00-00 00:00:00' ? true : false;
        $isCompletedShipment = isset($data['dt_ba_bm']) && $data['dt_ba_bm']!='0000-00-00 00:00:00' ? true : false;
        $isInvoicing = isset($data['dt_inv_received']) && $data['dt_inv_received']!='0000-00-00 00:00:00' ? true : false;
        $isCompletedInvoicing = isset($data['dt_payment']) && $data['dt_payment']!='0000-00-00' ? true : false;
      ?>

	    <div class="row justify-content-center">
	    	<div class="col-11">
		    	<div class="progressbar">
		        <div class="progress" id="progress"></div>
		   <!--  //progress-step-active progress-step-completed -->
		        <div class="progress-step  <?php if($isLoadingPort) {echo "progress-step-completed";} else { echo "progress-step-active";} ?>" data-title="Loading Port"></div>
		        <div class="progress-step <?php if($isCommenceLoading && $isLoadingPort) {echo "progress-step-completed";} else if (!$isCommenceLoading && $isLoadingPort) {echo "progress-step-active";} ?>" data-title="Commence & Loading"></div>
		        <div class="progress-step <?php if($isDeparture) {echo "progress-step-completed";} else if (!$isDeparture && $isCommenceLoading) {echo "progress-step-active";} ?>"  data-title="Departure"></div>
		        <div class="progress-step <?php if($isSampleActualReceived) {echo "progress-step-completed";} else if (!$isSampleActualReceived && $isDeparture) {echo "progress-step-active";} ?>"  data-title="Sample & Actual Received"></div>
		        <div class="progress-step <?php if($isUnloading) {echo "progress-step-completed";} else if (!$isUnloading && $isSampleActualReceived) {echo "progress-step-active";} ?>" data-title="Unloading"></div>
		        <div class="progress-step <?php if($isDocuments) {echo "progress-step-completed";} else if (!$isDocuments && $isUnloading) {echo "progress-step-active";} ?>" data-title="Documents"></div>
		        <div class="progress-step <?php if($isCompletedShipment) {echo "progress-step-completed";} else if (!$isCompletedShipment && $isDocuments) {echo "progress-step-active";} ?>" data-title="Completed Shipment"></div>
		        <div class="progress-step <?php if($isInvoicing) {echo "progress-step-completed";} else if (!$isInvoicing && $isCompletedShipment) {echo "progress-step-active";} ?>" data-title="Invoicing"></div>
		        <div class="progress-step <?php if($isCompletedInvoicing) {echo "progress-step-completed";} else if (!$isCompletedInvoicing && $isInvoicing) {echo "progress-step-active";} ?>" data-title="Completed Invoicing"></div>
		      </div>
	      </div>
	    </div>
      <?php } ?>

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


 <div  class="row g-3">   

  <div class="col-12 col-lg-8">
<form method="post" <?php if($data['period_year']!=$this->Period_model->active_year()) echo 'onsubmit="alert(\'Maaf tidak bisa Edit. Saat ini Periode Aktif '.$this->Period_model->active_year().' bukan '.$data['period_year'].'\'); return false"'?>)

      action="<?= site_url('shipments/save'.($mode==='edit'?'/'.$data['id']:'')) ?>"

      enctype="multipart/form-data"

     >


    <div class="card shadow-sm">

      <div class="card-header bg-white" id="cardHeader">

        <h6 class="m-0">
          <a class="d-block d-flex justify-content-between align-items-center text-secondary fw-semibold" style="text-decoration:none;" data-bs-toggle="collapse" href="#collapseMain">
    Informasi Utama PERIODE <?=$data['period_year']?> <i class="bi bi-arrow-down-circle-fill"></i>
          </a>
        </h6>
            

      </div>

      <div class="collapse show" id="collapseMain">
      <div class="card-body">
        <div class="row g-3 mb-3">
          <small class="text-primary fw-bold mb-0">LBE Section</small>
          <div class="col-12 col-md-3">

            <label class="form-label">Shipment No (C$)</label>

            <?php if ($isStaff || $isAdmin){ ?>
            <input id="field_shipment_no" name="shipment_no" class="form-control" value="<?= set_value('shipment_no',$data['shipment_no']??'') ?>" required>
            
            <?php }else{?>
            
            <input disabled id="" name="" class="form-control" value="<?= set_value('shipment_no',$data['shipment_no']??'') ?>" >
            <input type='hidden' id="field_shipment_no" name="shipment_no" class="form-control" value="<?= set_value('shipment_no',$data['shipment_no']??'') ?>" required>
            
            <?php } ?>

          </div>
          <div class="col-12 col-md-3">

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

          <div class="col-6 col-md-3">

            <label class="form-label">Volume Plan (L$)</label>

            <?php if ($isStaff || $isAdmin){ ?>
            
            <input type="number" step="0.01" name="volume_plan_mt" class="form-control" value="<?= set_value('volume_plan_mt',$data['volume_plan_mt']??'') ?>">
            
            <?php }else{?>
            
            <input disabled type="number" step="0.01" name="volume_plan_mt" class="form-control" value="<?= set_value('volume_plan_mt',$data['volume_plan_mt']??'') ?>">
            
            <?php } ?>

          </div>

          <?php if ($isStaff || $isAdmin){ ?>
          <div class="col-6 col-md-3">

            <label class="form-label">ETA at LBE (M$)</label>

            <input type="date" name="eta_lbe" class="form-control" value="<?= set_value('eta_lbe',$data['eta_lbe']??'') ?>">

          </div>
          

       <!-- <div class="col-6 col-md-4">

            <label class="form-label">Shipment Status (AH$)</label>

            <select name="shipment_status" class="form-select">

              <?php foreach(['On process','Completed','Not Completed'] as $st): ?>

                <option <?= set_select('shipment_status',$st, ($data['shipment_status']??'')==$st) ?>><?= $st ?></option>

              <?php endforeach;?>

            </select>

          </div>-->
          <?php }else{?>
            <div class="col-6 col-md-3">

            <label class="form-label">ETA at LBE (M$)</label>

            <input disabled type="date" name="eta_lbe" class="form-control" value="<?= set_value('eta_lbe',$data['eta_lbe']??'') ?>">

          </div>
          
         <?php } ?>
        
      </div>
    
      <div class="row g-3">
        <small class="text-primary fw-bold mb-0">Vendor Section</small>
          <div class="col-12 col-md-3">

            <label class="form-label">MMSI / IMO</label>

            <input name="mmsi" class="form-control" value="<?= set_value('mmsi',$data['mmsi']??'') ?>" >

          </div>
          <div class="col-12 col-md-3">

            <label class="form-label">Nominated Vessel (D$)</label>

            <input name="nominated_vessel" class="form-control" value="<?= set_value('nominated_vessel',$data['nominated_vessel']??'') ?>">

          </div>

           <div class="col-6 col-md-3">

            <label class="form-label">Volume Actual (U$)</label>

            <input type="number" step="0.01" name="volume_actual_mt" class="form-control" value="<?= set_value('volume_actual_mt',$data['volume_actual_mt']??'') ?>">

          </div>

          <div class="col-6 col-md-3">

            <label class="form-label">ETA Loading Port (F$)</label>

            <input type="date" name="eta_loading_port" class="form-control" value="<?= set_value('eta_loading_port',$data['eta_loading_port']??'') ?>">

          </div>
      
    

          <div class="col-6 col-md-3">

            <label class="form-label">Loading Port (E$)</label>

            <input name="loading_port" class="form-control" value="<?= set_value('loading_port',$data['loading_port']??'') ?>">

          </div>

          
          <div class="card=footer text-end">
            <!-- Button trigger modal -->
            <?php if ($isStaff || $isAdmin){ ?>
            <a class="btn btn-info btn-sm text-light" data-bs-toggle="modal" data-bs-target="#modalShipmentVendor">
              Lihat Data Shipment Vendor
            </a>

            <?php } else { ?>
              <a class="btn btn-info btn-sm text-light" data-bs-toggle="modal" data-bs-target="#modalShipmentLBE">
              Lihat Data Shipment LBE
            </a>

            <?php } ?>
          
          </div>

        </div>
      </div>

          </div>
    </div>



    <!-- VENDOR SECTION -->

    <?php if ($isVendor): ?>

    <div class="card shadow-sm mt-3">

      <div class="card-header bg-white">
        <h6 class="m-0">
          <a class="d-block d-flex justify-content-between align-items-center text-secondary fw-semibold" style="text-decoration:none;" data-bs-toggle="collapse" href="#collapseVendor">
    Input Vendor/Shipper (G$,H$,I$,J$,M$,X$,Z$ input) 
            <i class="bi bi-arrow-down-circle-fill"></i>
          </a>
        </h6>
      </div>

      <div class="collapse show" id="collapseVendor">
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

    </div>

    <?php endif; ?>

    <!-- STAFF/ADMIN SECTION -->

    <?php if ($isStaff || $isAdmin): ?>

    <div class="card shadow-sm mt-3">

      <div class="card-header bg-white">
        
        <h6 class="m-0">
          <a class="d-block d-flex justify-content-between align-items-center text-secondary fw-semibold" style="text-decoration:none;" data-bs-toggle="collapse" href="#collapseLBE">
    Input Staff/Admin (U$,W$,Y$,Z$,AA$, AC..AL, AG, AH) <i class="bi bi-arrow-down-circle-fill"></i>
          </a>

        </h6>
      </div>

      <div class="collapse show" id="collapseLBE">
      <div class="card-body">

           <!-- Tabs and pill -->

        <!-- Nav Tabs -->

        <ul class="nav nav-tabs nav-justified" id="myTab" role="tablist">

          <li class="nav-item" role="presentation">

            <button class="nav-link active" id="sample_received-tab" data-bs-toggle="tab"

                    data-bs-target="#sample_received" type="button" role="tab"> <i class="bi bi-1-circle"></i> Sample Request/Received</button>

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

            <input type="datetime-local" name="complete_disch" class="form-control" value="<?= set_value('complete_disch', isset($data['complete_disch'])?date('Y-m-d\TH:i',strtotime($data['complete_disch'])):'') ?>">



             

            </div>

            </div> 

          </div>

          <!--<div class="tab-pane fade" id="cast_off" role="tabpanel"></div>-->

          <div class="tab-pane fade" id="coa_doc" role="tabpanel">

            <div class="row g-3">
                
                <div class="col-6 col-md-6">

                <label class="form-label">COA Received (Y$)</label>

                <input type="datetime-local" name="dt_coa_received" class="form-control" value="<?= set_value('dt_coa_received', isset($data['dt_coa_received'])?date('Y-m-d\TH:i',strtotime($data['dt_coa_received'])):'') ?>">

              </div>
              
              

              <div class="col-6 col-md-6">

            <label class="form-label">BA Bongkar Muat (W$)</label>

            <input type="datetime-local" name="dt_ba_bm" class="form-control" value="<?= set_value('dt_ba_bm', isset($data['dt_ba_bm'])?date('Y-m-d\TH:i',strtotime($data['dt_ba_bm'])):'') ?>">

          </div>
          
                <div class="col-3 col-md-3">

                    <label class="form-label">Quality Calori</label>

                    <input name="status_coal_supplier" class="form-control" value="<?= set_value('quolity_cal',$data['quolity_cal']??'') ?>">

                  </div>
                   <div class="col-3 col-md-3">

                    <label class="form-label">Quality Sulfur</label>

                    <input name="status_coal_supplier" class="form-control" value="<?= set_value('quolity_sul',$data['quolity_sul']??'') ?>">

                  </div>
                   <div class="col-3 col-md-3">

                    <label class="form-label">Quality Ash</label>

                    <input name="status_coal_supplier" class="form-control" value="<?= set_value('quolity_ash',$data['quolity_ash']??'') ?>">

                  </div>
                   <div class="col-3 col-md-3">

                    <label class="form-label">Quality Moistore</label>

                    <input name="status_coal_supplier" class="form-control" value="<?= set_value('quolity_moi',$data['quolity_moi']??'') ?>">

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

    </div>

    <?php endif; ?>

    <!-- Modal -->
    <div class="modal fade" id="modalShipmentVendor" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title fs-5" id="exampleModalLabel">Data Shipment Vendor</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
                <div class="row row-cols-1 row-cols-md-2 g-3">

          <?php

            $timeline = [

              ['label'=>'Arrival Load Port (G$)','val'=>$data['actual_arrival_load_port']],

              ['label'=>'Commence Loading (H$)','val'=>$data['commence_loading']],

              ['label'=>'Complete Loading (I$)','val'=>$data['complete_loading']],

              ['label'=>'Actual Departure (J$)','val'=>$data['actual_departure']],

              ['label'=>'Load AWB buyer sample (Z$)','val'=>$data['dt_load_sample']],

              ['label'=>'COA Delivery (X$)','val'=>$data['dt_coa_delivery']],

            ];

          ?>

          <?php foreach($timeline as $t): ?>

          <div class="col">

            <div class="border-start ps-3">

              <div class="small text-muted"><?= $t['label'] ?>
              </div>

              <div class="fw-semibold">

                <?= $t['val'] ? tgl_time($t['val']) : '-' ?>

              </div>

            </div>

          </div>

          <?php endforeach; ?>
          </div>

          </div>
          
        </div>
      </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="modalShipmentLBE" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title fs-5" id="exampleModalLabel">Data Shipment LBE</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
                <div class="row row-cols-1 row-cols-md-2 g-3">

          <?php

            $timeline = [
              ['label'=>'Actual Departure (J$)','val'=>$data['actual_departure']],

              ['label'=>'COA Received (Y$)','val'=>$data['dt_coa_received']],

              ['label'=>'Load Sample (Z$)','val'=>$data['dt_load_sample']],

              ['label'=>'Sample Received (AA$)','val'=>$data['dt_sample_received']],

              ['label'=>'BA Bongkar Muat (W$)','val'=>$data['dt_ba_bm']],

              ['label'=>'Inv Softcopy (AC$)','val'=>$data['dt_inv_delivery_soft']],

              ['label'=>'Inv Hardcopy (AD$)','val'=>$data['dt_inv_delivery_hard']],

              ['label'=>'Invoice Received (AE$)','val'=>$data['dt_inv_received']],

              ['label'=>'Payment (AF$)','val'=>$data['dt_payment']],

              ['label'=>'Disch Port Date (AI$)','val'=>$data['dt_disch_port']],

              ['label'=>'Sample Request (AK$)','val'=>$data['dt_sample_request']],

              ['label'=>'2nd Sample Received (AL$)','val'=>$data['dt_sample_received2']],

            ];

          ?>

          <?php foreach($timeline as $t): ?>

          <div class="col">

            <div class="border-start ps-3">

              <div class="small text-muted"><?= $t['label'] ?>
              </div>

              <div class="fw-semibold">

                <?= $t['val'] ? tgl_time($t['val']) : '-' ?>

              </div>

            </div>

          </div>

          <?php endforeach; ?>
          </div>

          </div>
          
        </div>
      </div>
    </div>

  

    <div class="card shadow-sm mt-3">

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
</form>
  </div>



  <!-- Files -->
  <?php if(isset($data['id'])){?>
  <div class="col-12 col-lg-4">
    <div class="card shadow-sm">
      <div class="card-header bg-white d-flex align-items-center justify-content-between">
        <h6 class="m-0">Dokumen</h6>
      </div>
      <div class="card-body">
        <?php if($this->session->flashdata('ok')): ?>
          <div class="alert alert-success py-2"><?= $this->session->flashdata('ok') ?></div>
        <?php endif; ?>
        <?php if($this->session->flashdata('err')): ?>
          <div class="alert alert-danger py-2"><?= $this->session->flashdata('err') ?></div>
        <?php endif; ?>

        <!-- Upload form -->
        <form method="post" enctype="multipart/form-data" action="<?= site_url('files/upload/'.$data['id']) ?>" class="row g-2">
          <div class="col-12">
            <label class="form-label small">Jenis Dokumen</label>
            <select name="doc_type" class="form-select" required>
              <?php if (is_vendor()): ?>
                <option value="COA">COA</option>
                <option value="AWB">AWB</option>
                <option value="OTHER_VENDOR">Other (Vendor)</option>
              <?php else: ?>
                <option value="BA_BM">BA Bongkar Muat</option>
                <option value="LAB_RESULT">Lab Result</option>
                <option value="INVOICE_SC">Invoice Softcopy</option>
                <option value="INVOICE_HC">Invoice Hardcopy</option>
                <option value="AH_PROOF">Proof AH</option>
                <option value="COA">COA</option>
                <option value="AWB">AWB</option>
                <option value="OTHER_VENDOR">Other (Vendor)</option>
              <?php endif; ?>
            </select>
          </div>
          <div class="col-12">
            <input type="file" name="file" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx,.csv" required>
            <div class="form-text">Maks 10 MB</div>
          </div>
          <div class="col-12 d-grid">
            <button class="btn btn-primary btn-sm"><i class="bi bi-upload me-1"></i> Upload</button>
          </div>
        </form>

        <hr>

        <!-- List files -->
        <?php
          $files = $this->File_model->by_shipment($data['id']);
          if (!$files) echo '<div class="text-muted small">Belum ada dokumen.</div>';
        ?>
        <?php foreach($files as $f): ?>
          <div class="d-flex align-items-center justify-content-between border rounded-3 p-2 mb-2">
            <div class="me-2 small">
              <div class="fw-semibold"><?= htmlentities($f['doc_type']) ?></div>
              <div class="text-muted"><?= htmlentities($f['original_name']) ?></div>
              <div class="text-muted"><?= date('Y-m-d H:i', strtotime($f['uploaded_at'])) ?></div>
            </div>
            <div class="btn-group btn-group-sm">
              <a class="btn btn-outline-secondary" href="<?= site_url('files/download/'.$f['id']) ?>"><i class="bi bi-download"></i></a>
              <?php if (is_admin_lbe() || is_superadmin() || is_staff_lbe()): ?>
                <a class="btn btn-outline-danger" href="<?= site_url('files/delete/'.$f['id']) ?>" onclick="return confirm('Hapus file ini?')"><i class="bi bi-trash"></i></a>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <?php }?>
</div>


<?php $this->load->view('layout/footer'); ?>


