<div class="pagetitle">
	<h1>Berkas-berkas</h1>
	<nav>
        <ol class="breadcrumb">
			<li class="breadcrumb-item"><a href="<?=base_url();?>">Home</a></li>
			<li class="breadcrumb-item active">Berkas-berkas</li>
		</ol>
	</nav>
</div><!-- End Page Title -->

<section class="section">
	<div class="row">
		<div class="col-md-6">
			<form action="<?php echo site_url('user_guide/save_files'); ?>" method='post' id='form_files'>
				
				<div class="card">
					<div class="card-body table-responsive">
						<h5 class="card-title">Edit nama berkas</h5>
						
						<table class="table table-bordered" width="100%">
							<thead>
								<tr>
									<th colspan="2">Nama File <small>(bisa diubah)</small></th>
									<th>Ukuran</th>
									<th>Ukuran</th>
									<th>Tgl. Upload</th>
									<th class="text-center"><i class="bi bi-trash"></i></th>
								</tr>
							</thead>
							
							<tbody>
								<?php
									foreach ($rows->result() as $row) {
										$id_file = $row->id_file;
										$f = $this->encryption->encrypt($id_file);
										$en_id = str_replace('+','~',$f);
									?>
									<tr>
										<td>
											<?php echo form_input("title[${id_file}]", $row->title, "class='form-control'"); ?>
										</td>
										<td>
											<a target="_blank" class="btn btn-sm btn-light" href="<?=site_url('keluarga/unduh_file/?en_id='.$en_id)?>">
												<i class="bi bi-download"></i></a>
										</td>
										<td>
											<?= $row->file_size; ?>
										</td>
										<td>
											<?= $row->upload_date; ?>
										</td>
										<td>
											<a class="btn btn-sm btn-light" href="<?=site_url('keluarga/hapus_file/?en_id='.$en_id)?>">
												<i class="bi bi-trash"></i></a>
										</td>
									</tr>
								<?php } ?>
							</tbody>
							
						</table>
						<button type="submit" class="btn btn-sm btn-primary">
						<i class="bi bi-save"></i> Simpan</button>
						
					</div>
					
				</div>
				
			</form>
			
		</div>
		
		<div class="col-md-6">
			
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Upload berkas</h5>
					
					<form  action="<?php echo site_url('keluarga/upload_files'); ?>" method='post' enctype="multipart/form-data">
						<div class="row g-3">
							<div class="col-md-12">
								<label for="formFile" class="form-label">File Upload</label>
								<input class="form-control" type="file" id="formFile" name="formFile[]">
							</div>
							<div class="col-md-12" style="text-align:right">
								<input type="submit" value="Upload" name="submit" class="btn btn-primary"/>
							</div>
						</div>
					</form>
				</div>
			</div>
			
		</div>
		
	</div>
	
</section>

<script lang="javascript">
	$(document).ready(function() {
	
	});
</script>
