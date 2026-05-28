<?php $pageTitle = 'Enroll Student';
$errors    = $_SESSION['errors'] ?? []; unset($_SESSION['errors']);
$preselect = (int)($_GET['student_id'] ?? 0);
?>

<div class="page-header">
  <div class="page-header-left">
    <h2>Enroll Student in Courses</h2>
    <p>Select a student — courses shown are from that student's department only</p>
  </div>
  <a href="<?= url('enrollments') ?>" class="btn btn-ghost">← Back</a>
</div>

<div class="card" style="max-width:680px">
  <div class="card-body">

    <?php if (!empty($errors)): ?>
    <div class="alert alert-error mb-4">
      <span class="alert-icon">❌</span>
      <span class="alert-text"><?= htmlspecialchars(current($errors)) ?></span>
    </div>
    <?php endif; ?>

    <form method="POST" action="<?= url('enrollments/store') ?>" id="enrollForm">
      <?= csrf_field() ?>

      <!-- ── Step 1: Pick student ── -->
      <div class="form-group mb-4">
        <label for="studentSelect">
          Student <span class="req">*</span>
        </label>
        <select name="student_id" id="studentSelect" class="form-control" required>
          <option value="">— Select a student —</option>
          <?php foreach ($students as $s): ?>
          <option value="<?= (int)$s['id'] ?>"
                  <?= $preselect === (int)$s['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($s['first_name'] . ' ' . $s['last_name']) ?>
            (<?= htmlspecialchars($s['student_id']) ?>)
            — <?= htmlspecialchars($s['department_name'] ?? 'No Department') ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- ── Step 2: Loading indicator (always visible in DOM) ── -->
      <div id="courseLoading" style="display:none;padding:14px;color:var(--muted);font-size:13px">
        ⏳ Loading courses for this student's department…
      </div>

      <!-- ── Step 3: No-courses message ── -->
      <div id="noCourseMsg" style="display:none" class="alert alert-warning mb-4">
        <span class="alert-icon">⚠️</span>
        <span class="alert-text" id="noCourseText">
          No available courses found for this student's department.
        </span>
      </div>

      <!-- ── Step 4: Multi-select course list (hidden until loaded) ── -->
      <div id="courseGroup" style="display:none" class="form-group mb-4">
        <label for="courseSelect">
          Courses — <?= '<span id="deptLabel" style="color:var(--accent)">student\'s department</span>' ?>
          <span class="req">*</span>
          <br>
          <span class="form-hint">
            Hold <kbd style="background:var(--bg);border:1px solid var(--border);padding:1px 5px;border-radius:3px">Ctrl</kbd>
            (Windows) or
            <kbd style="background:var(--bg);border:1px solid var(--border);padding:1px 5px;border-radius:3px">Cmd</kbd>
            (Mac) to select multiple courses
          </span>
        </label>

        <select id="courseSelect" name="course_ids[]" multiple
                class="form-control"
                style="min-height:200px;padding:6px;font-size:13px;line-height:1.8">
        </select>

        <div id="selectionInfo" style="margin-top:8px;font-size:13px;color:var(--text-secondary)"></div>
      </div>

      <!-- ── Submit ── -->
      <div class="form-actions">
        <button type="submit" id="submitBtn" class="btn btn-primary btn-lg" disabled>
          ✅ Enroll in Selected Course(s)
        </button>
        <a href="<?= url('enrollments') ?>" class="btn btn-ghost btn-lg">Cancel</a>
      </div>
    </form>
  </div>
</div>

<script>
(function () {
  var studentSel  = document.getElementById('studentSelect');
  var courseGroup = document.getElementById('courseGroup');
  var courseSelect= document.getElementById('courseSelect');
  var loading     = document.getElementById('courseLoading');
  var noMsg       = document.getElementById('noCourseMsg');
  var noText      = document.getElementById('noCourseText');
  var selInfo     = document.getElementById('selectionInfo');
  var submitBtn   = document.getElementById('submitBtn');
  var deptLabel   = document.getElementById('deptLabel');

  function resetCourseArea() {
    courseGroup.style.display  = 'none';
    noMsg.style.display        = 'none';
    loading.style.display      = 'none';
    submitBtn.disabled         = true;
    courseSelect.innerHTML     = '';
    selInfo.textContent        = '';
  }

  function loadCourses(studentId) {
    resetCourseArea();
    if (!studentId) return;

    loading.style.display = 'block';

    var url = '<?= url('enrollments/available-courses') ?>?student_id=' + encodeURIComponent(studentId);

    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(function (r) {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
      })
      .then(function (data) {
        loading.style.display = 'none';

        var courses = data.courses || [];

        if (courses.length === 0) {
          noText.textContent = 'No available courses found for this student\'s department. '
            + 'They may already be enrolled in all department courses.';
          noMsg.style.display = 'flex';
          return;
        }

        // Populate multi-select
        courseSelect.innerHTML = '';
        courses.forEach(function (c) {
          var opt    = document.createElement('option');
          opt.value  = c.id;
          var seats  = (c.max_students > 0)
            ? ' | seats: ' + (c.enrolled_count || 0) + '/' + c.max_students
            : '';
          opt.textContent = c.code + ' — ' + c.name + ' (' + c.credits + ' cr' + seats + ')';
          courseSelect.appendChild(opt);
        });

        courseGroup.style.display = 'block';
        submitBtn.disabled        = false;
        selInfo.textContent       = courses.length + ' course(s) available. Select one or more above.';
      })
      .catch(function (err) {
        loading.style.display = 'none';
        noText.textContent    = 'Error loading courses: ' + err.message + '. Please refresh and try again.';
        noMsg.style.display   = 'flex';
      });
  }

  // Track selection count
  courseSelect.addEventListener('change', function () {
    var n = courseSelect.selectedOptions.length;
    if (n === 0) {
      selInfo.textContent = courseSelect.options.length + ' course(s) available. Select one or more above.';
    } else {
      selInfo.textContent = n + ' course(s) selected.';
    }
    submitBtn.disabled = (n === 0);
  });

  // Student dropdown change
  studentSel.addEventListener('change', function () {
    loadCourses(this.value);
  });

  // Form submit: validate at least one course is selected
  document.getElementById('enrollForm').addEventListener('submit', function (e) {
    var selected = courseSelect.selectedOptions.length;
    if (selected === 0) {
      e.preventDefault();
      noText.textContent  = 'Please select at least one course before submitting.';
      noMsg.style.display = 'flex';
      courseGroup.style.display = 'block';
    }
  });

  // Auto-load if student pre-selected (e.g. redirected from student profile)
  if (studentSel.value) {
    loadCourses(studentSel.value);
  }
}());
</script>
