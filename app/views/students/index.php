<?php $pageTitle = 'Students'; ?>

<div class="page-header">
  <div class="page-header-left">
    <h2>Students</h2>
    <p>Manage all registered students</p>
  </div>
  <div class="page-header-actions">
    <?php if (is_admin()): ?>
    <a href="<?= url('students/create') ?>" class="btn btn-primary">+ Add Student</a>
    <a href="<?= url('students/export-csv') ?>" class="btn btn-ghost">⬇ CSV</a>
    <a href="<?= url('students/export-pdf') ?>" class="btn btn-ghost">⬇ PDF</a>
    <?php endif; ?>
    <a href="<?= url('students/ranking') ?>" class="btn btn-ghost">🏆 Rankings</a>
  </div>
</div>

<!-- Filter Bar -->
<div class="card mb-4">
  <div class="card-body">
    <form method="GET" action="<?= url('students') ?>" class="filter-bar">
      <div class="input-icon-wrap" style="flex:2;min-width:200px">
        <span class="input-icon">🔍</span>
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
               class="form-control" placeholder="Search name, ID, email…">
      </div>
      <select name="status" class="form-control">
        <option value="">All Statuses</option>
        <?php foreach (['active','inactive','graduated','suspended'] as $s): ?>
        <option value="<?= $s ?>" <?= $status===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
        <?php endforeach; ?>
      </select>
      <select name="department" class="form-control">
        <option value="">All Departments</option>
        <?php foreach ($departments as $d): ?>
        <option value="<?= $d['id'] ?>" <?= $department==$d['id']?'selected':'' ?>><?= htmlspecialchars($d['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn btn-primary">Filter</button>
      <a href="<?= url('students') ?>" class="btn btn-ghost">Reset</a>
    </form>
  </div>
</div>

<?php if (is_admin()): ?>
<div class="card mb-4">
  <div class="card-body">
    <form method="POST" action="<?= url('students/import') ?>" enctype="multipart/form-data" class="filter-bar">
      <?= csrf_field() ?>
      <input type="file" name="csv_file" accept=".csv" class="form-control" style="max-width:300px" required>
      <button type="submit" class="btn btn-ghost">📥 Import CSV</button>
      <a href="<?= url('students/csv-template') ?>" class="btn btn-ghost">📄 Template</a>
    </form>
  </div>
</div>
<?php endif; ?>

<div class="card">
  <div class="table-wrap">
    <table class="table">
      <thead>
        <tr>
          <th>Student</th><th>Student ID</th><th>Email</th>
          <th>Department</th><th>Year</th><th>Status</th><th>Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($students as $s): ?>
      <tr>
        <td>
          <div class="flex items-center gap-2">
            <?php if ($s['profile_image']): ?>
            <img src="<?= url('storage/uploads/profiles/'.urlencode($s['profile_image'])) ?>" class="avatar" alt="">
            <?php else: ?>
            <div class="avatar-placeholder"><?= strtoupper(substr($s['first_name'],0,1)) ?></div>
            <?php endif; ?>
            <span class="font-bold"><?= htmlspecialchars($s['first_name'].' '.$s['last_name']) ?></span>
          </div>
        </td>
        <td><code><?= htmlspecialchars($s['student_id']) ?></code></td>
        <td><?= htmlspecialchars($s['email']) ?></td>
        <td><?= htmlspecialchars($s['department_name'] ?? '—') ?></td>
        <td><?= $s['enrollment_year'] ?></td>
        <td><span class="badge badge-<?= $s['status'] ?>"><?= ucfirst($s['status']) ?></span></td>
        <td>
          <div class="actions">
            <a href="<?= url('students/show/'.$s['id']) ?>" class="btn btn-sm btn-ghost">View</a>
            <?php if (is_admin()): ?>
            <a href="<?= url('students/edit/'.$s['id']) ?>" class="btn btn-sm btn-outline">Edit</a>
            <?php endif; ?>
            <?php if (is_admin()): ?>
            <form method="POST" action="<?= url('students/delete/'.$s['id']) ?>" style="display:inline"
                  onsubmit="return confirm('Delete this student? This cannot be undone.')">
              <?= csrf_field() ?>
              <button type="submit" class="btn btn-sm btn-danger">Delete</button>
            </form>
            <?php endif; ?>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($students)): ?>
      <tr><td colspan="7">
        <div class="table-empty">
          <div class="empty-icon">👨‍🎓</div>
          <p>No students found. Try adjusting your filters.</p>
        </div>
      </td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
  <?php if ($paging['total_pages'] > 1): ?>
  <div class="pagination">
    <span class="pagination-info">Showing <?= count($students) ?> of <?= $paging['total'] ?> students</span>
    <?php if ($paging['has_prev']): ?>
    <a href="?page=<?= $paging['current_page']-1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>&department=<?= urlencode($department) ?>" class="page-btn">←</a>
    <?php endif; ?>
    <?php for ($i=max(1,$paging['current_page']-2); $i<=min($paging['total_pages'],$paging['current_page']+2); $i++): ?>
    <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>&department=<?= urlencode($department) ?>"
       class="page-btn <?= $i==$paging['current_page']?'active':'' ?>"><?= $i ?></a>
    <?php endfor; ?>
    <?php if ($paging['has_next']): ?>
    <a href="?page=<?= $paging['current_page']+1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>&department=<?= urlencode($department) ?>" class="page-btn">→</a>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</div>
