# Quick Start Guide

## For Developers

### 1. Local Development Setup

```bash
# Clone the repository
git clone https://github.com/your-repo/braintoper.git
cd braintoper

# Copy environment file and configure
cp .env.example .env

# Edit .env with your local database credentials
nano .env

# Run setup script
bash setup.sh

# Start PHP development server
php -S localhost:8000 -t public/
```

### 2. Access the Application

- **URL**: http://localhost:8000
- **Super Admin Code**: SUPAD-01-0001
- **PIN**: 1234

### 3. Create Test Data

```bash
# Run migrations and seeding
php database/seeds/seed.php
```

### 4. Test Login Codes

- **Super Admin**: SUPAD-01-0001
- **Admin**: AD-01-001
- **Teacher**: TEA-01-0001
- **Student**: Register with STU-XX-XXXX format

## For Administrators

### Initial Setup

1. **Install Prerequisites**
   ```bash
   sudo apt-get update
   sudo apt-get install php8.2 php8.2-fpm mysql-server nginx
   ```

2. **Configure Web Server**
   - Copy `nginx.conf` to `/etc/nginx/sites-available/braintoper`
   - Enable site: `sudo a2ensite braintoper`
   - Reload: `sudo systemctl reload nginx`

3. **Setup SSL**
   ```bash
   sudo certbot certonly --nginx -d your-domain.com
   # Update nginx.conf with certificate paths
   ```

4. **Initialize Database**
   ```bash
   # Create database and user
   mysql -u root
   CREATE DATABASE braintoper;
   CREATE USER 'braintoper'@'localhost' IDENTIFIED BY 'secure-password';
   GRANT ALL PRIVILEGES ON braintoper.* TO 'braintoper'@'localhost';
   
   # Run setup
   cd /path/to/braintoper
   bash setup.sh
   ```

5. **Set Permissions**
   ```bash
   sudo chown -R www-data:www-data /path/to/braintoper
   chmod -R 755 /path/to/braintoper/storage
   ```

### Daily Operations

**Check System Health**
```bash
bash scripts/health-check.sh
```

**Backup Database**
```bash
bash scripts/backup.sh
```

**View Logs**
```bash
tail -f storage/logs/$(date +%Y-%m-%d).log
tail -f /var/log/nginx/braintoper-access.log
```

### User Management

**Create Teacher**
- Login as Admin/Super Admin
- Generate teacher code (TEA-XX-XXXX)
- Provide code to teacher
- Teacher can login and create exams

**Create Student**
- No admin action needed
- Student registers with code (STU-XX-XXXX)
- Student sets name, class, grade, arm
- System auto-generates PIN

**Create Admin**
- Only Super Admin can create admin accounts
- Generate admin code (AD-XX-XXX)
- Provide code and PIN to admin

## For Teachers

### Creating an Exam

1. **Login** with your teacher code (TEA-XX-XXXX)
2. **Go to Dashboard** → Create New Exam
3. **Fill Exam Details**:
   - Title (e.g., "Mathematics Final Exam")
   - Subject (select from assigned subjects)
   - Class, Grade, Arm (who takes the exam)
   - Duration (in minutes)
   - Total Marks
   - Passing Marks

4. **Create Questions**:
   - Click "Add Question"
   - Enter question text
   - Enter 4 options (A, B, C, D)
   - Select correct option
   - Set marks for question

5. **Publish Exam**:
   - Review all questions
   - Click "Publish"
   - Share exam code with students

### Monitoring Exams

- **Dashboard** shows all your exams
- **Click exam** to see live progress
- **Leaderboard** shows real-time rankings
- **Results** available after all students complete

### Releasing Results

1. Go to exam details
2. Click "Release Results"
3. Students can now view their scores

## For Students

### Taking an Exam

1. **Get Exam Code** from your teacher
2. **Login** with your student code (STU-XX-XXXX)
3. **On first login**:
   - Enter your name
   - Select your Class, Grade, Arm
   - System generates your PIN
   - Write down the PIN!

4. **Enter Exam Code**:
   - Go to "Join Exam"
   - Enter exam code provided by teacher
   - Click "Start Exam"

5. **During Exam**:
   - Red bar shows timer (auto-submits when 0)
   - Left panel shows all questions:
     - Green = answered
     - Orange = skipped
     - Gray = not visited
   - Use calculator tool (non-pausing)
   - Click "Submit" when done
   - Confirm submission

6. **After Exam**:
   - Score visible (if teacher released results)
   - View attempt details
   - Download result (if enabled)

### Tips for Students

- ⏱️ Watch the timer - exam submits automatically when time ends
- 📝 Answer questions you know first, skip harder ones
- 🔢 Use the calculator for difficult computations
- ✅ Monitor the question palette to track progress
- 🚀 Don't go back - focus on one question at a time
- 💾 Answers auto-save - don't worry about losing work

## Troubleshooting

### "Database connection failed"
- Check MySQL service: `systemctl status mysql`
- Verify .env credentials match MySQL user
- Run: `bash setup.sh` to reinitialize

### "Permission denied" errors
```bash
sudo chown -R www-data:www-data /path/to/braintoper
chmod -R 755 /path/to/braintoper/storage
```

### WebSocket not connecting
- Check if Node.js server is running: `ps aux | grep node`
- Check port 8080: `netstat -an | grep 8080`
- Restart: `systemctl restart braintoper-websocket`

### Login code not accepting
- Verify format: STU/TEA/AD/SUPAD-XX-XXXX
- Ensure code is uppercase
- Check code exists in database
- PIN must be 4 digits

## Support & Documentation

- **Full Deployment Guide**: [DEPLOYMENT.md](DEPLOYMENT.md)
- **API Reference**: [API.md](API.md)
- **Project Structure**: [STRUCTURE.md](STRUCTURE.md)
- **Main README**: [../README.md](../README.md)
