<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Vessel Detail - <?=htmlspecialchars($vessel['name'])?></title>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

  <style>
    body { padding: 15px; }
    #map { width: 100%; height: 520px; border-radius: 8px; border: 1px solid #ddd; }
    .kv { margin: 0; }
    .kv dt { width: 140px; }
    .kv dd { margin-left: 160px; }
    .badge-soft { background:#f5f5f5; color:#333; border:1px solid #ddd; }
    .muted { color:#777; }
    .mt8 { margin-top:8px; }
  </style>
</head>
<body>

<div class="container-fluid">
  <div class="row">
    <div class="col-md-8">
      <h3 style="margin-top:0;">
        <?=htmlspecialchars($vessel['name'])?>
        <?php if(!empty($vessel['mmsi'])): ?>
          <span class="badge badge-soft">MMSI: <?=htmlspecialchars($vessel['mmsi'])?></span>
        <?php endif; ?>
        <?php if(!empty($vessel['imo'])): ?>
          <span class="badge badge-soft">IMO: <?=htmlspecialchars($vessel['imo'])?></span>
        <?php endif; ?>
      </h3>

      <div id="map"></div>

      <p class="muted mt8">
        Auto refresh: <span id="refreshStatus">ON</span> •
        Last update (server): <span id="lastFetched">-</span>
      </p>
    </div>

    <div class="col-md-4">
      <div class="panel panel-default">
        <div class="panel-heading"><b>Informasi Vessel</b></div>
        <div class="panel-body">
          <dl class="kv">
            <dt>Nama</dt><dd id="vName"><?=htmlspecialchars($vessel['name'])?></dd>
            <dt>Vendor</dt><dd id="vVendor"><?=htmlspecialchars($vessel['vendor_kode'])?></dd>
            <dt>Type</dt><dd id="vType"><?=htmlspecialchars($vessel['vessel_type'])?></dd>
            <dt>Flag</dt><dd id="vFlag"><?=htmlspecialchars($vessel['flag'])?></dd>
          </dl>
          <hr style="margin:10px 0;">
          <dl class="kv">
            <dt>Latitude</dt><dd id="vLat"><?=htmlspecialchars($vessel['lat'])?></dd>
            <dt>Longitude</dt><dd id="vLon"><?=htmlspecialchars($vessel['lon'])?></dd>
            <dt>Speed (SOG)</dt><dd id="vSog"><?=htmlspecialchars($vessel['sog'])?></dd>
            <dt>Course (COG)</dt><dd id="vCog"><?=htmlspecialchars($vessel['cog'])?></dd>
            <dt>Heading</dt><dd id="vHeading"><?=htmlspecialchars($vessel['heading'])?></dd>
            <dt>Status</dt><dd id="vNav"><?=htmlspecialchars($vessel['nav_status'])?></dd>
            <dt>Destination</dt><dd id="vDest"><?=htmlspecialchars($vessel['destination'])?></dd>
            <dt>ETA</dt><dd id="vEta"><?=htmlspecialchars($vessel['eta'])?></dd>
            <dt>Pos Time</dt><dd id="vPosTime"><?=htmlspecialchars($vessel['pos_time'])?></dd>
          </dl>

          <hr style="margin:10px 0;">
          <button class="btn btn-default btn-sm" id="btnCenter">Center ke Vessel</button>
          <button class="btn btn-info btn-sm" id="btnTrack">Tampilkan Track 12 Jam</button>
          <button class="btn btn-warning btn-sm" id="btnToggle">Toggle Refresh</button>
        </div>
      </div>

      <div class="alert alert-info">
        Tips: kalau posisi tidak berubah, cek apakah AIS update terakhir lama atau kapal sedang moored/anchored.
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
(function(){
  var vesselId = <?= (int)$vessel['id']; ?>;

  // default center Indonesia
  var map = L.map('map').setView([-2.5, 118.0], 5);

  // OSM tiles
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; OpenStreetMap'
  }).addTo(map);

  var marker = null;
  var trackLine = null;
  var autoRefresh = true;
  var refreshMs = 30000; // 30 detik

  function setText(id, val){
    document.getElementById(id).textContent = (val === null || val === undefined || val === "") ? "-" : val;
  }

  function updateUI(d){
    setText('vName', d.name);
    setText('vVendor', d.vendor_kode);
    setText('vType', d.vessel_type);
    setText('vFlag', d.flag);

    setText('vLat', d.lat);
    setText('vLon', d.lon);
    setText('vSog', d.sog);
    setText('vCog', d.cog);
    setText('vHeading', d.heading);
    setText('vNav', d.nav_status);
    setText('vDest', d.destination);
    setText('vEta', d.eta);
    setText('vPosTime', d.pos_time);
    setText('lastFetched', d.fetched_at);

    // update marker
    if (d.lat && d.lon) {
      var lat = parseFloat(d.lat), lon = parseFloat(d.lon);

      var popupHtml =
        '<b>' + (d.name || '-') + '</b><br>' +
        'SOG: ' + (d.sog || '-') + ' kn<br>' +
        'COG: ' + (d.cog || '-') + '<br>' +
        'Status: ' + (d.nav_status || '-') + '<br>' +
        'Dest: ' + (d.destination || '-') + '<br>' +
        'PosTime: ' + (d.pos_time || '-') + '<br>';

      if (!marker) {
        marker = L.marker([lat, lon]).addTo(map).bindPopup(popupHtml);
        map.setView([lat, lon], 10);
      } else {
        marker.setLatLng([lat, lon]).setPopupContent(popupHtml);
      }
    }
  }

  function fetchDetail(){
    $.getJSON('<?= site_url('vessels/api_detail'); ?>/' + vesselId, function(res){
      if (!res || !res.ok || !res.data) return;
      updateUI(res.data);
    });
  }

  function fetchTrack(){
    $.getJSON('<?= site_url('vessels/api_track'); ?>/' + vesselId, function(res){
      if (!res || !res.ok) return;
      var pts = (res.points || []).filter(function(p){ return p.lat && p.lon; })
        .map(function(p){ return [parseFloat(p.lat), parseFloat(p.lon)]; });

      if (trackLine) {
        map.removeLayer(trackLine);
        trackLine = null;
      }
      if (pts.length >= 2) {
        trackLine = L.polyline(pts).addTo(map);
        map.fitBounds(trackLine.getBounds(), {padding:[20,20]});
      } else {
        alert('Track belum cukup (butuh minimal 2 titik).');
      }
    });
  }

  // Buttons
  $('#btnCenter').on('click', function(){
    if (marker) map.setView(marker.getLatLng(), 12);
  });

  $('#btnTrack').on('click', function(){
    fetchTrack();
  });

  $('#btnToggle').on('click', function(){
    autoRefresh = !autoRefresh;
    $('#refreshStatus').text(autoRefresh ? 'ON' : 'OFF');
  });

  // initial load
  fetchDetail();

  // auto refresh loop
  setInterval(function(){
    if (autoRefresh) fetchDetail();
  }, refreshMs);

})();
</script>

</body>
</html>
