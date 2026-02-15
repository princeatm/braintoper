<?php
/**
 * Admin Dashboard View
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - BrainToper</title>
    <link rel="stylesheet" href="/assets/css/dashboard.css">
</head>
<body class="dashboard">
    <nav class="navbar">
        <div class="navbar-left">
            <h1>BrainToper</h1>
            <p>Administration</p>
        </div>
        <div class="navbar-right">
            <span class="user-info">Admin Panel</span>
            <a href="/auth/logout" class="btn btn-logout">Logout</a>
        </div>
    </nav>

    <div class="dashboard-container">
        <main class="dashboard-content">
            <!-- Statistics Cards -->
            <section class="section">
                <h2>System Overview</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">👥</div>
                        <div class="stat-content">
                            <div class="stat-value"><?= $stats['total_students'] ?></div>
                            <div class="stat-label">Active Students</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">👨‍🏫</div>
                        <div class="stat-content">
                            <div class="stat-value"><?= $stats['total_teachers'] ?></div>
                            <div class="stat-label">Total Teachers</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">📋</div>
                        <div class="stat-content">
                            <div class="stat-value"><?= $stats['total_exams'] ?></div>
                            <div class="stat-label">Published Exams</div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Management Sections -->
            <section class="section">
                <h2>Management</h2>
                <div class="management-grid">
                    <div class="management-card">
                        <h3>👥 Students</h3>
                        <p>Manage student accounts and reset PINs</p>
                        <button class="btn btn-primary" onclick="loadStudents()">Manage Students</button>
                    </div>
                    <div class="management-card">
                        <h3>👨‍🏫 Teachers</h3>
                        <p>Add, edit, or remove teachers</p>
                        <button class="btn btn-primary" onclick="loadTeachers()">Manage Teachers</button>
                    </div>
                    <div class="management-card">
                        <h3>🎓 Classes</h3>
                        <p>Manage class structure and groups</p>
                        <button class="btn btn-primary" onclick="alert('Coming soon')">Manage Classes</button>
                    </div>
                    <div class="management-card">
                        <h3>📊 Exams</h3>
                        <p>Monitor and manage all exams</p>
                        <button class="btn btn-primary" onclick="alert('Coming soon')">Manage Exams</button>
                    </div>
                </div>
            </section>

            <!-- Students List -->
            <section class="section" id="studentsSection" style="display:none;">
                <h2>Student Management</h2>
                <div id="studentsList" class="attempts-table"></div>
            </section>

            <!-- Teachers List -->
            <section class="section" id="teachersSection" style="display:none;">
                <h2>Teacher Management</h2>
                <div id="teachersList" class="attempts-table"></div>
            </section>
        </main>
    </div>

    <script>
        async function loadStudents() {
            try {
                const response = await fetch('/api/admin/students');
                const data = await response.json();

                if (data.success && data.students.length > 0) {
                    let html = '<table><thead><tr><th>ID</th><th>Name</th><th>User ID</th><th>Registration Date</th></tr></thead><tbody>';
                    data.students.forEach(student => {
                        html += `<tr><td>${student.id}</td><td>${student.first_name} ${student.last_name}</td><td>${student.user_id}</td><td>${new Date(student.registration_date).toLocaleDateString()}</td></tr>`;
                    });
                    html += '</tbody></table>';
                    document.getElementById('studentsList').innerHTML = html;
                    document.getElementById('studentsSection').style.display = 'block';
                }
            } catch (error) {
                alert('Error loading students');
            }
        }

        async function loadTeachers() {
            try {
                const response = await fetch('/api/admin/teachers');
                const data = await response.json();

                if (data.success && data.teachers.length > 0) {
                    let html = '<table><thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Specialization</th></tr></thead><tbody>';
                    data.teachers.forEach(teacher => {
                        html += `<tr><td>${teacher.id}</td><td>${teacher.first_name} ${teacher.last_name}</td><td>${teacher.email}</td><td>${teacher.specialization || 'N/A'}</td></tr>`;
                    });
                    html += '</tbody></table>';
                    document.getElementById('teachersList').innerHTML = html;
                    document.getElementById('teachersSection').style.display = 'block';
                }
            } catch (error) {
                alert('Error loading teachers');
            }
        }
    </script>

    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 20px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .stat-icon {
            font-size: 40px;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 12px;
            opacity: 0.9;
        }

        .management-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .management-card {
            background: white;
            border: 1px solid #eee;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
        }

        .management-card h3 {
            margin-bottom: 10px;
            color: var(--primary);
        }

        .management-card p {
            color: #666;
            font-size: 13px;
            margin-bottom: 15px;
        }
    </style>
</body>
</html>
