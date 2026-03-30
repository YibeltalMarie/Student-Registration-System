// app/students/create.php
<h2>Add New Student</h2>

<?php if(isset($error)): ?>
    <div style="background: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 20px; border-radius: 5px; border: 1px solid #f5c6cb;">
        <?php echo $error; ?>
    </div>
<?php endif; ?>

<form method="POST" action="">
    <div style="margin-bottom: 15px;">
        <label style="display: block; margin-bottom: 5px; font-weight: bold;">First Name <span style="color: red;">*</span></label>
        <input type="text" name="first_name" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
    </div>

    <div style="margin-bottom: 15px;">
        <label style="display: block; margin-bottom: 5px; font-weight: bold;">Last Name <span style="color: red;">*</span></label>
        <input type="text" name="last_name" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
    </div>

    <div style="margin-bottom: 15px;">
        <label style="display: block; margin-bottom: 5px; font-weight: bold;">Email <span style="color: red;">*</span></label>
        <input type="email" name="email" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
        <small style="color: #666;">Email must be unique</small>
    </div>

    <div style="margin-bottom: 15px;">
        <label style="display: block; margin-bottom: 5px; font-weight: bold;">Phone</label>
        <input type="tel" name="phone" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
    </div>
<div style="margin-bottom: 15px;">
        <label style="display: block; margin-bottom: 5px; font-weight: bold;">Date of Birth</label>
        <input type="date" name="date_of_birth" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
    </div>

    <div style="margin-bottom: 15px;">
        <label style="display: block; margin-bottom: 5px; font-weight: bold;">Gender</label>
        <select name="gender" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            <option value="">-- Select Gender --</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
        </select>
    </div>

    <div style="margin-bottom: 15px;">
        <label style="display: block; margin-bottom: 5px; font-weight: bold;">Address</label>
        <textarea name="address" rows="3" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"></textarea>
    </div>

    <div style="margin-bottom: 15px;">
        <label style="display: block; margin-bottom: 5px; font-weight: bold;">Department</label>
        <select name="department_id" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            <option value="">-- Select Department --</option>
            <?php if(isset($departments) && !empty($departments)): ?>
                <?php foreach($departments as $dept): ?>
                    <option value="<?= $dept['department_id'] ?>"><?= htmlspecialchars($dept['department_name']) ?></option>
                <?php endforeach; ?>
            <?php else: ?>
                <option value="" disabled>No departments available. Please add departments first.</option>
            <?php endif; ?>
        </select>
        <small style="color: #666;">Optional: Leave empty if no department assigned</small>
    </div>
<div style="margin-bottom: 15px;">
        <label style="display: block; margin-bottom: 5px; font-weight: bold;">Enrollment Year <span style="color: red;">*</span></label>
        <select name="enrollment_year" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            <?php for($year = date('Y'); $year >= date('Y')-10; $year--): ?>
                <option value="<?= $year ?>" <?= $year == date('Y') ? 'selected' : '' ?>><?= $year ?></option>
            <?php endfor; ?>
        </select>
    </div>
    <div style="margin-top: 20px;">
        <button type="submit" style="background: green; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px;">💾 Save Student</button>
        <a href="index.php?url=students" style="background: gray; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; margin-left: 10px;">Cancel</a>
    </div>
</form>
