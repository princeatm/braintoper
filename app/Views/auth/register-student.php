<?php
/**
 * Student Registration View
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration - BrainToper</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="registration-page">
    <div class="registration-container">
        <div class="registration-box">
            <div class="registration-header">
                <h1>Student Registration</h1>
                <p>Complete your profile</p>
            </div>

            <form id="registrationForm" class="registration-form">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="hidden" name="login_code" value="<?= htmlspecialchars($loginCode) ?>">

                <div class="form-group">
                    <label for="firstName">First Name</label>
                    <input 
                        type="text" 
                        id="firstName" 
                        name="first_name" 
                        class="form-control"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="lastName">Last Name</label>
                    <input 
                        type="text" 
                        id="lastName" 
                        name="last_name" 
                        class="form-control"
                        required
                    >
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="classId">Class</label>
                        <select id="classId" name="class_id" class="form-control" required>
                            <option value="">Select Class</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?= $class['id'] ?>"><?= htmlspecialchars($class['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="gradeId">Grade</label>
                        <select id="gradeId" name="grade_id" class="form-control" required>
                            <option value="">Select Grade</option>
                            <?php foreach ($grades as $grade): ?>
                                <option value="<?= $grade['id'] ?>"><?= htmlspecialchars($grade['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="armId">Arm</label>
                        <select id="armId" name="arm_id" class="form-control" required>
                            <option value="">Select Arm</option>
                            <?php foreach ($arms as $arm): ?>
                                <option value="<?= $arm['id'] ?>"><?= htmlspecialchars($arm['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    Complete Registration
                </button>

                <div id="successMessage" class="alert alert-success hidden">
                    <h4>Registration Successful!</h4>
                    <p>Your 4-digit PIN is: <strong id="pinDisplay"></strong></p>
                    <p>Save this PIN carefully. You will use it to log in.</p>
                </div>

                <div id="errorMessage" class="alert alert-danger hidden"></div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('registrationForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const errorDiv = document.getElementById('errorMessage');
            const successDiv = document.getElementById('successMessage');

            errorDiv.classList.add('hidden');
            successDiv.classList.add('hidden');

            try {
                const response = await fetch('/auth/register-student', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    document.getElementById('pinDisplay').textContent = data.pin;
                    successDiv.classList.remove('hidden');
                    document.getElementById('registrationForm').style.display = 'none';
                    
                    setTimeout(() => {
                        window.location.href = '/';
                    }, 5000);
                } else if (data.error) {
                    errorDiv.textContent = data.error;
                    errorDiv.classList.remove('hidden');
                }
            } catch (error) {
                errorDiv.textContent = 'An error occurred. Please try again.';
                errorDiv.classList.remove('hidden');
            }
        });
    </script>

    <style>
        .registration-page {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .registration-container {
            width: 100%;
            max-width: 600px;
        }

        .registration-box {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        .registration-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .registration-header h1 {
            font-size: 28px;
            margin-bottom: 5px;
        }

        .registration-form {
            padding: 30px;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .alert-success {
            background: #efe;
            color: #3c3;
            border: 1px solid #cfc;
            padding: 20px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .alert-success h4 {
            margin-bottom: 10px;
        }

        .alert-success p {
            margin: 5px 0;
            font-size: 14px;
        }

        @media (max-width: 600px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>
</html>
