<style>
.iframe-cropper {
  position: relative;
  width: 100%;
  height: 500px;           /* total tinggi yang ingin ditampilkan */
  overflow: hidden;        /* sembunyikan area di luar kotak */
  border-radius: 12px;
}

.iframe-cropper iframe {
  position: absolute;
  top: -150px;             /* geser iframe ke atas sejauh 150px (bagian atas tersembunyi) */
  left: 0;
  width: 100%;
  height: calc(100% + 150px); /* tambahkan 150px agar bagian bawah tidak terpotong */
}

</style>
<div class="iframe-cropper">
  <iframe
    src="https://www.vesselfinder.com/vessels/details/224399260"
    frameborder="0"
    loading="lazy"
  ></iframe>
</div>
