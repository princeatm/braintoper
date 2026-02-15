<?php
/**
 * Login View
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>BrainToper - Online Exam Portal</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <h1>BrainToper</h1>
                <p>Online Exam Portal</p>
            </div>

            <form id="loginForm" class="login-form">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

                <div class="form-group">
                    <label for="loginCode">Login Code</label>
                    <input 
                        type="text" 
                        id="loginCode" 
                        name="login_code" 
                        placeholder="e.g., STU-12-3456"
                        class="form-control"
                        autocomplete="off"
                        required
                    >
                    <small class="help-text">
                        Student: STU-XX-XXXX<br>
                        Teacher: TEA-XX-XXXX<br>
                        Admin: AD-XX-XXX
                    </small>
                </div>

                <div class="form-group">
                    <label for="pin">PIN (4 digits)</label>
                    <input 
                        type="password" 
                        id="pin" 
                        name="pin" 
                        placeholder="••••"
                        class="form-control"
                        maxlength="4"
                        required
                    >
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    Sign In
                </button>

                <div id="errorMessage" class="alert alert-danger hidden"></div>
            </form>

            <div class="login-footer">
                <p>Don't have a login code? Contact your institution.</p>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const errorDiv = document.getElementById('errorMessage');
            errorDiv.classList.add('hidden');

            try {
                const response = await fetch('/auth/login', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    if (data.action === 'register_student') {
                        window.location.href = data.redirect;
                    } else {
                        window.location.href = data.redirect || '/dashboard/student';
                    }
                } else if (data.error) {
                    errorDiv.textContent = data.error;
                    errorDiv.classList.remove('hidden');
                }
            } catch (error) {
                errorDiv.textContent = 'An error occurred. Please try again.';
                errorDiv.classList.remove('hidden');
            }
        });

        // Auto-format login code
        document.getElementById('loginCode').addEventListener('input', function(e) {
            let value = e.target.value.toUpperCase().replace(/[^A-Z0-9-]/g, '');
            e.target.value = value;
        });
    </script>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 20px;
        }

        .login-box {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 20px;
            text-align: center;
        }

        .login-header h1 {
            font-size: 32px;
            margin-bottom: 5px;
            font-weight: 700;
        }

        .login-header p {
            font-size: 14px;
            opacity: 0.9;
        }

        .login-form {
            padding: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
            font-size: 14px;
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .help-text {
            display: block;
            margin-top: 8px;
            font-size: 12px;
            color: #666;
            line-height: 1.6;
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
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-block {
            width: 100%;
        }

        .alert {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 16px;
            font-size: 14px;
        }

        .alert-danger {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }

        .hidden {
            display: none;
        }

        .login-footer {
            background: #f5f5f5;
            padding: 20px;
            text-align: center;
            border-top: 1px solid #eee;
        }

        .login-footer p {
            font-size: 13px;
            color: #666;
        }
    </style>
</body>
</html>
