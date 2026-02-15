# BrainToper - Online Exam Portal

A production-ready, fully functional online examination system built with PHP 8.2, MySQL 8, and modern web technologies.

## Features

### Core Features
- 🎯 **Secure Authentication**: Role-based login with auto-detection from login code format
- 📝 **Exam Engine**: Full-featured exam taking with real-time features
- 📊 **Real-time Monitoring**: LiveWebSocket updates for exam progress and leaderboards
- 🏆 **Leaderboard**: Real-time leaderboard with filtering by academic groups
- 🔐 **Security**: CSRF protection, prepared statements, secure session handling
- 📱 **Mobile-First**: Fully responsive design
- 🎨 **Dark Theme**: Modern quiz show interface

### Student Features
- Automatic profile creation on first login
- Self-organization into academic groups (Class/Grade/Arm)
- Join exams with exam codes
- Real-time exam experience with:
  - Countdown timer with auto-submit
  - Question palette showing answered/skipped/remaining
  - Next/Previous question navigation
  - Skip question functionality
  - Scientific calculator (non-pausing)
- Focus loss detection and logging
- Instant answer auto-save via AJAX
- No score display until results released

### Teacher Features
- Create and publish exams by class/grade/arm
- Question management with image uploads
- Create objective questions with A-B-C-D options
- Randomize questions and options per student
- Exam code generation
- Publish/unpublish exams
- Live monitoring of student progress
- Leaderboard with real-time updates
- Download results (individual/class/arm)
- Release results to students
- Statistics and charts

### Admin Features
- Student management (add/edit/delete)
- Teacher management (add/edit/delete)
- PIN reset functionality
- Class/Grade/Arm configuration
- Live exam monitoring
- System-wide statistics
- Results management

### Super Admin Features
- Full system access
- Admin account creation
- Global settings management
- Complete audit log access
- Credential visibility
- Class hierarchy management

## Technical Requirements

- **PHP**: 8.2 with PDO MySQL extension
- **MySQL**: 8.0 or higher
- **Web Server**: Apache 2.4+ or Nginx
- **Node.js**: 14+ (for WebSocket server, optional)
- **SSL/TLS**: Required for production

## Installation

### Quick Start

1. **Clone Repository**
   ```bash
   git clone https://github.com/princeatm/braintoper.git
   cd braintoper
   ```

2. **Setup Environment**
   ```bash
   cp .env.example .env
   # Edit .env with your database credentials
   ```

3. **Initialize Database**
   ```bash
   php database/seeds/seed.php
   ```

4. **Configure Web Server**
   - See `docs/DEPLOYMENT.md` for Apache/Nginx configuration
   - Enable mod_rewrite for Apache
   - Point document root to `public/` directory

5. **Set Permissions**
   ```bash
   chmod -R 755 storage/
   chown -R www-data:www-data storage/
   ```

6. **Access Application**
   - Visit `https://your-domain.com`
   - Use default credentials from seed data

### Detailed Setup

See [DEPLOYMENT.md](docs/DEPLOYMENT.md) for comprehensive production deployment guide.

## Default Credentials (Change in Production)

After running seed script:
- **Super Admin**: SUPAD-01-0001 / 1234
- **Admin**: AD-01-001 / 1234
- **Teacher**: TEA-01-0001 / 1234
- **Student**: Register on first login with code STU-XX-XXXX

## Login Code Formats

- **Student**: `STU-XX-XXXX` (e.g., STU-12-3456)
- **Teacher**: `TEA-XX-XXXX` (e.g., TEA-01-0001)
- **Admin**: `AD-XX-XXX` (e.g., AD-01-001)
- **Super Admin**: `SUPAD-XX-XXXX` (e.g., SUPAD-01-0001)

## Architecture

### MVC Structure
```
app/
├── Controllers/    # Request handlers
├── Models/        # Database models
├── Views/         # HTML templates
├── Services/      # Business logic
├── Middleware/    # Auth, CSRF, etc.
└── Helpers/       # Utilities

public/
├── index.php      # Entry point
└── assets/
    ├── css/       # Stylesheets
    └── js/        # JavaScript

database/
├── migrations/    # Schema setup
└── seeds/         # Initial data
```

### Key Technologies
- **Backend**: PHP 8.2 with prepared statements (PDO)
- **Database**: MySQL 8 with full normalization
- **Frontend**: HTML5, CSS3, Vanilla JavaScript
- **Real-time**: WebSocket (Node.js)
- **Session**: Secure httpOnly cookies with SameSite=Strict
- **Security**: CSRF tokens, rate limiting, input validation

