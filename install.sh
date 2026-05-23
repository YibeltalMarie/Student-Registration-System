#!/bin/bash
echo "=== Student Registration System Installer ==="
echo ""

# Check PHP
if ! command -v php &> /dev/null; then
    echo "ERROR: PHP not found. Please install PHP 8.0+"
    exit 1
fi
echo "✅ PHP $(php -r 'echo PHP_VERSION;') found"

# Check Composer
if ! command -v composer &> /dev/null; then
    echo "Downloading Composer..."
    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer
fi
echo "✅ Composer found"

# Install dependencies
echo "Installing dependencies..."
composer install --no-interaction --prefer-dist --optimize-autoloader

# Setup .env
if [ ! -f ".env" ]; then
    cp .env.example .env
    echo "⚠️  .env created from .env.example — please edit it with your settings"
fi

# Set permissions
chmod -R 755 storage/
chmod -R 755 public/

# ── AUTO-FIX PASSWORDS ──────────────────────────────────────
echo ""
echo "Re-hashing admin and student passwords using your .env pepper..."
php -r "
define('ROOT_PATH', __DIR__);
require ROOT_PATH . '/vendor/autoload.php';

\$dotenv = Dotenv\Dotenv::createImmutable(ROOT_PATH);
\$dotenv->safeLoad();

\$pepper = \$_ENV['PASSWORD_PEPPER'] ?? '';
\$host   = \$_ENV['DB_HOST'] ?? 'localhost';
\$dbname = \$_ENV['DB_NAME'] ?? 'student_registration_system';
\$user   = \$_ENV['DB_USER'] ?? 'root';
\$pass   = \$_ENV['DB_PASS'] ?? '';

\$db = new mysqli(\$host, \$user, \$pass, \$dbname);
if (\$db->connect_error) {
    echo 'DB Error: ' . \$db->connect_error . PHP_EOL;
    exit(1);
}

// Fix admin password = Admin@123 + pepper
\$adminHash = password_hash('Admin@123' . \$pepper, PASSWORD_BCRYPT);
\$stmt = \$db->prepare(\"UPDATE users SET password=?, must_change_password=0, email_verified_at=NOW(), role='admin' WHERE username='admin'\");
\$stmt->bind_param('s', \$adminHash);
\$stmt->execute();
echo '✅ Admin password set (admin / Admin@123)' . PHP_EOL;

// Fix ONLY seeded placeholder students — those still carrying the original
// unpeppered hash inserted by schema.sql. Real student passwords are untouched.
\$placeholderHash = '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
\$studentHash = password_hash('12345678' . \$pepper, PASSWORD_BCRYPT);
\$stmt2 = \$db->prepare(\"UPDATE users SET password=?, must_change_password=1 WHERE role='student' AND password=?\");
\$stmt2->bind_param('ss', \$studentHash, \$placeholderHash);
\$stmt2->execute();
\$fixed = \$stmt2->affected_rows;
echo '✅ Fixed ' . \$fixed . ' seeded student(s) with placeholder hash (password: 12345678)' . PHP_EOL;
echo '   Students with real passwords were left untouched.' . PHP_EOL;

\$db->close();
"

echo ""
echo "=== Installation Complete ==="
echo "1. Edit .env with your DB credentials and SMTP settings"
echo "2. Import database: sudo mariadb -u root -p < database/schema.sql"
echo "3. Run this script again after importing the DB to fix passwords"
echo "4. Point your web server DocumentRoot to: $(pwd)/public"
echo "5. Log in: admin / Admin@123  (change the password immediately!)"