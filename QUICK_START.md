# Quick Start Guide

## For Web Interface (Recommended)

1. **Windows Users:**
   - Double-click `start-server.bat`
   - Wait for "PHP Development Server started" message
   - Open browser to: http://localhost:5000

2. **Mac/Linux Users:**
   ```bash
   ./start-server.sh
   ```
   - Open browser to: http://localhost:5000

## For Flutter Mobile Development

1. **Install Flutter SDK** (if not already installed)
   - Download from: https://flutter.dev/docs/get-started/install

2. **Install Dependencies:**
   ```bash
   flutter pub get
   ```

3. **Run Flutter Web:**
   ```bash
   flutter run -d web-server --web-port 3000
   ```

4. **Run on Mobile Device:**
   ```bash
   flutter run
   ```

## Troubleshooting

- **PHP not found:** Install PHP 8.2 or higher
- **Permission errors:** Run as administrator/sudo
- **Port 5000 busy:** Change port in start scripts
- **Database errors:** Check backend/database/ directory permissions

## Features

- Upload requirements and generate BRD documents
- Create comprehensive UAT test cases
- Export documents in multiple formats
- Professional document templates
- File upload support for supporting documents

Need help? Check README.md for detailed instructions.
