<?php $pageTitle='Add Course';
$errors=$_SESSION['errors']??[]; unset($_SESSION['errors']);
$old=$_SESSION['old']??[]; unset($_SESSION['old']); ?>
<div class="page-header">
  <div class="page-header-left"><h2>Add Course</h2></div>
  <a href="<?= url('courses') ?>" class="btn btn-ghost">← Back</a>
</div>
<div class="card" style="max-width:640px">
  <div class="card-body">
    <form method="POST" action="<?= url('courses/store') ?>" class="form-grid">
      <?= csrf_field() ?>
      <div class="form-group">
        <label>Course Code <span class="req">*</span></label>
        <input type="text" name="code" value="<?= htmlspecialchars($old['code']??'') ?>"
               class="form-control <?= isset($errors['code'])?'error':'' ?>" placeholder="e.g. CS101" required>
        <?php if(isset($errors['code'])): ?><span class="invalid-feedback"><?= htmlspecialchars($errors['code']) ?></span><?php endif; ?>
      </div>
      <div class="form-group">
        <label>Credits <span class="req">*</span></label>
        <input type="number" name="credits" value="<?= htmlspecialchars($old['credits']??3) ?>"
               class="form-control" min="1" max="10" required>
      </div>
      <div class="form-group full">
        <label>Course Name <span class="req">*</span></label>
        <input type="text" name="name" value="<?= htmlspecialchars($old['name']??'') ?>"
               class="form-control <?= isset($errors['name'])?'error':'' ?>" required>
        <?php if(isset($errors['name'])): ?><span class="invalid-feedback"><?= htmlspecialchars($errors['name']) ?></span><?php endif; ?>
      </div>
      <div class="form-group">
        <label>Department <span class="req">*</span></label>
        <select name="department_id" class="form-control" required>
          <option value="">Select department</option>
          <?php foreach($departments as $d): ?>
          <option value="<?= $d['id'] ?>" <?= ($old['department_id']??'')==$d['id']?'selected':'' ?>><?= htmlspecialchars($d['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Max Students</label>
        <input type="number" name="max_students" value="<?= htmlspecialchars($old['max_students']??30) ?>"
               class="form-control" min="1" max="500">
      </div>
      <div class="form-group full">
        <label>Description</label>
        <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($old['description']??'') ?></textarea>
      </div>
      <div class="form-actions full">
        <button type="submit" class="btn btn-primary btn-lg">Create Course</button>
        <a href="<?= url('courses') ?>" class="btn btn-ghost btn-lg">Cancel</a>
      </div>
    </form>
  </div>
</div>
