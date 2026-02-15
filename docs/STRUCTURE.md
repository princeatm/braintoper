# BrainToper Project Structure

```
braintoper/
│
├── app/                          # Application code (PSR-4 compliant)
│   ├── Controllers/              # Request handlers (MVC Controllers)
│   │   ├── AuthController.php
│   │   ├── ExamController.php
│   │   ├── StudentDashboardController.php
│   │   ├── TeacherDashboardController.php
│   │   └── AdminDashboardController.php
│   │
│   ├── Models/                   # Database models (MVC Models)
│   │   ├── BaseModel.php         # Abstract base model with CRUD
│   │   ├── User.php
│   │   ├── Student.php
│   │   ├── Teacher.php
│   │   ├── Admin.php
│   │   ├── SuperAdmin.php
│   │   ├── Exam.php
│   │   ├── Question.php
│   │   ├── Option.php
│   │   ├── ExamAttempt.php
│   │   ├── ExamAnswer.php
│   │   ├── ExamResult.php
│   │   ├── AcademicGroup.php
│   │   ├── ClassModel.php
│   │   ├── Grade.php
│   │   ├── Arm.php
│   │   ├── Subject.php
│   │   ├── Notification.php
│   │   ├── AuditLog.php
│   │   └── (other models)
│   │
│   ├── Views/                    # HTML templates (MVC Views)
│   │   ├── auth/
│   │   │   ├── login.php
│   │   │   └── register-student.php
│   │   ├── exam/
│   │   │   └── take-exam.php
│   │   ├── dashboard/
│   │   │   ├── student.php
│   │   │   ├── teacher.php
│   │   │   └── admin.php
│   │   └── (other views)
│   │
│   ├── Helpers/                  # Utility classes
│   │   ├── Database.php          # PDO MySQL singleton
│   │   ├── Security.php          # Crypto, validation, CSRF
│   │   ├── Logger.php            # Centralized logging
│   │   ├── Utils.php             # General utilities
│   │   └── Migration.php         # Database schema
│   │
│   ├── Middleware/               # Middleware classes
│   │   ├── AuthMiddleware.php    # Authentication & authorization
│   │   └── CSRFMiddleware.php    # CSRF protection
│   │
│   ├── Services/                 # Business logic (optional)
│   │   └── (service classes)
│   │
│   ├── Autoloader.php            # PSR-4 autoloader
│   └── Router.php                # Simple router
│
├── config/                       # Configuration files
│   ├── app.php                   # Application config
│   ├── database.php              # Database config
│   └── (other configs)
│
├── database/                     # Database management
│   ├── migrations/               # Schema migrations
│   │   └── (migration files)
│   └── seeds/                    # Data seeders
│       └── seed.php              # Initial data
│
├── public/                       # Web root (document root)
│   ├── index.php                 # Application entry point
│   ├── .htaccess                 # Apache rewrites
│   │
│   └── assets/                   # Static files
│       ├── css/
│       │   ├── style.css         # Main stylesheet
│       │   ├── exam.css          # Exam interface styles
│       │   ├── calculator.css    # Calculator app styles
│       │   └── dashboard.css     # Dashboard styles
│       │
│       ├── js/
│       │   ├── exam.js           # Exam interface JS
│       │   ├── calculator.js     # Scientific calculator
│       │   └── (other scripts)
│       │
│       └── images/               # Static images (logo, etc)
│
├── storage/                      # Application storage
│   ├── logs/                     # Application logs
│   │   ├── yyyy-mm-dd.log
│   │   └── .gitkeep
│   ├── uploads/                  # User uploads (secure)
│   │   ├── profiles/
│   │   ├── attachments/
│   │   └── .gitkeep
│   ├── cache/                    # Application cache
│   │   ├── classes.json
│   │   ├── grades.json
│   │   └── .gitkeep
│   └── sessions/                 # Session storage (optional)
│
├── websocket/                    # WebSocket server (Node.js)
│   ├── server.js                 # Main WebSocket server
│   ├── package.json              # Node dependencies
│   └── .env                      # WebSocket config
│
├── scripts/                      # Utility scripts
│   ├── backup.sh                 # Database backup
│   ├── health-check.sh           # System health monitoring
│   └── (other scripts)
│
├── docs/                         # Documentation
│   ├── DEPLOYMENT.md             # Deployment guide
│   ├── API.md                    # API documentation
│   ├── INSTALL.sh                # Installation script
│   └── STRUCTURE.md              # This file
│
├── .env.example                  # Environment template
├── .gitignore                    # Git ignore rules
├── README.md                     # Project readme
├── composer.json                 # PHP dependencies
├── Dockerfile                    # Docker image definition
├── docker-compose.yml            # Docker orchestration
├── nginx.conf                    # Nginx config template
├── security-headers.conf         # Security headers
├── braintoper-websocket.service  # Systemd service
└── LICENSE                       # License file
```

