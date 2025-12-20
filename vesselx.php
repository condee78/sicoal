<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>VesselFinder MMSI Viewer</title>
  <style>
    :root {
      --primary-color: #007bff;
      --bg-color: #f4f4f4;
      --card-bg: #fff;
      --corner-radius: 8px;
    }
    *, *::before, *::after {
      box-sizing: border-box;
    }
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 10px;
      background-color: var(--bg-color);
      color: #333;
    }
    .form-container {
      width: 100%;
      max-width: 480px;
      margin: 0 auto 20px;
      padding: 15px;
      background: var(--card-bg);
      border-radius: var(--corner-radius);
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .form-container label {
      display: block;
      margin-bottom: 8px;
      font-weight: bold;
      font-size: 1rem;
    }
    .form-container input[type="text"] {
      width: 100%;
      padding: 12px;
      margin-bottom: 12px;
      border: 1px solid #ccc;
      border-radius: var(--corner-radius);
      font-size: 1rem;
    }
    .form-container button {
      width: 100%;
      padding: 12px;
      font-size: 1rem;
      border: none;
      border-radius: var(--corner-radius);
      background-color: var(--primary-color);
      color: #fff;
      cursor: pointer;
    }
    .form-container button:hover {
      background-color: #0056b3;
    }
    .iframe-container {
      position: relative;
      width: 100%;
      max-width: 480px;
      margin: 0 auto;
      height: 60vh; /* 60% of viewport height for mobile */
      overflow: hidden;
      border-radius: var(--corner-radius);
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      background: var(--card-bg);
    }
    .iframe-container iframe {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      border: none;
      border-radius: var(--corner-radius);
    }
    /* Overlay to hide parts of iframe content */
    .iframe-overlay {
      position: absolute;
      left: 0;
      width: 100%;
      background: var(--bg-color);
      pointer-events: none;
    }
    .iframe-overlay.top {
      top: 0;
      height: 12%;
    }
    .iframe-overlay.bottom {
      bottom: 0;
      height: 10%;
    }
    /* Desktop: use aspect ratio 16:9 */
    @media (min-width: 769px) {
      .form-container {
        max-width: 720px;
      }
      .iframe-container {
        height: auto;
        padding-top: 56.25%; /* 16:9 Aspect Ratio */
      }
    }
  </style>
</head>
<body>
  <div class="form-container">
    <form method="get" action="">
      <label for="mmsi">Masukkan MMSI:</label>
      <input type="text" id="mmsi" name="mmsi" placeholder="e.g. 224399260" value="<?= htmlspecialchars($_GET['mmsi'] ?? '') ?>" required>
      <button type="submit">Tampilkan Vessel</button>
    </form>
  </div>

  <?php if (!empty($_GET['mmsi'])): ?>
    <?php $mmsi = preg_replace('/[^0-9]/', '', $_GET['mmsi']); ?>
    <div class="iframe-container">
      <iframe
        src="https://www.vesselfinder.com/vessels/details/<?= urlencode($mmsi) ?>"
        loading="lazy"
        allowfullscreen>
      </iframe>
      <div class="iframe-overlay top"></div>
      <div class="iframe-overlay bottom"></div>
    </div>
  <?php endif; ?>
</body>
</html>
