<?php $pageTitle = 'Add Student';
$errors = $_SESSION['errors'] ?? []; unset($_SESSION['errors']);
$old    = $_SESSION['old']    ?? []; unset($_SESSION['old']);
?>
<div class="page-header">
  <div class="page-header-left">
    <h2>Add New Student</h2>
    <p>Fill in the details below to register a student</p>
  </div>
  <a href="<?= url('students') ?>" class="btn btn-ghost">← Back</a>
</div>

<div class="card">
  <div class="card-body">
    <form method="POST" action="<?= url('students/store') ?>" enctype="multipart/form-data" class="form-grid">
      <?= csrf_field() ?>

      <div class="form-group">
        <label>First Name <span class="req">*</span></label>
        <input type="text" name="first_name" value="<?= htmlspecialchars($old['first_name']??'') ?>"
               class="form-control <?= isset($errors['first_name'])?'error':'' ?>" required>
        <?php if(isset($errors['first_name'])): ?><span class="invalid-feedback"><?= htmlspecialchars($errors['first_name']) ?></span><?php endif; ?>
      </div>

      <div class="form-group">
        <label>Last Name <span class="req">*</span></label>
        <input type="text" name="last_name" value="<?= htmlspecialchars($old['last_name']??'') ?>"
               class="form-control <?= isset($errors['last_name'])?'error':'' ?>" required>
        <?php if(isset($errors['last_name'])): ?><span class="invalid-feedback"><?= htmlspecialchars($errors['last_name']) ?></span><?php endif; ?>
      </div>

      <div class="form-group">
        <label>Email <span class="req">*</span></label>
        <input type="email" name="email" value="<?= htmlspecialchars($old['email']??'') ?>"
               class="form-control <?= isset($errors['email'])?'error':'' ?>" required>
        <?php if(isset($errors['email'])): ?><span class="invalid-feedback"><?= htmlspecialchars($errors['email']) ?></span><?php endif; ?>
      </div>

      <div class="form-group">
        <label>Phone</label>
        <input type="text" name="phone" value="<?= htmlspecialchars($old['phone']??'') ?>" class="form-control" placeholder="+251…">
      </div>

      <div class="form-group">
        <label>Date of Birth <span class="form-hint">(min age 16)</span></label>
        <input type="date" name="date_of_birth" value="<?= htmlspecialchars($old['date_of_birth']??'') ?>"
               class="form-control <?= isset($errors['date_of_birth'])?'error':'' ?>">
        <?php if(isset($errors['date_of_birth'])): ?><span class="invalid-feedback"><?= htmlspecialchars($errors['date_of_birth']) ?></span><?php endif; ?>
      </div>

      <div class="form-group">
        <label>Gender</label>
        <select name="gender" class="form-control">
          <option value="">Select gender</option>
          <?php foreach(['male','female','other'] as $g): ?>
          <option value="<?= $g ?>" <?= ($old['gender']??'')===$g?'selected':'' ?>><?= ucfirst($g) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label>Department <span class="req">*</span></label>
        <select name="department_id" class="form-control <?= isset($errors['department_id'])?'error':'' ?>" required>
          <option value="">Select department</option>
          <?php foreach($departments as $d): ?>
          <option value="<?= $d['id'] ?>" <?= ($old['department_id']??'')==$d['id']?'selected':'' ?>><?= htmlspecialchars($d['name']) ?></option>
          <?php endforeach; ?>
        </select>
        <?php if(isset($errors['department_id'])): ?><span class="invalid-feedback"><?= htmlspecialchars($errors['department_id']) ?></span><?php endif; ?>
      </div>

      <div class="form-group">
        <label>Enrollment Year <span class="req">*</span></label>
        <input type="number" name="enrollment_year" value="<?= htmlspecialchars($old['enrollment_year']??date('Y')) ?>"
               class="form-control" min="2000" max="<?= date('Y')+1 ?>" required>
      </div>

      <div class="form-group">
        <label>Status</label>
        <select name="status" class="form-control">
          <?php foreach(['active','inactive','graduated','suspended'] as $s): ?>
          <option value="<?= $s ?>" <?= ($old['status']??'active')===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label>Profile Photo</label>
        <input type="file" name="profile_image" accept="image/*" class="form-control"
               onchange="previewImage(this)">
        <?php if(isset($errors['profile_image'])): ?><span class="invalid-feedback"><?= htmlspecialchars($errors['profile_image']) ?></span><?php endif; ?>
        <img id="imagePreview" src="" alt="Preview"
             style="display:none;width:80px;height:80px;object-fit:cover;border-radius:8px;margin-top:8px;border:2px solid var(--border)">
      </div>

      <div class="form-group full">
        <label>Address</label>
        <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($old['address']??'') ?></textarea>
      </div>

      <div class="form-actions full">
        <button type="submit" class="btn btn-primary btn-lg">Create Student</button>
        <a href="<?= url('students') ?>" class="btn btn-ghost btn-lg">Cancel</a>
      </div>
    </form>
  </div>
</div>
