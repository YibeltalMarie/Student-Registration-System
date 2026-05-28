<?php $pageTitle='Add Department';
$errors=$_SESSION['errors']??[]; unset($_SESSION['errors']);
$old=$_SESSION['old']??[]; unset($_SESSION['old']); ?>
<div class="page-header">
  <div class="page-header-left"><h2>Add Department</h2></div>
  <a href="<?= url('departments') ?>" class="btn btn-ghost">← Back</a>
</div>
<div class="card" style="max-width:600px">
  <div class="card-body">
    <form method="POST" action="<?= url('departments/store') ?>" class="form-grid">
      <?= csrf_field() ?>
      <div class="form-group">
        <label>Name <span class="req">*</span></label>
        <input type="text" name="name" value="<?= htmlspecialchars($old['name']??'') ?>"
               class="form-control <?= isset($errors['name'])?'error':'' ?>" required>
        <?php if(isset($errors['name'])): ?><span class="invalid-feedback"><?= htmlspecialchars($errors['name']) ?></span><?php endif; ?>
      </div>
      <div class="form-group">
        <label>Code <span class="req">*</span></label>
        <input type="text" name="code" value="<?= htmlspecialchars($old['code']??'') ?>"
               class="form-control <?= isset($errors['code'])?'error':'' ?>" placeholder="e.g. CS" required>
        <?php if(isset($errors['code'])): ?><span class="invalid-feedback"><?= htmlspecialchars($errors['code']) ?></span><?php endif; ?>
      </div>
      <div class="form-group full">
        <label>Parent Department</label>
        <select name="parent_id" class="form-control">
          <option value="">None (top-level)</option>
          <?php foreach($departments as $d): ?>
          <option value="<?= $d['id'] ?>" <?= ($old['parent_id']??'')==$d['id']?'selected':'' ?>><?= htmlspecialchars($d['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group full">
        <label>Description</label>
        <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($old['description']??'') ?></textarea>
      </div>
      <div class="form-actions full">
        <button type="submit" class="btn btn-primary btn-lg">Create Department</button>
        <a href="<?= url('departments') ?>" class="btn btn-ghost btn-lg">Cancel</a>
      </div>
    </form>
  </div>
</div>
