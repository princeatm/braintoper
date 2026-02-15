<?php
/**
 * Teacher Dashboard View
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - BrainToper</title>
    <link rel="stylesheet" href="/assets/css/dashboard.css">
</head>
<body class="dashboard">
    <nav class="navbar">
        <div class="navbar-left">
            <h1>BrainToper</h1>
            <p>Teacher Dashboard</p>
        </div>
        <div class="navbar-right">
            <span class="user-info">
                <?= htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']) ?>
                <small><?= htmlspecialchars($teacher['specialization']) ?></small>
            </span>
            <a href="/auth/logout" class="btn btn-logout">Logout</a>
        </div>
    </nav>

    <div class="dashboard-container">
        <main class="dashboard-content">
            <!-- Quick Actions -->
            <section class="section">
                <h2>Quick Actions</h2>
                <div class="actions-grid">
                    <a href="/exam/create" class="action-card">
                        <div class="action-icon">➕</div>
                        <div class="action-title">Create New Exam</div>
                        <div class="action-desc">Create and publish exam</div>
                    </a>
                    <a href="#manage-subjects" class="action-card">
                        <div class="action-icon">📚</div>
                        <div class="action-title">My Subjects</div>
                        <div class="action-desc"><?= count($subjects) ?> subjects</div>
                    </a>
                    <a href="#my-exams" class="action-card">
                        <div class="action-icon">📋</div>
                        <div class="action-title">My Exams</div>
                        <div class="action-desc"><?= count($exams) ?> exams</div>
                    </a>
                    <a href="#analytics" class="action-card">
                        <div class="action-icon">📊</div>
                        <div class="action-title">Analytics</div>
                        <div class="action-desc">View statistics</div>
                    </a>
                </div>
            </section>

            <!-- My Subjects -->
            <section class="section" id="manage-subjects">
                <h2>My Subjects</h2>
                <?php if (empty($subjects)): ?>
                    <div class="empty-state">
                        <p>No subjects assigned yet. Contact administrator.</p>
                    </div>
                <?php else: ?>
                    <div class="subjects-grid">
                        <?php foreach ($subjects as $subject): ?>
                            <div class="subject-card">
                                <h3><?= htmlspecialchars($subject['name']) ?></h3>
                                <p><?= htmlspecialchars($subject['code']) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>

            <!-- My Exams -->
            <section class="section" id="my-exams">
                <h2>My Exams</h2>
                <?php if (empty($exams)): ?>
                    <div class="empty-state">
                        <p>No exams created yet. <a href="/exam/create">Create one now</a></p>
                    </div>
                <?php else: ?>
                    <div class="exams-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Exam</th>
                                    <th>Subject</th>
                                    <th>Class</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($exams as $exam): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($exam['title']) ?></td>
                                        <td><?= htmlspecialchars($exam['subject_name']) ?></td>
                                        <td><?= htmlspecialchars($exam['class_name']) ?></td>
                                        <td>
                                            <span class="badge badge-<?= $exam['status'] ?>">
                                                <?= ucfirst($exam['status']) ?>
                                            </span>
                                        </td>
                                        <td><?= date('M d, Y', strtotime($exam['created_at'])) ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-secondary" onclick="viewLeaderboard(<?= $exam['id'] ?>)">
                                                Leaderboard
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>
        </main>
    </div>

    <script>
        const csrfToken = '<?= htmlspecialchars($csrfToken) ?>';

        function viewLeaderboard(examId) {
            fetch(`/api/teacher/leaderboard?exam_id=${examId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.leaderboard.length > 0) {
                        let html = 'Leaderboard:\n\n';
                        data.leaderboard.forEach((student, idx) => {
                            html += `${idx + 1}. ${student.first_name} ${student.last_name} - ${student.obtained_marks} marks\n`;
                        });
                        alert(html);
                    } else {
                        alert('No results yet');
                    }
                });
        }
    </script>

    <style>
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .action-card {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 20px;
            border-radius: 12px;
            text-decoration: none;
            transition: all 0.3s ease;
            text-align: center;
        }

        .action-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.3);
        }

        .action-icon {
            font-size: 32px;
            margin-bottom: 10px;
        }

        .action-title {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .action-desc {
            font-size: 12px;
            opacity: 0.9;
        }

        .subjects-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
        }

        .subject-card {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
            border: 1px solid var(--primary);
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }

        .subject-card h3 {
            color: var(--primary);
            margin-bottom: 5px;
        }

        .subject-card p {
            color: #666;
            font-size: 12px;
        }

        .badge-draft {
            background: #fff3cd;
            color: #856404;
        }

        .badge-published {
            background: #d4edda;
            color: #155724;
        }

        .badge-archived {
            background: #e2e3e5;
            color: #383d41;
        }
    </style>
</body>
</html>
