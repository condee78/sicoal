<div class="modal fade" id="modalRemote" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalTitle">Memuat...</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body" id="modalBody">
          <div class="text-center p-4">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2">Sedang memuat...</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  
    </div><!-- /.container-fluid main -->
    <footer class="bg-white border-top py-3 mt-auto">
      <div class="container-fluid small text-muted">
        © <?= date('Y') ?> CoalSM — LBE
      </div>
    </footer>
  </div><!-- /.content-wrap -->

  <!-- JS: Bootstrap & Vendors -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const modalEl   = document.getElementById('modalRemote');
  const modal     = new bootstrap.Modal(modalEl);
  const modalBody = document.getElementById('modalBody');
  const modalTitle= document.getElementById('modalTitle');

  // DELEGATION: click di document, cari anchor .open-modal (termasuk yang dibuat ulang oleh DataTables)
  document.addEventListener('click', async function (e) {
    const link = e.target.closest('a.open-modal');
    if (!link) return;

    e.preventDefault();
    const url = link.getAttribute('href') || '#';

    // Loader
    modalTitle.textContent = 'Memuat data dari: ' + url;
    modalBody.innerHTML = `
      <div class="text-center p-4">
        <div class="spinner-border text-primary" role="status"></div>
        <p class="mt-2">Sedang memuat...</p>
      </div>
    `;
    modal.show();

    // Jika URL kosong (mis. imo=''), kasih pesan yang ramah
    if (!url || url === '#' || /[?&](imo|mmsi)=\s*(&|$)/i.test(url)) {
      modalTitle.textContent = 'Data tidak lengkap';
      modalBody.innerHTML = `<div class="alert alert-warning mb-0">Parameter vessel (IMO/MMSI) kosong.</div>`;
      return;
    }

    // Opsi A: fetch HTML fragment (jalan jika vessel.php mengembalikan fragmen tanpa <script>)
    try {
      const res = await fetch(url, { credentials: 'include' });
      const text = await res.text();
      modalTitle.textContent = 'Data Vessel ' + (link.textContent || '');
      modalBody.innerHTML = text; // catatan: <script> di sini tidak dieksekusi
    } catch (err) {
      modalTitle.textContent = 'Gagal Memuat Data';
      modalBody.innerHTML = `<div class="alert alert-danger mb-0">Tidak bisa memuat dari URL: ${url}</div>`;
    }
  });
});
</script>
  <!-- Charts -->
  <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.49.1"></script>

  <!-- Simple-DataTables -->
  <script src="https://cdn.jsdelivr.net/npm/simple-datatables@9.0.0" ></script>

  <!-- Helpers -->
  <script>
    // Auto-init .datatable
    document.addEventListener('DOMContentLoaded', function(){
      document.querySelectorAll('.datatable').forEach(function(table){
        new simpleDatatables.DataTable(table, { perPage: 10, fixedHeight: false, searchable: true });
      });
    });
  </script>
</body>
</html>
