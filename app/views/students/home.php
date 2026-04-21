
<?php require_once __DIR__ . '/layouts/header.php'; ?>

<!-- Hero Section -->
<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 20px; padding: 60px 40px; margin-bottom: 50px; text-align: center; color: white;">
    <h1 style="font-size: 48px; margin-bottom: 20px; animation: fadeInUp 0.8s ease;">
        <i class="fas fa-graduation-cap"></i> Welcome to Student Registration System
    </h1>
    <p style="font-size: 18px; opacity: 0.95; max-width: 600px; margin: 0 auto; line-height: 1.6;">
        Manage student records, track academic progress, and streamline enrollment processes all in one place.
    </p>
</div>

<!-- Statistics Dashboard -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-users" style="color: #667eea;"></i>
        </div>
        <div class="stat-number"><?php echo number_format($studentCount ?? 0); ?></div>
        <div class="stat-label">Total Students</div>
        <a href="index.php?url=students" class="stat-link">
            View All Students <i class="fas fa-arrow-right"></i>
        </a>
    </div>

    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-chalkboard-user" style="color: #48bb78;"></i>
        </div>
        <div class="stat-number"><?php echo number_format($activeStudents ?? $studentCount ?? 0); ?></div>
        <div class="stat-label">Active Students</div>
        <span class="stat-link">
            <i class="fas fa-check-circle"></i> Currently Enrolled
        </span>
    </div>

    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-calendar-alt" style="color: #ed8936;"></i>
        </div>
        <div class="stat-number"><?php echo date('Y'); ?></div>
        <div class="stat-label">Academic Year</div>
        <a href="index.php?url=students/create" class="stat-link">
            Register New Student <i class="fas fa-user-plus"></i>
        </a>
    </div>
</div>

<!-- Recent Students Section -->
<div class="recent-section">
    <div class="section-header">
        <h2>
            <i class="fas fa-clock" style="color: #667eea;"></i>
            Recently Added Students
        </h2>
        <a href="index.php?url=students" class="btn-view-all">
            View All Students <i class="fas fa-arrow-right"></i>
        </a>
    </div>
    

    <?php if (isset($recentStudents) && mysqli_num_rows($recentStudents) > 0): ?>
        <div style="overflow-x: auto;">
            <table class="student-table">
                <thead>
                    <tr>
                        <th><i class="fas fa-hashtag"></i> ID</th>
                        <th><i class="fas fa-user"></i> Full Name</th>
                        <th><i class="fas fa-envelope"></i> Email</th>
                        <th><i class="fas fa-building"></i> Department</th>
                        <th><i class="fas fa-calendar-plus"></i> Registered</th>
                        <th><i class="fas fa-chart-line"></i> Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($student = mysqli_fetch_assoc($recentStudents)): ?>
                        <tr>
                            <td><strong>#<?php echo $student['student_id']; ?></strong></td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="width: 35px; height: 35px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                                        <i class="fas fa-user-graduate"></i>
                                    </div>
                                    <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($student['email']); ?></td>
                            <td>
                                <span class="badge" style="background: #e9d8fd; color: #6b46c1;">
                                    <?php echo htmlspecialchars($student['department_name'] ?? 'Not Assigned'); ?>
                                </span>
                            </td>
                            <td>
                                <i class="far fa-calendar-alt" style="color: #718096;"></i>
                                <?php echo date('M d, Y', strtotime($student['created_at'])); ?>
                            </td>
                            <td>
                                <span class="badge badge-active">
                                    <i class="fas fa-circle" style="font-size: 8px;"></i> Active
                                </span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-folder-open"></i>
            <h3>No Students Yet</h3>
            <p>Get started by adding your first student to the system.</p>
            <a href="index.php?url=students/create" class="btn-primary">
                <i class="fas fa-plus"></i> Add Your First Student
            </a>
        </div>
    <?php endif; ?>
</div>


<!-- Features Section -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; margin-top: 50px;">
    <div style="background: white; border-radius: 15px; padding: 25px; text-align: center; box-shadow: 0 5px 20px rgba(0,0,0,0.05);">
        <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
            <i class="fas fa-database" style="color: white; font-size: 24px;"></i>
        </div>
        <h3 style="color: #2d3748; margin-bottom: 10px;">Secure Database</h3>
        <p style="color: #718096;">All student data is securely stored with proper encryption and backup systems.</p>
    </div>
    
    <div style="background: white; border-radius: 15px; padding: 25px; text-align: center; box-shadow: 0 5px 20px rgba(0,0,0,0.05);">
        <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #48bb78 0%, #38a169 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
            <i class="fas fa-chart-line" style="color: white; font-size: 24px;"></i>
        </div>
        <h3 style="color: #2d3748; margin-bottom: 10px;">Real-time Analytics</h3>
        <p style="color: #718096;">Track student progress and enrollment trends with detailed reports.</p>
    </div>
    
    <div style="background: white; border-radius: 15px; padding: 25px; text-align: center; box-shadow: 0 5px 20px rgba(0,0,0,0.05);">
        <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
            <i class="fas fa-shield-alt" style="color: white; font-size: 24px;"></i>
        </div>
        <h3 style="color: #2d3748; margin-bottom: 10px;">Role-based Access</h3>
        <p style="color: #718096;">Secure authentication system with different permission levels for users.</p>
    </div>
</div>

<style>
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>