## Directory Purposes

### app/Controllers/
Contains request handlers that process user input and coordinate with models.
**Pattern**: One controller per major feature (Auth, Exam, Dashboard)

### app/Models/
Contains database access layer using PDO prepared statements.
**Pattern**: One model per database table, extends BaseModel

### app/Views/
Contains HTML templates rendered by controllers.
**Pattern**: Minimal logic, mostly data display

### app/Helpers/
Utility classes providing reusable functionality (Database, Security, etc).
**Pattern**: Static methods or singletons

### app/Middleware/
Cross-cutting concerns like authentication and CSRF protection.
**Pattern**: Middleware classes with validate() method

### config/
Environment-specific configuration loaded from .env file.
**Pattern**: PHP arrays returning configuration

### database/
Database schema definition and initial data.
**Pattern**: SQL migrations and PHP seeders

### public/
Web-accessible directory (Apache/Nginx document root).
**Pattern**: Only index.php and static assets here

### public/assets/
Static files (CSS, JavaScript, images) served directly to browser.
**Pattern**: Organized by type (css/, js/, images/)

### storage/
Non-web-accessible data storage.
**Pattern**: logs/, uploads/, cache/ subdirectories

### websocket/
Node.js WebSocket server for real-time features.
**Pattern**: Separate Node application with package.json

### scripts/
Maintenance and deployment scripts.
**Pattern**: Bash scripts for system tasks

### docs/
Project documentation for developers and operations.
**Pattern**: Markdown files with clear structure

## File Naming Conventions

- **PHP Classes**: PascalCase (AuthController.php, BaseModel.php)
- **Functions**: camelCase (sanitizeInput(), validateEmail())
- **Config Files**: kebab-case (security-headers.conf)
- **Scripts**: kebab-case (backup.sh, health-check.sh)
- **CSS Classes**: kebab-case (.exam-timer, .question-palette)
- **JavaScript Classes**: PascalCase (ExamManager, ScientificCalculator)
- **Database Tables**: snake_case (exam_attempts, exam_results)
- **Database Columns**: snake_case (created_at, updated_at)

## Key Design Patterns

1. **MVC Architecture**: Clear separation between Controllers, Models, Views
2. **Singleton Pattern**: Database connection is a singleton
3. **Template Method**: BaseModel defines CRUD structure
4. **Middleware Pattern**: Auth and CSRF validation via middleware
5. **Repository Pattern**: Models act as repositories for data access
6. **Factory Pattern**: User model creates appropriate role instances

## Security Considerations

- All database queries use PDO prepared statements
- CSRF tokens on all state-changing operations
- Password hashing with bcrypt (cost 12)
- Secure session configuration (httpOnly, Secure, SameSite)
- Input validation and output escaping
- Sensitive files outside web root

## Performance Optimizations

- Database query optimization with indexes
- Caching of reference data (classes, grades, arms)
- Gzip compression for static assets
- AJAX for partial page updates
- WebSocket for real-time updates (reduces polling)
- Proper cache headers for static files

## Scalability Features

- Prepared statements prevent SQL injection
- Rate limiting on authentication
- Audit logging for compliance
- Transaction support for data integrity
- Database connection pooling ready
- WebSocket server can be load-balanced
