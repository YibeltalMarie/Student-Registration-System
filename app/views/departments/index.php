<?php $pageTitle = 'Departments'; ?>
<div class="page-header">
  <div class="page-header-left"><h2>Departments</h2><p>Manage academic departments and their hierarchy</p></div>
  <div class="page-header-actions">
    <?php if(is_admin()): ?>
    <a href="<?= url('departments/create') ?>" class="btn btn-primary">+ Add Department</a>
    <?php endif; ?>
  </div>
</div>

<div style="display:grid;grid-template-columns:320px 1fr;gap:20px;align-items:start">
  <!-- Tree View -->
  <div class="card">
    <div class="card-header"><h3>🌲 Hierarchy</h3></div>
    <div class="card-body">
      <?php
      function renderTree(array $nodes, int $depth=0): void {
        foreach($nodes as $node) {
          echo '<div class="tree-node" style="padding-left:'.($depth*20+8).'px">';
          echo '<span class="tree-dept-icon">'.($depth===0?'🏛️':'📂').'</span>';
          echo '<span style="flex:1">'.htmlspecialchars($node['name']).'</span>';
          echo '<code style="font-size:11px">'.htmlspecialchars($node['code']).'</code>';
          echo '</div>';
          if(!empty($node['children'])) renderTree($node['children'],$depth+1);
        }
      }
      if(!empty($tree)) renderTree($tree);
      else echo '<p class="text-muted text-sm">No departments yet.</p>';
      ?>
    </div>
  </div>

  <!-- Table -->
  <div class="card">
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Name</th><th>Code</th><th>Parent</th><th>Description</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach($departments as $d): ?>
        <tr>
          <td><strong><?= htmlspecialchars($d['name']) ?></strong></td>
          <td><code><?= htmlspecialchars($d['code']) ?></code></td>
          <td><?= htmlspecialchars($d['parent_name'] ?? '—') ?></td>
          <td><span class="text-muted text-sm"><?= htmlspecialchars(substr($d['description']??'',0,50)) ?><?= strlen($d['description']??'')>50?'…':'' ?></span></td>
          <td>
            <div class="actions">
              <?php if(is_admin()): ?>
              <a href="<?= url('departments/edit/'.$d['id']) ?>" class="btn btn-sm btn-outline">Edit</a>
              <?php endif; ?>
              <?php if(is_admin()): ?>
              <form method="POST" action="<?= url('departments/delete/'.$d['id']) ?>" style="display:inline"
                    onsubmit="return confirm('Delete this department?')">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
              </form>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($departments)): ?>
        <tr><td colspan="5"><div class="table-empty"><div class="empty-icon">🏛️</div><p>No departments yet.</p></div></td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
