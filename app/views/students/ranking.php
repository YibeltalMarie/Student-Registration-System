<?php $pageTitle = 'Student Rankings'; ?>
<div class="page-header">
  <div class="page-header-left">
    <h2>🏆 Student Rankings</h2>
    <p>Ranked by average grade using SQL window functions</p>
  </div>
  <a href="<?= url('students') ?>" class="btn btn-ghost">← Back</a>
</div>
<div class="card">
  <div class="table-wrap">
    <table class="table">
      <thead>
        <tr><th>Rank</th><th>Student</th><th>Department</th><th>Avg Grade</th><th>Letter</th><th>Dept Rank</th></tr>
      </thead>
      <tbody>
      <?php foreach($students as $s): $r=(int)$s['overall_rank']; ?>
      <tr style="<?= $r<=3?'background:#fffbf0':'' ?>">
        <td class="rank-cell">
          <div class="rank-medal">
            <?= $r===1?'🥇':($r===2?'🥈':($r===3?'🥉':'#'.$r)) ?>
          </div>
        </td>
        <td>
          <div>
            <a href="<?= url('students/show/'.$s['id']) ?>" class="font-bold" style="color:var(--accent);text-decoration:none">
              <?= htmlspecialchars($s['first_name'].' '.$s['last_name']) ?>
            </a>
            <div class="text-xs text-muted mt-1"><code><?= htmlspecialchars($s['student_id']) ?></code></div>
          </div>
        </td>
        <td><?= htmlspecialchars($s['department_name'] ?? '—') ?></td>
        <td><strong><?= number_format((float)$s['avg_grade'],1) ?></strong></td>
        <td><?php $l=letter_grade((float)$s['avg_grade']); ?><span class="grade-pill grade-<?= $l ?>"><?= $l ?></span></td>
        <td><span class="badge badge-active">#<?= $s['dept_rank'] ?></span></td>
      </tr>
      <?php endforeach; ?>
      <?php if(empty($students)): ?>
      <tr><td colspan="6"><div class="table-empty"><div class="empty-icon">🏆</div><p>No graded students yet.</p></div></td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
