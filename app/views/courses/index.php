<?php $pageTitle='Courses'; ?>
<div class="page-header">
  <div class="page-header-left"><h2>Courses</h2><p>Manage course catalog</p></div>
  <div class="page-header-actions">
    <?php if(is_admin()): ?>
    <a href="<?= url('courses/create') ?>" class="btn btn-primary">+ Add Course</a>
    <?php endif; ?>
  </div>
</div>
<div class="card mb-4">
  <div class="card-body">
    <form method="GET" action="<?= url('courses') ?>" class="filter-bar">
      <div class="input-icon-wrap" style="flex:2;min-width:200px">
        <span class="input-icon">🔍</span>
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" class="form-control" placeholder="Search code or name…">
      </div>
      <select name="department" class="form-control">
        <option value="">All Departments</option>
        <?php foreach($departments as $d): ?>
        <option value="<?= $d['id'] ?>" <?= $department==$d['id']?'selected':'' ?>><?= htmlspecialchars($d['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn btn-primary">Filter</button>
      <a href="<?= url('courses') ?>" class="btn btn-ghost">Reset</a>
    </form>
  </div>
</div>
<div class="card">
  <div class="table-wrap">
    <table class="table">
      <thead><tr><th>Code</th><th>Name</th><th>Department</th><th>Credits</th><th>Capacity</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach($courses as $c): ?>
      <tr>
        <td><code><?= htmlspecialchars($c['code']) ?></code></td>
        <td><strong><?= htmlspecialchars($c['name']) ?></strong></td>
        <td><?= htmlspecialchars($c['department_name']??'—') ?></td>
        <td><span class="badge badge-enrolled"><?= $c['credits'] ?> cr</span></td>
        <td><?= $c['max_students'] ?> seats</td>
        <td>
          <div class="actions">
            <?php if(is_admin()): ?>
            <a href="<?= url('courses/edit/'.$c['id']) ?>" class="btn btn-sm btn-outline">Edit</a>
            <?php endif; ?>
            <?php if(is_admin()): ?>
            <form method="POST" action="<?= url('courses/delete/'.$c['id']) ?>" style="display:inline"
                  onsubmit="return confirm('Delete this course?')">
              <?= csrf_field() ?>
              <button type="submit" class="btn btn-sm btn-danger">Delete</button>
            </form>
            <?php endif; ?>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if(empty($courses)): ?>
      <tr><td colspan="6"><div class="table-empty"><div class="empty-icon">📚</div><p>No courses found.</p></div></td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
  <?php if($paging['total_pages']>1): ?>
  <div class="pagination">
    <span class="pagination-info"><?= $paging['total'] ?> courses</span>
    <?php if($paging['has_prev']): ?><a href="?page=<?= $paging['current_page']-1 ?>&search=<?= urlencode($search) ?>&department=<?= urlencode($department) ?>" class="page-btn">←</a><?php endif; ?>
    <?php for($i=max(1,$paging['current_page']-2);$i<=min($paging['total_pages'],$paging['current_page']+2);$i++): ?>
    <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&department=<?= urlencode($department) ?>" class="page-btn <?= $i==$paging['current_page']?'active':'' ?>"><?= $i ?></a>
    <?php endfor; ?>
    <?php if($paging['has_next']): ?><a href="?page=<?= $paging['current_page']+1 ?>&search=<?= urlencode($search) ?>&department=<?= urlencode($department) ?>" class="page-btn">→</a><?php endif; ?>
  </div>
  <?php endif; ?>
</div>
