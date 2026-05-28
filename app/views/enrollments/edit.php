<?php $pageTitle='Update Grade';
$errors=$_SESSION['errors']??[]; unset($_SESSION['errors']); ?>
<div class="page-header">
  <div class="page-header-left"><h2>Update Grade</h2></div>
  <a href="<?= url('enrollments') ?>" class="btn btn-ghost">← Back</a>
</div>
<div class="card" style="max-width:480px">
  <div class="card-header"><h3><?= htmlspecialchars($enrollment['course_name']) ?></h3></div>
  <div class="card-body">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:24px;background:var(--bg);padding:16px;border-radius:8px">
      <div><span class="info-label">Student</span><div class="info-value"><?= htmlspecialchars($enrollment['first_name'].' '.$enrollment['last_name']) ?></div></div>
      <div><span class="info-label">Student ID</span><div class="info-value"><code><?= htmlspecialchars($enrollment['student_code']) ?></code></div></div>
      <div><span class="info-label">Course Code</span><div class="info-value"><code><?= htmlspecialchars($enrollment['course_code']) ?></code></div></div>
      <div><span class="info-label">Current Grade</span><div class="info-value">
        <?= $enrollment['grade']!==null ? number_format((float)$enrollment['grade'],1).' ('.letter_grade((float)$enrollment['grade']).')' : 'Not graded' ?>
      </div></div>
    </div>
    <?php if(!empty($errors)): ?>
    <div class="alert alert-error mb-4">
      <span class="alert-icon">❌</span>
      <span class="alert-text"><?= htmlspecialchars(current($errors)) ?></span>
    </div>
    <?php endif; ?>
    <form method="POST" action="<?= url('enrollments/grade/'.$enrollment['id']) ?>">
      <?= csrf_field() ?>
      <div class="form-group mb-4">
        <label>Grade (0 – 100) <span class="req">*</span></label>
        <input type="number" name="grade" value="<?= htmlspecialchars($enrollment['grade']??'') ?>"
               class="form-control" min="0" max="100" step="0.1" required autofocus>
      </div>
      <div class="form-actions">
        <button type="submit" class="btn btn-primary btn-lg">Save Grade</button>
        <a href="<?= url('enrollments') ?>" class="btn btn-ghost btn-lg">Cancel</a>
      </div>
    </form>
  </div>
</div>
