# Deployment Guide for BRD & UAT Generator

## Local Testing Export

### Quick Start Package

1. **Download Required Files**
   Copy these essential files to your local directory:
   ```
   - server.php (main server file)
   - index.html (web interface)
   - backend/ (entire directory)
   - lib/ (Flutter source code)
   - web/ (Flutter web build)
   - main.dart (Flutter entry point)
   ```

2. **Minimum Setup Commands**
   ```bash
   # Navigate to project directory
   cd your-project-folder
   
   # Start PHP server
   php -S localhost:5000 server.php
   
   # Open browser
   # Go to: http://localhost:5000
   ```

3. **Database Auto-Setup**
   - SQLite database creates automatically on first run
   - Located at: `backend/database/brd_uat_generator.db`
   - No manual database setup required

### File Permissions Setup

```bash
# Make directories writable (Unix/Linux/Mac)
chmod 755 backend/uploads
chmod 755 backend/exports
chmod 755 backend/database

# Or create directories if they don't exist
mkdir -p backend/uploads backend/exports backend/database
```

### Windows Setup

```cmd
# Start server from command prompt
php -S localhost:5000 server.php

# Or create a batch file (start-server.bat):
@echo off
php -S localhost:5000 server.php
pause
```

## Production Deployment Options

### Option 1: Shared Hosting (cPanel/Plesk)

1. **Upload Files**
   - Upload all files to public_html directory
   - Ensure backend/ directory is outside public_html for security

2. **Create .htaccess**
   ```apache
   RewriteEngine On
   RewriteCond %{REQUEST_FILENAME} !-f
   RewriteCond %{REQUEST_FILENAME} !-d
   RewriteRule ^(.*)$ server.php [QSA,L]
   ```

3. **Database Setup**
   - Use cPanel to create MySQL database
   - Update database credentials in backend/config/database.php

### Option 2: VPS/Cloud Server

1. **Server Requirements**
   ```bash
   # Ubuntu/Debian
   sudo apt update
   sudo apt install php8.2 php8.2-sqlite3 php8.2-pdo nginx
   
   # CentOS/RHEL
   sudo yum install php php-sqlite3 php-pdo nginx
   ```

2. **Nginx Configuration**
   ```nginx
   server {
       listen 80;
       server_name your-domain.com;
       root /var/www/html;
       index server.php;
       
       location / {
           try_files $uri $uri/ /server.php?$query_string;
       }
       
       location ~ \.php$ {
           fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
           fastcgi_index server.php;
           fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
           include fastcgi_params;
       }
   }
   ```

### Option 3: Docker Deployment

Create `Dockerfile`:
```dockerfile
FROM php:8.2-apache

# Install SQLite
RUN apt-get update && apt-get install -y sqlite3 libsqlite3-dev

# Enable PDO SQLite
RUN docker-php-ext-install pdo pdo_sqlite

# Copy application
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html/
RUN chmod -R 755 /var/www/html/backend/

EXPOSE 80
```

Create `docker-compose.yml`:
```yaml
version: '3.8'
services:
  web:
    build: .
    ports:
      - "8080:80"
    volumes:
      - ./backend/uploads:/var/www/html/backend/uploads
      - ./backend/exports:/var/www/html/backend/exports
      - ./backend/database:/var/www/html/backend/database
```

## Flutter App Deployment

### Web Deployment
```bash
# Build Flutter web app
flutter build web

# Copy build files to web server
cp -r build/web/* /var/www/html/flutter/
```

### Mobile App Store Deployment

1. **Android (Google Play Store)**
   ```bash
   # Build APK
   flutter build apk --release
   
   # Build App Bundle (recommended)
   flutter build appbundle --release
   ```

2. **iOS (App Store)**
   ```bash
   # Build iOS app
   flutter build ios --release
   
   # Open in Xcode for signing and submission
   open ios/Runner.xcworkspace
   ```

## Environment Configuration

### Development Environment
```php
// backend/config/database.php
private $debug = true;
private $environment = 'development';
```

### Production Environment
```php
// backend/config/database.php
private $debug = false;
private $environment = 'production';

// Use environment variables
$host = getenv('DB_HOST') ?: 'localhost';
$dbname = getenv('DB_NAME') ?: 'brd_uat_generator';
```

## Security Considerations

### File Upload Security
```php
// Add to backend/api/requirements.php
$allowedTypes = ['pdf', 'doc', 'docx', 'txt'];
$maxFileSize = 5 * 1024 * 1024; // 5MB

// Validate file type and size
if (!in_array($fileExtension, $allowedTypes)) {
    throw new Exception('File type not allowed');
}
```

### Production Hardening
1. Move backend/ directory outside web root
2. Disable PHP error display in production
3. Use HTTPS with SSL certificate
4. Implement rate limiting for API endpoints
5. Add authentication for sensitive operations

## Monitoring and Maintenance

### Log Files
- PHP Error Log: Check server error logs
- Application Log: Implement custom logging
- Access Log: Monitor web server access

### Backup Strategy
```bash
# Database backup (SQLite)
cp backend/database/brd_uat_generator.db backup/

# Files backup
tar -czf backup_$(date +%Y%m%d).tar.gz backend/uploads backend/exports
```

### Updates and Maintenance
1. Regular PHP and server updates
2. Monitor disk space for uploads/exports
3. Clean old generated documents periodically
4. Update SSL certificates

This deployment guide covers all major scenarios for running the BRD & UAT Generator application locally and in production environments.