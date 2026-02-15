<?php
/**
 * Database Seeder
 * Initial setup with JSS/SSS, Grades 1-3, Arms A-C
 */

require_once __DIR__ . '/../app/Autoloader.php';
\App\Autoloader::register();

use App\Helpers\Database;
use App\Helpers\Migration;
use App\Models\ClassModel;
use App\Models\Grade;
use App\Models\Arm;
use App\Models\Subject;
use App\Models\User;

// Load environment
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env');
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || $line[0] === '#') continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

// Initialize database
$dbConfig = include __DIR__ . '/../config/database.php';
Database::init($dbConfig);
$pdo = Database::connect();

echo "🚀 Starting database seeding...\n\n";

try {
    // Run migrations
    echo "📝 Running migrations...\n";
    $migration = new Migration($pdo);
    $migration->runAll();
    echo "✅ Migrations completed\n\n";

    // Seed Classes
    echo "📚 Seeding classes...\n";
    $classModel = new ClassModel();
    $classes = [
        ['name' => 'JSS', 'code' => 'JSS'],
        ['name' => 'SSS', 'code' => 'SSS'],
    ];
    
    foreach ($classes as $class) {
        if (!$classModel->findBy(['code' => $class['code']])) {
            $classModel->insert($class);
            echo "  ✓ Created class: {$class['name']}\n";
        }
    }

    // Seed Grades
    echo "\n📊 Seeding grades...\n";
    $gradeModel = new Grade();
    $grades = [
        ['name' => '1', 'code' => 'GRADE1', 'level' => 1],
        ['name' => '2', 'code' => 'GRADE2', 'level' => 2],
        ['name' => '3', 'code' => 'GRADE3', 'level' => 3],
    ];
    
    foreach ($grades as $grade) {
        if (!$gradeModel->findBy(['code' => $grade['code']])) {
            $gradeModel->insert($grade);
            echo "  ✓ Created grade: {$grade['name']}\n";
        }
    }

    // Seed Arms
    echo "\n🎯 Seeding arms...\n";
    $armModel = new Arm();
    $arms = [
        ['name' => 'A', 'code' => 'ARM_A'],
        ['name' => 'B', 'code' => 'ARM_B'],
        ['name' => 'C', 'code' => 'ARM_C'],
    ];
    
    foreach ($arms as $arm) {
        if (!$armModel->findBy(['code' => $arm['code']])) {
            $armModel->insert($arm);
            echo "  ✓ Created arm: {$arm['name']}\n";
        }
    }

    // Create academic groups
    echo "\n👥 Creating academic groups...\n";
    $classRecords = $classModel->all();
    $gradeRecords = $gradeModel->all();
    $armRecords = $armModel->all();
    
    foreach ($classRecords as $class) {
        foreach ($gradeRecords as $grade) {
            foreach ($armRecords as $arm) {
                $code = $class['name'] . $grade['name'] . $arm['name'];
                $existing = $pdo->prepare("SELECT id FROM academic_groups WHERE code = ?");
                $existing->execute([$code]);
                
                if (!$existing->fetch()) {
                    $stmt = $pdo->prepare("
                        INSERT INTO academic_groups (class_id, grade_id, arm_id, code)
                        VALUES (?, ?, ?, ?)
                    ");
                    $stmt->execute([$class['id'], $grade['id'], $arm['id'], $code]);
                    echo "  ✓ Created group: {$code}\n";
                }
            }
        }
    }

    // Seed Subjects
    echo "\n📖 Seeding subjects...\n";
    $subjectModel = new Subject();
    $subjects = [
        ['name' => 'Mathematics', 'code' => 'MATH'],
        ['name' => 'English Language', 'code' => 'ENGLISH'],
        ['name' => 'Physics', 'code' => 'PHYSICS'],
        ['name' => 'Chemistry', 'code' => 'CHEMISTRY'],
        ['name' => 'Biology', 'code' => 'BIOLOGY'],
        ['name' => 'History', 'code' => 'HISTORY'],
        ['name' => 'Geography', 'code' => 'GEOGRAPHY'],
        ['name' => 'Civic Education', 'code' => 'CIVICS'],
    ];
    
    foreach ($subjects as $subject) {
        if (!$subjectModel->findBy(['code' => $subject['code']])) {
            $subjectModel->insert($subject);
            echo "  ✓ Created subject: {$subject['name']}\n";
        }
    }

    // Seed Super Admin
    echo "\n🔐 Creating super admin account...\n";
    $userModel = new User();
    
    if (!$userModel->exists('SUPAD-01-0001')) {
        $superAdminPin = '1234';
        $pinHash = password_hash($superAdminPin, PASSWORD_BCRYPT, ['cost' => 12]);
        
        $userId = $userModel->create('SUPAD-01-0001', 'superadmin', $pinHash);
        
        $stmt = $pdo->prepare("
            INSERT INTO super_admins (user_id, first_name, last_name, email, phone)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$userId, 'Super', 'Admin', 'admin@braintoper.com', '+1234567890']);
        
        echo "  ✓ Super Admin Created\n";
        echo "    Login Code: SUPAD-01-0001\n";
        echo "    PIN: {$superAdminPin}\n";
    }

    // Seed Test Admin
    echo "\n👤 Creating test admin account...\n";
    if (!$userModel->exists('AD-01-001')) {
        $adminPin = '1234';
        $pinHash = password_hash($adminPin, PASSWORD_BCRYPT, ['cost' => 12]);
        
        $userId = $userModel->create('AD-01-001', 'admin', $pinHash);
        
        $stmt = $pdo->prepare("
            INSERT INTO admins (user_id, first_name, last_name, email, phone)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$userId, 'Admin', 'User', 'admin@school.edu', '+9876543210']);
        
        echo "  ✓ Admin Created\n";
        echo "    Login Code: AD-01-001\n";
        echo "    PIN: {$adminPin}\n";
    }

    // Seed Test Teacher
    echo "\n👨‍🏫 Creating test teacher account...\n";
    if (!$userModel->exists('TEA-01-0001')) {
        $teacherPin = '1234';
        $pinHash = password_hash($teacherPin, PASSWORD_BCRYPT, ['cost' => 12]);
        
        $userId = $userModel->create('TEA-01-0001', 'teacher', $pinHash);
        
        $stmt = $pdo->prepare("
            INSERT INTO teachers (user_id, first_name, last_name, email, specialization, is_active)
            VALUES (?, ?, ?, ?, ?, TRUE)
        ");
        $stmt->execute([$userId, 'John', 'Doe', 'teacher@school.edu', 'Mathematics']);
        
        // Assign subjects
        $subjects = $pdo->query("SELECT id FROM subjects LIMIT 3")->fetchAll();
        $teacherId = $pdo->query("SELECT id FROM teachers WHERE user_id = $userId")->fetch()['id'];
        
        foreach ($subjects as $subject) {
            $stmt = $pdo->prepare("INSERT IGNORE INTO teacher_subjects (teacher_id, subject_id) VALUES (?, ?)");
            $stmt->execute([$teacherId, $subject['id']]);
        }
        
        echo "  ✓ Teacher Created\n";
        echo "    Login Code: TEA-01-0001\n";
        echo "    PIN: {$teacherPin}\n";
    }

    echo "\n✅ Database seeding completed successfully!\n";
    echo "\n📋 Login Credentials:\n";
    echo "  Super Admin: SUPAD-01-0001 / 1234\n";
    echo "  Admin: AD-01-001 / 1234\n";
    echo "  Teacher: TEA-01-0001 / 1234\n";
    echo "  Student: STU-XX-XXXX (auto-registered on first login)\n";

} catch (Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
    echo $e->getTraceAsString();
    exit(1);
}
