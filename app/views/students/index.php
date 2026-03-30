// app/students/index.php


<h2>Student List</h2>

<a href="index.php?url=students/create" style="background: green; color: white; padding: 10px; display: inline-block; margin-bottom: 20px; text-decoration: none;">+ Add New Student</a>

<table border="1" cellpadding="8" style="width: 100%; border-collapse: collapse;">
    <thead>
        <tr>
            <th>ID</th>
            <th>Full Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Department</th>
            <th>Year</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($students as $student): ?>
        <tr>
            <td><?= $student['student_id'] ?></td>
            <td><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></td>
            <td><?= htmlspecialchars($student['email']) ?></td>
            <td><?= htmlspecialchars($student['phone'] ?? '-') ?></td>
            <td><?= htmlspecialchars($student['department_name'] ?? 'Not Assigned') ?></td>
            <td><?= $student['enrollment_year'] ?></td>
            <td><?= $student['status'] ?></td>
            <td>
                <a href="index.php?url=students/edit&id=<?= $student['student_id'] ?>">Edit</a>
                <a href="index.php?url=students/delete&id=<?= $student['student_id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
</tbody>
</table>
