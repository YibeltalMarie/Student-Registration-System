// app/students/edit.php

<h2>Edit Student</h2>

<form method="POST" action="">
    <div style="margin-bottom: 15px;">
        <label style="display: block; margin-bottom: 5px; font-weight: bold;">First Name *</label>
        <input type="text" name="first_name" value="<?= htmlspecialchars($student['first_name'] ?? '') ?>" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
    </div>

    <div style="margin-bottom: 15px;">
        <label style="display: block; margin-bottom: 5px; font-weight: bold;">Last Name *</label>
        <input type="text" name="last_name" value="<?= htmlspecialchars($student['last_name'] ?? '') ?>" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
    </div>

    <div style="margin-bottom: 15px;">
        <label style="display: block; margin-bottom: 5px; font-weight: bold;">Email *</label>
        <input type="email" name="email" value="<?= htmlspecialchars($student['email'] ?? '') ?>" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
    </div>

    <div style="margin-bottom: 15px;">
        <label style="display: block; margin-bottom: 5px; font-weight: bold;">Phone</label>
        <input type="tel" name="phone" value="<?= htmlspecialchars($student['phone'] ?? '') ?>" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
    </div>

    <div style="margin-bottom: 15px;">
        <label style="display: block; margin-bottom: 5px; font-weight: bold;">Date of Birth</label>
        <input type="date" name="date_of_birth" value="<?= htmlspecialchars($student['date_of_birth'] ?? '') ?>" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
    </div>
<div style="margin-bottom: 15px;">
        <label style="display: block; margin-bottom: 5px; font-weight: bold;">Gender</label>
        <select name="gender" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            <option value="">Select Gender</option>
            <option value="Male" <?= ($student['gender'] ?? '') == 'Male' ? 'selected' : '' ?>>Male</option>
            <option value="Female" <?= ($student['gender'] ?? '') == 'Female' ? 'selected' : '' ?>>Female</option>
        </select>
    </div>

    <div style="margin-bottom: 15px;">
        <label style="display: block; margin-bottom: 5px; font-weight: bold;">Address</label>
        <textarea name="address" rows="3" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"><?= htmlspecialchars($student['address'] ?? '') ?></textarea>
    </div>

    <div style="margin-bottom: 15px;">
        <label style="display: block; margin-bottom: 5px; font-weight: bold;">Department</label>
        <select name="department_id" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            <option value="">Select Department</option>
            <?php if(isset($departments) && !empty($departments)): ?>
                <?php foreach($departments as $dept): ?>
                    <option value="<?= $dept['department_id'] ?>" <?= ($student['department_id'] ?? '') == $dept['department_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($dept['department_name']) ?>
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
    </div>

    <div style="margin-bottom: 15px;">
        <label style="display: block; margin-bottom: 5px; font-weight: bold;">Enrollment Year *</label>
        <select name="enrollment_year" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            <?php for($year = date('Y'); $year >= date('Y')-5; $year--): ?>
                <option value="<?= $year ?>" <?= ($student['enrollment_year'] ?? '') == $year ? 'selected' : '' ?>><?= $year ?></option>
            <?php endfor; ?>
        </select>
            </div>
    <div style="margin-top: 20px;">
        <button type="submit" style="background: blue; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">Update Student</button>
        <a href="index.php?url=students" style="background: gray; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; margin-left: 10px;">Cancel</a>
    </div>
</form>
