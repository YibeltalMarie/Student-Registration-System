<?php $pageTitle='Enrollments'; ?>
<div class="page-header">
  <div class="page-header-left"><h2>Enrollments</h2><p>Student course enrollments</p></div>
  <div class="page-header-actions">
    <?php if(is_admin()): ?>
    <a href="<?= url('enrollments/create') ?>" class="btn btn-primary">+ Enroll Student</a>
    <?php endif; ?>
  </div>
</div>
<div class="card mb-4">
  <div class="card-body">
    <form method="GET" action="<?= url('enrollments') ?>" class="filter-bar">
      <div class="input-icon-wrap" style="flex:2;min-width:200px">
        <span class="input-icon">🔍</span>
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" class="form-control" placeholder="Search student or course…">
      </div>
      <select name="status" class="form-control">
        <option value="">All Statuses</option>
        <?php foreach(['enrolled','dropped','completed'] as $s): ?>
        <option value="<?= $s ?>" <?= $status===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn btn-primary">Filter</button>
      <a href="<?= url('enrollments') ?>" class="btn btn-ghost">Reset</a>
    </form>
  </div>
</div>
<div class="card">
  <div class="table-wrap">
    <table class="table">
      <thead><tr><th>Student</th><th>Course</th><th>Credits</th><th>Status</th><th>Grade</th><th>Letter</th><th>Date</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach($enrollments as $e): ?>
      <tr>
        <td>
          <div>
            <a href="<?= url('students/show/'.$e['student_id']) ?>" class="font-bold" style="color:var(--accent);text-decoration:none">
              <?= htmlspecialchars($e['first_name'].' '.$e['last_name']) ?>
            </a>
            <div class="text-xs text-muted mt-1"><code><?= htmlspecialchars($e['student_code']) ?></code></div>
          </div>
        </td>
        <td>
          <div><?= htmlspecialchars($e['course_name']) ?></div>
          <div class="text-xs text-muted mt-1"><code><?= htmlspecialchars($e['course_code']) ?></code></div>
        </td>
        <td><?= $e['credits'] ?> cr</td>
        <td><span class="badge badge-<?= $e['status'] ?>"><?= ucfirst($e['status']) ?></span></td>
        <td><?= $e['grade']!==null ? number_format((float)$e['grade'],1) : '—' ?></td>
        <td><?php if($e['grade']!==null): $l=letter_grade((float)$e['grade']); ?>
          <span class="grade-pill grade-<?= $l ?>"><?= $l ?></span>
        <?php else: ?>—<?php endif; ?></td>
        <td class="text-sm text-muted"><?= format_date($e['created_at']) ?></td>
        <td>
          <?php if(is_admin()): ?>
          <a href="<?= url('enrollments/edit/'.$e['id']) ?>" class="btn btn-sm btn-outline">Grade</a>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if(empty($enrollments)): ?>
      <tr><td colspan="8"><div class="table-empty"><div class="empty-icon">📋</div><p>No enrollments found.</p></div></td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
  <?php if($paging['total_pages']>1): ?>
  <div class="pagination">
    <span class="pagination-info"><?= $paging['total'] ?> enrollments</span>
    <?php if($paging['has_prev']): ?><a href="?page=<?= $paging['current_page']-1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>" class="page-btn">←</a><?php endif; ?>
    <?php for($i=max(1,$paging['current_page']-2);$i<=min($paging['total_pages'],$paging['current_page']+2);$i++): ?>
    <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>" class="page-btn <?= $i==$paging['current_page']?'active':'' ?>"><?= $i ?></a>
    <?php endfor; ?>
    <?php if($paging['has_next']): ?><a href="?page=<?= $paging['current_page']+1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>" class="page-btn">→</a><?php endif; ?>
  </div>
  <?php endif; ?>
</div>
