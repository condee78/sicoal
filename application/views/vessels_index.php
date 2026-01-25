<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Master Vessel</title>
  <style>
    body{font-family:Arial; padding:16px;}
    table{border-collapse:collapse; width:100%;}
    th,td{border:1px solid #ddd; padding:8px; font-size:13px;}
    input{padding:6px; width:100%;}
    .row{display:flex; gap:8px;}
    .col{flex:1;}
  </style>
</head>
<body>

<h2>Master Vessel</h2>

<form method="get">
  <div class="row">
    <div class="col"><input name="q" value="<?=htmlspecialchars($q)?>" placeholder="Cari nama kapal..."></div>
    <div><button type="submit">Search</button></div>
  </div>
</form>

<hr>
<hr>
<h3>API Search (IMO / MMSI)</h3>
<div class="row" style="margin-bottom:10px;">
  <div class="col-md-8">
    <input id="apiQ" class="form-control" placeholder="Masukkan IMO (7 digit) atau MMSI (9 digit)">
  </div>
  <div class="col-md-4">
    <button class="btn btn-primary" id="btnApiSearch">Cari via API</button>
  </div>
</div>

<pre id="apiOut" style="display:none; max-height:260px; overflow:auto;"></pre>


<h3>Tambah / Update Vessel</h3>
<form method="post" action="<?=site_url('admin_vessels/save')?>">
  <input type="hidden" name="id" value="">
  <div class="row">
    <div class="col"><input name="name" placeholder="Nama kapal (mis: TB MAKMUR JAYA)" required></div>
    <div class="col"><input name="mmsi" placeholder="MMSI (disarankan)"></div>
    <div class="col"><input name="imo" placeholder="IMO (opsional)"></div>
  </div>
  <div class="row" style="margin-top:8px;">
    <div class="col"><input name="vendor_kode" placeholder="Vendor/Rekanan kode"></div>
    <div class="col"><input name="vessel_type" placeholder="Type (TUG/BARGE/CARGO)"></div>
    <div class="col"><input name="flag" placeholder="Flag"></div>
     <div class="col"><input name="callsign" placeholder="CallSign"></div>
  </div>
  
  <div style="margin-top:8px;">
    <input name="notes" placeholder="Catatan (opsional)">
  </div>
  <div style="margin-top:8px;">
    <label><input type="checkbox" name="is_active" checked> Active</label>
  </div>
  <div style="margin-top:8px;">
    <button type="submit">Save</button>
  </div>
</form>

<hr>

<h3>Daftar Vessel</h3>
<table>
  <thead>
    <tr>
      <th>ID</th>
      <th>Vendor</th>
      <th>Nama</th>
      <th>MMSI</th>
      <th>IMO</th>
       <th>Call Sign</th>
      <th>Type</th>
      <th>Flag</th>
      <th>Active</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach($rows as $r): ?>
      <tr>
        <td><?=$r['id']?></td>
        <td><?=htmlspecialchars($r['vendor_kode'])?></td>
        <td><?=htmlspecialchars($r['name'])?></td>
        <td><?=htmlspecialchars($r['mmsi'])?></td>
        <td><?=htmlspecialchars($r['imo'])?></td>
         <td><?=htmlspecialchars($r['callsign'])?></td>
        <td><?=htmlspecialchars($r['vessel_type'])?></td>
        <td><?=htmlspecialchars($r['flag'])?></td>
        <td><?=$r['is_active']?'Y':'N'?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script>
$('#btnApiSearch').on('click', function(e){
  e.preventDefault();
  var q = $('#apiQ').val().trim();
  if(!q) return alert('Isi IMO atau MMSI');

  $('#apiOut').show().text('Loading...');
  $.getJSON('<?=site_url('admin_vessels/api_search')?>', {q:q}, function(res){
    $('#apiOut').text(JSON.stringify(res, null, 2));

    // Auto-fill form tambah vessel kalau API result ada
    try{
      if(res && res.api && res.api.ok){
        var data = res.api.data;
        var item = Array.isArray(data) ? data[0] : (data && data[0]);
        // beberapa response bisa array langsung; kita ambil item pertama
        if(!item && Array.isArray(data)) item = data[0];
        if(item && item.AIS){
          $('input[name="name"]').val(item.AIS.NAME || '');
          $('input[name="mmsi"]').val(item.AIS.MMSI || '');
           $('input[name="callsign"]').val(item.AIS.CALLSIGN || '');
          $('input[name="imo"]').val(item.AIS.IMO || '');
          $('input[name="vessel_type"]').val((item.MASTERDATA && item.MASTERDATA.TYPE) ? item.MASTERDATA.TYPE : '');
          $('input[name="flag"]').val((item.MASTERDATA && item.MASTERDATA.FLAG) ? item.MASTERDATA.FLAG : '');
        }
      }
    }catch(err){}
  }).fail(function(){
    $('#apiOut').text('Error call api_search');
  });
});
</script>

</body>
</html>


