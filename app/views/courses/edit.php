<?php $pageTitle='Edit Course';
$errors=$_SESSION['errors']??[]; unset($_SESSION['errors']); ?>
<div class="page-header">
  <div class="page-header-left"><h2>Edit Course</h2><p><?= htmlspecialchars($course['name']) ?></p></div>
  <a href="<?= url('courses') ?>" class="btn btn-ghost">← Back</a>
</div>
<div class="card" style="max-width:640px">
  <div class="card-body">
    <form method="POST" action="<?= url('courses/update/'.$course['id']) ?>" class="form-grid">
      <?= csrf_field() ?>
      <div class="form-group">
        <label>Course Code <span class="req">*</span></label>
        <input type="text" name="code" value="<?= htmlspecialchars($course['code']) ?>"
               class="form-control <?= isset($errors['code'])?'error':'' ?>" required>
        <?php if(isset($errors['code'])): ?><span class="invalid-feedback"><?= htmlspecialchars($errors['code']) ?></span><?php endif; ?>
      </div>
      <div class="form-group">
        <label>Credits</label>
        <input type="number" name="credits" value="<?= $course['credits'] ?>" class="form-control" min="1" max="10" required>
      </div>
      <div class="form-group full">
        <label>Course Name <span class="req">*</span></label>
        <input type="text" name="name" value="<?= htmlspecialchars($course['name']) ?>" class="form-control" required>
      </div>
      <div class="form-group">
        <label>Department</label>
        <select name="department_id" class="form-control" required>
          <?php foreach($departments as $d): ?>
          <option value="<?= $d['id'] ?>" <?= $course['department_id']==$d['id']?'selected':'' ?>><?= htmlspecialchars($d['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Max Students</label>
        <input type="number" name="max_students" value="<?= $course['max_students'] ?>" class="form-control" min="1">
      </div>
      <div class="form-group full">
        <label>Description</label>
        <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($course['description']??'') ?></textarea>
      </div>
      <div class="form-actions full">
        <button type="submit" class="btn btn-primary btn-lg">Update Course</button>
        <a href="<?= url('courses') ?>" class="btn btn-ghost btn-lg">Cancel</a>
      </div>
    </form>
  </div>
</div>
