    </main>
    
    <footer style="background: white; margin-top: 60px; padding: 40px 0 20px; box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.05);">
        <div style="max-width: 1400px; margin: 0 auto; padding: 0 30px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 40px; margin-bottom: 30px;">
                <div>
                    <h3 style="color: #2d3748; margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-graduation-cap" style="color: #667eea;"></i>
                        <?php echo APP_NAME; ?>
                    </h3>
                    <p style="color: #718096; line-height: 1.6;">A comprehensive student registration system for managing student records, courses, and enrollments efficiently.</p>
                </div>
                <div>
                    <h4 style="color: #2d3748; margin-bottom: 15px;">Quick Links</h4>
                    <ul style="list-style: none; line-height: 2;">
                        <li><a href="index.php" style="color: #718096; text-decoration: none;">🏠 Home</a></li>
                        <li><a href="index.php?url=students" style="color: #718096; text-decoration: none;">👥 View Students</a></li>
                        <li><a href="index.php?url=students/create" style="color: #718096; text-decoration: none;">➕ Add Student</a></li>
                    </ul>
                </div>
                <div>
                    <h4 style="color: #2d3748; margin-bottom: 15px;">Statistics</h4>
                    <ul style="list-style: none; line-height: 2;">
                        <li style="color: #718096;">📚 Total Students: <span id="footer-student-count">Loading...</span></li>
                        <li style="color: #718096;">🎓 Active Enrollments: <span id="footer-active-count">-</span></li>
                    </ul>
                </div>
            </div>
            <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 20px 0;">
            <div style="text-align: center; color: #a0aec0; font-size: 14px;">
                <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</p>
                <p style="margin-top: 5px;">Built with <i class="fas fa-heart" style="color: #e53e3e;"></i> for better education management</p>
            </div>
        </div>
    </footer>
    
    <script>
        // Update footer stats dynamically
        fetch(window.location.href)
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const studentCount = doc.querySelector('.stat-number')?.innerText || '0';
                document.getElementById('footer-student-count').innerText = studentCount;
            });
    </script>
</body>
</html>
