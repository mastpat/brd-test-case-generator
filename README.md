# BRD & UAT Generator

A Flutter-PHP application that generates Business Requirement Documents (BRD) and UAT test cases from uploaded software requirements.

## Features

- **Web Interface**: Professional drag-and-drop file upload interface
- **Document Generation**: Automatic BRD creation in HTML format with professional styling
- **UAT Test Cases**: Comprehensive test case generation in CSV format with positive/negative scenarios
- **Flutter Mobile App**: Cross-platform mobile application with Material Design
- **File Support**: Upload supporting documents (PDF, DOC, DOCX, TXT)
- **Database Integration**: SQLite for development, PostgreSQL for production

## Local Development Setup

### Prerequisites

- PHP 8.2 or higher
- Flutter SDK
- SQLite (included with PHP)
- Web browser

### Installation Steps

1. **Clone/Download the project files**
   ```bash
   # Extract all files to your local directory
   cd brd-uat-generator
   ```

2. **Start the PHP Server**
   ```bash
   php -S localhost:5000 server.php
   ```

3. **Access the Application**
   - Open your browser and go to: `http://localhost:5000`
   - The database will be automatically created on first run

4. **For Flutter Development**
   ```bash
   # Install dependencies
   flutter pub get
   
   # Run on web
   flutter run -d web-server --web-port 3000
   
   # Run on mobile (with device connected)
   flutter run
   ```

## Project Structure

```
brd-uat-generator/
├── backend/                     # PHP Backend
│   ├── api/                    # REST API endpoints
│   │   ├── requirements.php    # Main requirements handling
│   │   ├── download.php       # File download handler
│   │   ├── generate_brd.php   # BRD generation
│   │   └── generate_uat.php   # UAT generation
│   ├── config/                
│   │   └── database.php       # Database configuration
│   ├── models/                
│   │   └── Requirement.php    # Data model
│   ├── utils/                 
│   │   ├── DocumentGenerator.php  # BRD generation logic
│   │   └── UATGenerator.php      # UAT generation logic
│   ├── uploads/               # Uploaded files storage
│   ├── exports/               # Generated documents
│   └── database/              # SQLite database file
├── lib/                       # Flutter Application
│   ├── models/               # Data models
│   ├── screens/             # UI screens
│   ├── services/            # API communication
│   └── widgets/             # Reusable components
├── web/                     # Flutter web build
├── index.html              # Main web interface
├── server.php             # PHP routing server
└── README.md              # This file
```

## API Endpoints

- `POST /api/requirements` - Submit new requirements and generate documents
- `GET /api/requirements` - Retrieve all requirements
- `GET /api/download?id={id}&type={brd|uat}&format={docx|pdf|xlsx}` - Download generated documents

## Usage

1. **Web Interface**:
   - Fill in project details, requirements description, and change requests
   - Upload supporting documents (optional)
   - Set priority level and delivery date
   - Click "Generate BRD & UAT Test Cases"
   - Download generated documents

2. **Flutter Mobile App**:
   - Navigate between Upload and Results screens
   - Use the same functionality as web interface
   - Access generated documents from Results screen

## Database Schema

The application uses three main tables:
- `requirements` - Project requirements and metadata
- `test_cases` - Generated UAT test cases
- `document_templates` - Document generation templates

## Generated Documents

### Business Requirements Document (BRD)
- Executive Summary
- Project Information
- Business Requirements
- Functional Requirements
- Non-Functional Requirements
- Assumptions and Constraints
- Acceptance Criteria
- Approval and Sign-off

### UAT Test Cases
- Positive test scenarios
- Negative test scenarios
- Test steps and expected results
- Priority levels and categories
- Status tracking fields

## Production Deployment

### Requirements
- Web server (Apache/Nginx)
- PHP 8.2+ with PDO extension
- PostgreSQL (recommended) or MySQL
- SSL certificate for HTTPS

### Steps
1. Upload files to web server
2. Configure database connection in `backend/config/database.php`
3. Set proper file permissions for uploads and exports directories
4. Configure web server to route requests through `server.php`
5. Update API URLs in frontend files to production domain

### Environment Variables
- `DATABASE_URL` - PostgreSQL connection string for production

## Troubleshooting

### Common Issues
1. **Permission Errors**: Ensure uploads/ and exports/ directories are writable
2. **Database Connection**: Check SQLite file permissions or PostgreSQL credentials
3. **File Upload Limits**: Adjust PHP `upload_max_filesize` and `post_max_size`
4. **CORS Issues**: Verify CORS headers in server.php for cross-origin requests

### Development Tips
- Check PHP error logs for backend issues
- Use browser developer tools for frontend debugging
- Monitor server.php for routing issues
- Verify file paths are correct for your system

## License

This project is created for educational and business purposes.