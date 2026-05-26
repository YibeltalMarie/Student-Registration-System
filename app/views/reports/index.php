<?php $pageTitle = 'Reports'; ?>
<div class="page-header">
  <div class="page-header-left"><h2>Reports &amp; Exports</h2><p>Export data and manage system operations</p></div>
</div>
<div class="reports-grid">

  <div class="card">
    <div class="card-header"><h3>📊 Export Enrollments</h3></div>
    <div class="card-body">
      <p class="text-muted text-sm mb-4">Download all enrollment records.</p>
      <div class="btn-group">
        <a href="<?= url('reports/export/pdf') ?>" class="btn btn-primary">⬇ PDF Report</a>
        <a href="<?= url('reports/export/csv') ?>" class="btn btn-ghost">⬇ CSV Export</a>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header"><h3>👨‍🎓 Export Students</h3></div>
    <div class="card-body">
      <p class="text-muted text-sm mb-4">Download student records with department info.</p>
      <div class="btn-group">
        <a href="<?= url('students/export-pdf') ?>" class="btn btn-primary">⬇ PDF Report</a>
        <a href="<?= url('students/export-csv') ?>" class="btn btn-ghost">⬇ CSV Export</a>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header"><h3>📧 Bulk Email to Students</h3></div>
    <div class="card-body">
      <p class="text-muted text-sm mb-4">
        Send a custom email to all active students or filter by department.
      </p>
      <form method="POST" action="<?= url('bulk-email') ?>">
        <?= csrf_field() ?>
        <div class="form-group mb-3">
          <label>Filter by Department <span class="form-hint">(optional — leave blank for all students)</span></label>
          <select name="department_id" class="form-control">
            <option value="">All Departments (all active students)</option>
            <?php foreach ($departments as $d): ?>
            <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group mb-3">
          <label>Subject <span class="req">*</span></label>
          <input type="text" name="subject" class="form-control" required placeholder="Email subject line">
        </div>
        <div class="form-group mb-4">
          <label>Message <span class="req">*</span></label>
          <textarea name="message" class="form-control" rows="5" required
                    placeholder="Write your message here. Students will receive this personalized with their name."></textarea>
        </div>
        <button type="submit" class="btn btn-primary btn-block"
                onclick="return confirm('Send bulk email to selected students?')">
          📧 Send Bulk Email
        </button>
        <?php if (!\App\Services\EmailService::isConfigured()): ?>
        <p class="form-hint mt-2" style="color:var(--warning)">
          ⚠️ SMTP not configured — emails will be logged to file only.
        </p>
        <?php endif; ?>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-header"><h3>💾 Database Backup</h3></div>
    <div class="card-body">
      <p class="text-muted text-sm mb-4">Create a full SQL dump of the database.</p>
      <form method="POST" action="<?= url('reports/backup') ?>">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-secondary btn-block">Create SQL Backup</button>
      </form>
      <hr style="margin:16px 0;border:none;border-top:1px solid var(--border)">
      <a href="<?= url('activity-logs') ?>" class="btn btn-ghost btn-block">🔍 View Activity Logs</a>
    </div>
  </div>

</div>