<?php $pageTitle = 'My Report'; ?>
<div class="page-header">
  <div class="page-header-left">
    <h2>My Academic Report</h2>
    <p>Your complete academic information — view only</p>
  </div>
</div>

<?php if (!$student): ?>
<div class="card"><div class="card-body">
  <div class="empty-state"><div class="icon">👤</div><h3>Profile Not Found</h3><p>Contact your administrator.</p></div>
</div></div>
<?php return; endif; ?>

<!-- Profile Summary -->
<div class="card mb-4">
  <div class="card-header"><h3>👤 Personal Information</h3></div>
  <div class="card-body">
    <div style="display:flex;align-items:center;gap:24px;flex-wrap:wrap">
      <?php if ($student['profile_image']): ?>
      <img src="<?= url('storage/uploads/profiles/' . urlencode($student['profile_image'])) ?>"
           style="width:80px;height:80px;border-radius:12px;object-fit:cover;border:3px solid var(--border)" alt="Photo">
      <?php else: ?>
      <div class="avatar-placeholder lg"><?= strtoupper(substr($student['first_name'], 0, 1)) ?></div>
      <?php endif; ?>
      <div style="flex:1">
        <div style="font-size:22px;font-weight:700;color:var(--text)">
          <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?>
        </div>
        <div style="margin-top:4px">
          <code style="font-size:14px"><?= htmlspecialchars($student['student_id']) ?></code>
          <span class="badge badge-<?= $student['status'] ?>" style="margin-left:8px"><?= ucfirst($student['status']) ?></span>
        </div>
        <div style="margin-top:8px;color:var(--text-secondary);font-size:13px">
          🏛️ <?= htmlspecialchars($student['department_name'] ?? 'No Department') ?>
          &nbsp;·&nbsp; 📅 Enrolled <?= htmlspecialchars($student['enrollment_year']) ?>
        </div>
      </div>
    </div>

    <hr style="margin:20px 0;border:none;border-top:1px solid var(--border)">

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px 32px">
      <div><span style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--muted)">Email</span>
           <div style="margin-top:3px;font-weight:500"><?= htmlspecialchars($student['email']) ?></div></div>
      <div><span style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--muted)">Phone</span>
           <div style="margin-top:3px;font-weight:500"><?= htmlspecialchars($student['phone'] ?? '—') ?></div></div>
      <div><span style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--muted)">Date of Birth</span>
           <div style="margin-top:3px;font-weight:500"><?= format_date($student['date_of_birth'] ?? null) ?></div></div>
      <div><span style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--muted)">Gender</span>
           <div style="margin-top:3px;font-weight:500"><?= ucfirst($student['gender'] ?? '—') ?></div></div>
    </div>
  </div>
</div>

<!-- GPA Summary -->
<?php
$graded = array_filter($enrollments, fn($e) => $e['grade'] !== null);
$avg    = count($graded) ? array_sum(array_column($graded, 'grade')) / count($graded) : null;
?>
<div class="stats-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:20px">
  <div class="stat-card blue">
    <div class="stat-icon-wrap">📚</div>
    <div class="stat-info">
      <div class="stat-value"><?= count($enrollments) ?></div>
      <div class="stat-label">Total Enrollments</div>
    </div>
  </div>
  <div class="stat-card green">
    <div class="stat-icon-wrap">✅</div>
    <div class="stat-info">
      <div class="stat-value"><?= count($graded) ?></div>
      <div class="stat-label">Graded Courses</div>
    </div>
  </div>
  <div class="stat-card purple">
    <div class="stat-icon-wrap">📊</div>
    <div class="stat-info">
      <div class="stat-value"><?= $avg !== null ? number_format($avg, 1) : '—' ?></div>
      <div class="stat-label">Average Grade<?= $avg !== null ? ' (' . letter_grade($avg) . ')' : '' ?></div>
    </div>
  </div>
</div>

<!-- Enrollment Details -->
<div class="card">
  <div class="card-header">
    <h3>📋 Course Enrollments</h3>
    <span style="font-size:12px;color:var(--muted);background:var(--warning-soft);padding:4px 10px;border-radius:20px;font-weight:600">
      🔒 View Only — No Export
    </span>
  </div>
  <?php if (empty($enrollments)): ?>
  <div class="card-body">
    <div class="empty-state"><div class="icon">📋</div><p>No enrollments yet.</p></div>
  </div>
  <?php else: ?>
  <div class="table-wrap">
    <table class="table">
      <thead>
        <tr><th>Code</th><th>Course Name</th><th>Credits</th><th>Status</th><th>Grade</th><th>Letter</th></tr>
      </thead>
      <tbody>
      <?php foreach ($enrollments as $e): ?>
      <tr>
        <td><code><?= htmlspecialchars($e['course_code']) ?></code></td>
        <td><?= htmlspecialchars($e['course_name']) ?></td>
        <td><?= htmlspecialchars($e['credits']) ?> cr</td>
        <td><span class="badge badge-<?= htmlspecialchars($e['status']) ?>"><?= ucfirst($e['status']) ?></span></td>
        <td><?= $e['grade'] !== null ? number_format((float)$e['grade'], 1) : '—' ?></td>
        <td>
          <?php if ($e['grade'] !== null): $l = letter_grade((float)$e['grade']); ?>
          <span class="grade-pill grade-<?= $l ?>"><?= $l ?></span>
          <?php else: ?>—<?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<!-- No export notice -->
<div style="margin-top:16px;padding:14px 18px;background:var(--warning-soft);border-radius:8px;border:1px solid #fcd34d;font-size:13px;color:#92400e">
  ⚠️ <strong>Export is disabled for students.</strong> This page is for viewing your academic information only.
  Contact your administrator for official transcripts.
</div>