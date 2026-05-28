<?php $pageTitle = 'My Enrollments'; ?>
<div class="page-header">
  <div class="page-header-left">
    <h2>My Enrollments</h2>
    <p>Courses you are currently enrolled in</p>
  </div>
</div>

<?php if (empty($enrollments)): ?>
<div class="card">
  <div class="card-body">
    <div class="empty-state">
      <div class="icon">📋</div>
      <h3>No Enrollments Yet</h3>
      <p>You have not been enrolled in any courses yet. Contact your administrator.</p>
    </div>
  </div>
</div>
<?php else: ?>
<div class="card">
  <div class="table-wrap">
    <table class="table">
      <thead>
        <tr>
          <th>Course Code</th>
          <th>Course Name</th>
          <th>Credits</th>
          <th>Status</th>
          <th>Grade</th>
          <th>Letter</th>
          <th>Enrolled Date</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($enrollments as $e): ?>
      <tr>
        <td><code><?= htmlspecialchars($e['course_code']) ?></code></td>
        <td><strong><?= htmlspecialchars($e['course_name']) ?></strong></td>
        <td><?= htmlspecialchars($e['credits']) ?> cr</td>
        <td><span class="badge badge-<?= htmlspecialchars($e['status']) ?>"><?= ucfirst(htmlspecialchars($e['status'])) ?></span></td>
        <td><?= $e['grade'] !== null ? number_format((float)$e['grade'], 1) : '—' ?></td>
        <td>
          <?php if ($e['grade'] !== null):
            $l = letter_grade((float)$e['grade']); ?>
            <span class="grade-pill grade-<?= $l ?>"><?= $l ?></span>
          <?php else: ?>—<?php endif; ?>
        </td>
        <td class="text-sm text-muted"><?= format_date($e['enrolled_at'] ?? $e['created_at']) ?></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- GPA Summary Card -->
<?php
$graded = array_filter($enrollments, fn($e) => $e['grade'] !== null);
$avg    = count($graded) ? array_sum(array_column($graded, 'grade')) / count($graded) : null;
?>
<?php if ($avg !== null): ?>
<div class="stats-grid" style="margin-top:16px;grid-template-columns:repeat(3,1fr)">
  <div class="stat-card blue">
    <div class="stat-icon-wrap">📚</div>
    <div class="stat-info">
      <div class="stat-value"><?= count($enrollments) ?></div>
      <div class="stat-label">Total Courses</div>
    </div>
  </div>
  <div class="stat-card green">
    <div class="stat-icon-wrap">✅</div>
    <div class="stat-info">
      <div class="stat-value"><?= count($graded) ?></div>
      <div class="stat-label">Graded</div>
    </div>
  </div>
  <div class="stat-card purple">
    <div class="stat-icon-wrap">📊</div>
    <div class="stat-info">
      <div class="stat-value"><?= number_format($avg, 1) ?></div>
      <div class="stat-label">Average Grade (<?= letter_grade($avg) ?>)</div>
    </div>
  </div>
</div>
<?php endif; ?>
<?php endif; ?>
