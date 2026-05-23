<?php $pageTitle = 'Student Profile'; ?>
<div class="page-header">
  <div class="page-header-left">
    <h2>Student Profile</h2>
    <p>Full details and enrollment history</p>
  </div>
  <div class="page-header-actions">
    <?php if(is_admin()): ?>
    <a href="<?= url('students/edit/'.$student['id']) ?>" class="btn btn-outline">✏️ Edit</a>
    <?php endif; ?>
    <a href="<?= url('students') ?>" class="btn btn-ghost">← Back</a>
  </div>
</div>

<div class="profile-layout">
  <!-- Left: avatar card -->
  <div class="profile-card">
    <?php if($student['profile_image']): ?>
    <img src="<?= url('storage/uploads/profiles/'.urlencode($student['profile_image'])) ?>" class="avatar-xl" alt="Photo" style="margin:0 auto;display:block">
    <?php else: ?>
    <div class="avatar-placeholder xl" style="margin:0 auto"><?= strtoupper(substr($student['first_name'],0,1)) ?></div>
    <?php endif; ?>
    <div class="profile-name"><?= htmlspecialchars($student['first_name'].' '.$student['last_name']) ?></div>
    <div class="profile-id"><?= htmlspecialchars($student['student_id']) ?></div>
    <div class="profile-badge mt-2"><span class="badge badge-<?= $student['status'] ?>"><?= ucfirst($student['status']) ?></span></div>
    <hr style="margin:16px 0;border:none;border-top:1px solid var(--border)">
    <div style="font-size:13px;color:var(--text-secondary)">
      <div style="margin-bottom:6px">📅 Enrolled <?= $student['enrollment_year'] ?></div>
      <div>🏛️ <?= htmlspecialchars($student['department_name'] ?? 'No Department') ?></div>
    </div>
  </div>

  <!-- Right: details -->
  <div>
    <div class="card mb-4">
      <div class="card-header"><h3>Personal Information</h3></div>
      <div class="card-body p-0" style="padding:0 20px">
        <div class="info-grid">
          <div class="info-row"><span class="info-label">Email</span><span class="info-value"><?= htmlspecialchars($student['email']) ?></span></div>
          <div class="info-row"><span class="info-label">Phone</span><span class="info-value"><?= htmlspecialchars($student['phone'] ?? '—') ?></span></div>
          <div class="info-row"><span class="info-label">Date of Birth</span><span class="info-value"><?= format_date($student['date_of_birth']??null) ?></span></div>
          <div class="info-row"><span class="info-label">Gender</span><span class="info-value"><?= ucfirst($student['gender'] ?? '—') ?></span></div>
          <div class="info-row"><span class="info-label">Address</span><span class="info-value"><?= htmlspecialchars($student['address'] ?? '—') ?></span></div>
          <div class="info-row"><span class="info-label">Registered</span><span class="info-value"><?= format_date($student['created_at']) ?></span></div>
        </div>
      </div>
    </div>

    <!-- Enrollments -->
    <div class="card">
      <div class="card-header">
        <h3>Course Enrollments (<?= count($enrollments) ?>)</h3>
        <?php if(is_admin()): ?>
        <a href="<?= url('enrollments/create?student_id='.$student['id']) ?>" class="btn btn-sm btn-primary">+ Enroll</a>
        <?php endif; ?>
      </div>
      <div class="table-wrap">
        <table class="table">
          <thead><tr><th>Code</th><th>Course</th><th>Credits</th><th>Status</th><th>Grade</th><th>Letter</th></tr></thead>
          <tbody>
          <?php foreach($enrollments as $e): ?>
          <tr>
            <td><code><?= htmlspecialchars($e['course_code']) ?></code></td>
            <td><?= htmlspecialchars($e['course_name']) ?></td>
            <td><?= $e['credits'] ?></td>
            <td><span class="badge badge-<?= $e['status'] ?>"><?= ucfirst($e['status']) ?></span></td>
            <td><?= $e['grade']!==null ? number_format((float)$e['grade'],1) : '—' ?></td>
            <td><?php if($e['grade']!==null): $l=letter_grade((float)$e['grade']); ?>
              <span class="grade-pill grade-<?= $l ?>"><?= $l ?></span>
            <?php else: ?>—<?php endif; ?></td>
          </tr>
          <?php endforeach; ?>
          <?php if(empty($enrollments)): ?>
          <tr><td colspan="6"><div class="table-empty"><div class="empty-icon">📋</div><p>No enrollments yet.</p></div></td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
