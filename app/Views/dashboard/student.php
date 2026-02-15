<?php
/**
 * Student Dashboard View
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - BrainToper</title>
    <link rel="stylesheet" href="/assets/css/dashboard.css">
</head>
<body class="dashboard">
    <nav class="navbar">
        <div class="navbar-left">
            <h1>BrainToper</h1>
        </div>
        <div class="navbar-right">
            <span class="user-info">
                <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?>
                <small>(<?= htmlspecialchars($student['academic_group_code']) ?>)</small>
            </span>
            <a href="/auth/logout" class="btn btn-logout">Logout</a>
        </div>
    </nav>

    <div class="dashboard-container">
        <main class="dashboard-content">
            <!-- Available Exams -->
            <section class="section">
                <h2>Available Exams</h2>
                <?php if (empty($availableExams)): ?>
                    <div class="empty-state">
                        <p>No exams available at this time.</p>
                    </div>
                <?php else: ?>
                    <div class="exams-grid">
                        <?php foreach ($availableExams as $exam): ?>
                            <div class="exam-card">
                                <div class="exam-card-header">
                                    <h3><?= htmlspecialchars($exam['title']) ?></h3>
                                    <span class="subject-badge"><?= htmlspecialchars($exam['subject_name']) ?></span>
                                </div>
                                <div class="exam-card-body">
                                    <p><?= htmlspecialchars(substr($exam['description'], 0, 100)) ?></p>
                                    <div class="exam-details">
                                        <div class="detail">
                                            <span class="label">Duration:</span>
                                            <span class="value"><?= $exam['duration_minutes'] ?> min</span>
                                        </div>
                                        <div class="detail">
                                            <span class="label">Total Marks:</span>
                                            <span class="value"><?= $exam['total_marks'] ?></span>
                                        </div>
                                        <div class="detail">
                                            <span class="label">Passing Marks:</span>
                                            <span class="value"><?= $exam['passing_marks'] ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="exam-card-footer">
                                    <form method="POST" action="/exam/start" class="start-exam-form">
                                        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                        <input type="hidden" name="exam_code" value="<?= htmlspecialchars($exam['exam_code']) ?>">
                                        <button type="submit" class="btn btn-primary btn-full">
                                            Start Exam
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>

            <!-- Recent Exam Attempts -->
            <section class="section">
                <h2>Recent Attempts</h2>
                <?php if (empty($recentAttempts)): ?>
                    <div class="empty-state">
                        <p>No exam attempts yet.</p>
                    </div>
                <?php else: ?>
                    <div class="attempts-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Exam</th>
                                    <th>Subject</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Score</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentAttempts as $attempt): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($attempt['title']) ?></td>
                                        <td><?= htmlspecialchars($attempt['subject_name']) ?></td>
                                        <td><?= date('M d, Y', strtotime($attempt['started_at'])) ?></td>
                                        <td>
                                            <?php if ($attempt['is_graded']): ?>
                                                <span class="badge badge-success">Graded</span>
                                            <?php else: ?>
                                                <span class="badge badge-pending">Pending</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($attempt['is_graded']): ?>
                                                <strong><?= $attempt['obtained_marks'] ?>/<?= $attempt['total_marks'] ?></strong>
                                                <small>(<?= round($attempt['percentage']) ?>%)</small>
                                            <?php else: ?>
                                                —
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($attempt['is_graded']): ?>
                                                <button class="btn btn-sm btn-secondary" onclick="viewResult(<?= $attempt['id'] ?>)">
                                                    View
                                                </button>
                                            <?php endif; ?>
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
        function viewResult(attemptId) {
            fetch(`/api/student/exam-result?attempt_id=${attemptId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert(`Score: ${data.result.obtained_marks}/${data.result.total_marks}\nPercentage: ${data.result.percentage}%\nGrade: ${data.result.grade}`);
                    }
                })
                .catch(() => alert('Failed to load result'));
        }
    </script>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f5f5;
        }

        .navbar {
            background: white;
            border-bottom: 1px solid #eee;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .navbar h1 {
            color: #667eea;
            font-size: 24px;
        }

        .user-info {
            margin-right: 20px;
            color: #666;
        }

        .user-info small {
            display: block;
            font-size: 12px;
        }

        .dashboard-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .section {
            background: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .section h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 20px;
        }

        .exams-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .exam-card {
            border: 1px solid #eee;
            border-radius: 8px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
        }

        .exam-card:hover {
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .exam-card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: start;
        }

        .exam-card-header h3 {
            flex: 1;
            font-size: 16px;
        }

        .subject-badge {
            background: rgba(255, 255, 255, 0.3);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            white-space: nowrap;
        }

        .exam-card-body {
            padding: 20px;
            flex: 1;
        }

        .exam-card-body p {
            color: #666;
            font-size: 14px;
            margin-bottom: 15px;
            line-height: 1.5;
        }

        .exam-details {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            font-size: 13px;
        }

        .detail {
            display: flex;
            justify-content: space-between;
        }

        .detail .label {
            color: #999;
        }

        .detail .value {
            color: #333;
            font-weight: 600;
        }

        .exam-card-footer {
            padding: 0 20px 20px;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .btn-full {
            width: 100%;
        }

        .attempts-table {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background: #f9f9f9;
            font-weight: 600;
            color: #333;
        }

        td {
            color: #666;
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-success {
            background: #efe;
            color: #393;
        }

        .badge-pending {
            background: #ffe;
            color: #993;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        @media (max-width: 768px) {
            .exams-grid {
                grid-template-columns: 1fr;
            }

            .dashboard-container {
                margin: 20px auto;
            }

            table {
                font-size: 13px;
            }

            th, td {
                padding: 10px;
            }
        }
    </style>
</body>
</html>
