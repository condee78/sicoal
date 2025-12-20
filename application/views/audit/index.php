<?php $this->load->view('layout/header', ['title'=>'Audit Logs','breadcrumb'=>['Audit'=>null]]); ?>

<form class="row g-2 mb-3" method="get">
    <div class="col-6 col-md-2">
  <label class="form-label small">Entity ID</label>
  <input class="form-control" name="entity_id" value="<?= htmlentities($filter['entity_id']??'') ?>" placeholder="mis. 123">
</div>

  <div class="col-6 col-md-2">
    <label class="form-label small">Entity</label>
    <input class="form-control" name="entity" value="<?= htmlentities($filter['entity']??'') ?>" placeholder="shipment/import/file/period">
  </div>
  <div class="col-6 col-md-2">
    <label class="form-label small">Action</label>
    <input class="form-control" name="action" value="<?= htmlentities($filter['action']??'') ?>" placeholder="update/create/...">
  </div>
  <div class="col-6 col-md-2">
    <label class="form-label small">Actor</label>
    <select name="actor" class="form-select">
      <option value="">Semua</option>
      <?php $sel=$filter['actor']??''; foreach($users as $u): ?>
        <option value="<?= $u['userid'] ?>" <?= ($sel==$u['userid'])?'selected':'' ?>><?= htmlentities($u['username']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-6 col-md-2">
    <label class="form-label small">From</label>
    <input type="date" class="form-control" name="from" value="<?= htmlentities($filter['from']??'') ?>">
  </div>
  <div class="col-6 col-md-2">
    <label class="form-label small">To</label>
    <input type="date" class="form-control" name="to" value="<?= htmlentities($filter['to']??'') ?>">
  </div>
  <div class="col-12 col-md-2 d-grid">
    <label class="form-label small">&nbsp;</label>
    <button class="btn btn-dark"><i class="bi bi-search"></i> Filter</button>
  </div>
</form>

<div class="card shadow-sm">
  <div class="card-header bg-white"><h6 class="m-0">Logs</h6></div>
  <div class="card-body table-responsive">
    <table class="table table-sm table-hover align-middle datatable">
      <thead>
        <tr>
          <th>#</th>
          <th>Waktu</th>
          <th>User</th>
          <th>Entity</th>
          <th>Entity ID</th>
          <th>Action</th>
          <th>Changes</th>
          <th>Meta</th>
        </tr>
      </thead>
      <tbody>
        <?php $i=1; foreach($rows as $r): ?>
          <tr>
            <td><?= $i++ ?></td>
            <td><?= date('Y-m-d H:i:s', strtotime($r['created_at'])) ?></td>
            <td><?= (int)$r['actor_userid'] ?></td>
            <td><?= htmlentities($r['entity']) ?></td>
            <td><?= htmlentities($r['entity_id']) ?></td>
            <td><span class="badge bg-secondary"><?= htmlentities($r['action']) ?></span></td>
            <td style="max-width:420px;">
              <?php if ($r['changes_json']): $chg=json_decode($r['changes_json'],true); ?>
                <details><summary>show</summary><pre class="small mb-0"><?= htmlentities(json_encode($chg, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)) ?></pre></details>
              <?php else: ?>
                <span class="text-muted">-</span>
              <?php endif; ?>
            </td>
            <td style="max-width:420px;">
              <?php if ($r['meta_json']): $mj=json_decode($r['meta_json'],true); ?>
                <details><summary>show</summary><pre class="small mb-0"><?= htmlentities(json_encode($mj, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)) ?></pre></details>
              <?php else: ?>
                <span class="text-muted">-</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach;?>
      </tbody>
    </table>
  </div>
</div>

<?php $this->load->view('layout/footer'); ?>
