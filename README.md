# Job Portal

A comprehensive job portal platform connecting employers with job seekers. Built with PHP and MySQL, featuring a modern responsive design using Tailwind CSS.

## Features

### For Employers
- Company registration and profile management
- Post, edit, and manage job listings
- Review and manage job applications
- Dashboard with statistics and recent activities
- Company public profile page

### For Job Seekers
- Personal account creation and profile management
- Browse and search job listings
- Apply to jobs with resume and cover letter
- Track application status
- Save favorite jobs

## Project Structure

```plaintext
jobportal/
├── config/
│   └── database.php         # Database configuration
├── companies/              # Employer-related pages
│   ├── dashboard.php       # Employer dashboard
│   ├── post-job.php        # Job posting form
│   ├── applications.php    # View applications
│   ├── view-application.php # Review specific application
│   └── includes/
│       └── sidebar.php     # Employer dashboard sidebar
├── users/                  # Job seeker pages
│   ├── dashboard.php       # Job seeker dashboard
│   └── profile.php         # Profile management
├── jobs/                   # Job-related pages
│   ├── browse.php          # Browse all jobs
│   ├── search.php          # Search results
│   └── view.php            # Single job view
├── includes/
│   ├── navigation.php      # Main navigation
│   └── footer.php          # Common footer
├── uploads/                # File upload directory
│   ├── resumes/           # User resumes
│   └── logos/             # Company logos
├── index.php              # Homepage
├── login.php              # Login page
├── register.php           # Registration page
├── about.php              # About page
└── contact.php            # Contact page
```

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Modern web browser
- Internet connection (for CDN resources)

## Installation

1. **Clone the repository:**
```bash
git clone https://github.com/yourusername/jobportal.git
cd jobportal
```

2. **Set up the database:**
```bash
mysql -u root -p
source database.sql
```

3. **Configure database connection:**
Create `config/database.php` with your database credentials:
```php
<?php
$host = 'localhost';
$dbname = 'jobportal';
$username = 'your_username';
$password = 'your_password';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
```

4. **Set up file permissions:**
```bash
chmod 777 uploads/resumes
chmod 777 uploads/logos
```

## File Descriptions

### Core Files
- `index.php`: Homepage with job search and featured listings
- `login.php`: User authentication
- `register.php`: User registration with role selection
- `about.php`: Platform information
- `contact.php`: Contact form

### Employer Section
- `companies/dashboard.php`: Employer dashboard with statistics
- `companies/post-job.php`: Job posting management
- `companies/applications.php`: Application review system

### Job Seeker Section
- `users/dashboard.php`: Job seeker's personal dashboard
- `jobs/browse.php`: Job listing page
- `jobs/search.php`: Search functionality

## Database Schema

### Tables
1. **users**
   - Stores both employer and job seeker information
   - Handles user authentication
   - Manages profile data

2. **jobs**
   - Job listings
   - Company information
   - Job requirements

3. **applications**
   - Job applications
   - Application status
   - Resume storage

4. **contact_messages**
   - User inquiries
   - Contact form submissions

## Security Features

1. **Authentication**
   - Secure password hashing
   - Session management
   - Role-based access

2. **Data Protection**
   - SQL injection prevention
   - XSS protection
   - Input validation

## Usage Guide

### For Employers
1. Register as an employer
2. Complete company profile
3. Post job listings
4. Review applications
5. Manage hiring process

### For Job Seekers
1. Create an account
2. Build your profile
3. Search for jobs
4. Submit applications
5. Track status

## Troubleshooting

Common issues and solutions:

1. **Database Connection Issues**
   - Check credentials
   - Verify MySQL service
   - Check permissions

2. **Upload Problems**
   - Verify directory permissions
   - Check file size limits
   - Confirm allowed file types

## Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## License

MIT License - free to use and modify

## Support

For support, please email support@jobportal.com or create an issue in the repository.