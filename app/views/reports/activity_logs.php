<?php $pageTitle='Activity Logs'; ?>
<div class="page-header">
  <div class="page-header-left"><h2>🔍 Activity Logs</h2><p>All CRUD operations audit trail</p></div>
  <a href="<?= url('reports') ?>" class="btn btn-ghost">← Back</a>
</div>
<div class="card">
  <div class="table-wrap">
    <table class="table">
      <thead><tr><th>Time</th><th>User</th><th>Action</th><th>Entity</th><th>Entity ID</th><th>IP Address</th></tr></thead>
      <tbody>
      <?php foreach($logs as $log): ?>
      <tr>
        <td class="text-sm text-muted"><?= format_date($log['created_at'],'d M Y H:i') ?></td>
        <td><?= htmlspecialchars($log['username']??'System') ?></td>
        <td>
          <?php $ac=$log['action'];
          $bc=$ac==='delete'?'badge-suspended':($ac==='create'?'badge-active':'badge-enrolled'); ?>
          <span class="badge <?= $bc ?>"><?= htmlspecialchars($ac) ?></span>
        </td>
        <td><?= htmlspecialchars($log['entity_type']) ?></td>
        <td><code><?= $log['entity_id'] ?></code></td>
        <td class="text-sm text-muted"><?= htmlspecialchars($log['ip_address']) ?></td>
      </tr>
      <?php endforeach; ?>
      <?php if(empty($logs)): ?>
      <tr><td colspan="6"><div class="table-empty"><div class="empty-icon">🔍</div><p>No activity logged yet.</p></div></td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
  <?php if($paging['total_pages']>1): ?>
  <div class="pagination">
    <span class="pagination-info"><?= $paging['total'] ?> log entries</span>
    <?php if($paging['has_prev']): ?><a href="?page=<?= $paging['current_page']-1 ?>" class="page-btn">←</a><?php endif; ?>
    <?php for($i=max(1,$paging['current_page']-2);$i<=min($paging['total_pages'],$paging['current_page']+2);$i++): ?>
    <a href="?page=<?= $i ?>" class="page-btn <?= $i==$paging['current_page']?'active':'' ?>"><?= $i ?></a>
    <?php endfor; ?>
    <?php if($paging['has_next']): ?><a href="?page=<?= $paging['current_page']+1 ?>" class="page-btn">→</a><?php endif; ?>
  </div>
  <?php endif; ?>
</div>