## Database Schema

The system includes 18 interconnected tables covering:
- User authentication and roles
- Academic structure (Classes, Grades, Arms, Academic Groups)
- Exam management
- Question and answer tracking
- Results and statistics
- Audit logging
- Notifications

Full schema is created automatically via migrations.

## API Endpoints

### Authentication
- `POST /auth/login` - User login
- `POST /auth/check-session` - Verify session
- `GET /auth/logout` - Logout

### Exams
- `POST /exam/start` - Begin exam
- `POST /exam/save-answer` - Auto-save answer
- `POST /exam/submit` - Submit exam
- `POST /exam/auto-submit` - Auto-submit on timer

### Dashboard
- `GET /dashboard/student` - Student dashboard
- `GET /dashboard/teacher` - Teacher dashboard
- `GET /dashboard/admin` - Admin dashboard
- `GET /api/student/exam-result` - Get result

See [API.md](docs/API.md) for complete API documentation.

## Security Features

✅ **All Implemented:**
- PDO prepared statements for all database queries
- CSRF protection on all POST requests
- Secure session handling (httpOnly, Secure, SameSite=Strict)
- Password hashing with bcrypt (cost=12)
- Input sanitization and validation
- Rate limiting on authentication
- Audit logging of all actions
- Image upload validation
- Secure file storage outside webroot
- No script execution in upload directories
- Security headers (HSTS, CSP, X-Frame-Options)

## Performance

- Database query optimization with proper indexing
- Cached class/grade/arm lists
- Gzip compression for static assets
- AJAX-based answer auto-save (no page refresh)
- WebSocket for real-time updates (fallback polling)
- Efficient question randomization per student

## Deployment

### Development
```bash
cp .env.example .env
# Edit for local development
php -S localhost:8000 -t public/
```

### Production
See comprehensive [DEPLOYMENT.md](docs/DEPLOYMENT.md) guide covering:
- SSL/TLS setup (Let's Encrypt)
- Apache/Nginx configuration
- PHP-FPM tuning
- MySQL optimization
- WebSocket server setup
- Security hardening
- Monitoring and backup strategies

## Troubleshooting

### Database Connection Fails
- Verify MySQL service is running
- Check database credentials in `.env`
- Ensure user has proper permissions

### File Upload Issues
- Check `storage/uploads/` permissions (755)
- Verify PHP upload limits in `php.ini`
- Check disk space availability

### WebSocket Connection Fails
- Verify Node.js WebSocket server is running
- Check firewall allows port 8080 (or configured port)
- Verify WebSocket protocol in `.env`

For more help, see [DEPLOYMENT.md](docs/DEPLOYMENT.md#troubleshooting)

## Directory Structure

```
braintoper/
├── app/                    # Application code
│   ├── Controllers/        # MVC controllers
│   ├── Models/            # Database models
│   ├── Views/             # HTML templates
│   ├── Services/          # Business logic
│   ├── Middleware/        # Auth, CSRF middleware
│   ├── Helpers/           # Utility functions
│   ├── Autoloader.php     # PSR-4 autoloader
│   └── Router.php         # Request router
├── database/
│   ├── migrations/        # Schema migrations
│   └── seeds/             # Data seeders
├── public/                # Web root
│   ├── index.php          # Entry point
│   └── assets/            # CSS, JS, images
├── storage/
│   ├── logs/              # Application logs
│   ├── uploads/           # File uploads
│   └── cache/             # Cache files
├── config/                # Configuration files
├── websocket/             # WebSocket server
├── docs/                  # Documentation
├── .env.example           # Environment template
└── README.md              # This file
```

## Contributing

Guidelines for contributing:
1. Follow PSR-12 code style
2. Use prepared statements for all database access
3. Add comprehensive comments
4. Test thoroughly before submitting

## License

This project is proprietary software. All rights reserved.

## Support

For support:
- Check [DEPLOYMENT.md](docs/DEPLOYMENT.md) for deployment issues
- Review [API.md](docs/API.md) for API reference
- Check logs in `storage/logs/`

## Version

**Current Version**: 1.0.0  
**Last Updated**: February 2026  
**Status**: Production Ready

---

Built with security, scalability, and user experience in mind for real school deployments.
