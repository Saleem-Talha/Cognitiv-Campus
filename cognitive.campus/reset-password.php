<?php
// reset-password.php
require_once 'includes/db-connect.php';
require_once 'includes/rate-limit.php';



// Verify user has completed OTP verification
if (!isset($_SESSION['password_reset_verified']) || !isset($_SESSION['reset_email'])) {
    header("Location: forget_password.php");
    exit();
}

$email = $_SESSION['reset_email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate password
    $has_lowercase = preg_match('/[a-z]/', $password);
    $has_special = preg_match('/[\d\s\W]/', $password); // number, symbol, or whitespace
    
    if (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } elseif (!$has_lowercase) {
        $error = "Password must contain at least one lowercase character.";
    } elseif (!$has_special) {
        $error = "Password must contain at least one number, symbol, or whitespace character.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check rate limiting for password reset attempts
        $rateLimit = checkRateLimit($email, 'password_reset', $db);
        if (!$rateLimit['allowed']) {
            $error = $rateLimit['message'];
        } else {
            // Hash new password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Update password and clear reset tokens
            $stmt = $db->prepare("UPDATE users SET 
                password = ?,
                reset_token = NULL,
                reset_token_expiry = NULL,
                reset_otp = NULL
                WHERE email = ?");
            $stmt->bind_param("ss", $hashed_password, $email);
            
            if ($stmt->execute()) {
                // Clear session variables
                unset($_SESSION['reset_email']);
                unset($_SESSION['reset_token']);
                unset($_SESSION['password_reset_verified']);
                
                // Set success message and redirect
                $_SESSION['password_reset_success'] = true;
                header("Location: login.php");
                exit();
            } else {
                $error = "An error occurred. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en" class="light-style" dir="ltr" data-theme="theme-default">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>Reset Password - Cognitive Campus</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/img/favicon.ico" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet" />

    <!-- Core CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css" rel="stylesheet">

    <style>
        body {
            background: #f5f5f9;
            font-family: 'Public Sans', sans-serif;
        }
        .authentication-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .authentication-inner {
            max-width: 400px;
            width: 100%;
        }
        .card {
            border: 0;
            box-shadow: 0 2px 6px 0 rgba(67, 89, 113, 0.12);
            border-radius: 8px;
        }
        .app-brand {
            margin-bottom: 1rem;
        }
        .app-brand-logo {
            max-width: 120px;
            height: auto;
        }
        .app-brand-text {
            font-size: 1.75rem;
            letter-spacing: -0.5px;
            font-weight: 600;
            color: #566a7f;
        }
        .btn-primary {
            background-color: #696cff;
            border-color: #696cff;
            box-shadow: 0 0.125rem 0.25rem 0 rgba(105, 108, 255, 0.4);
        }
        .btn-primary:hover {
            background-color: #6062e8;
            border-color: #6062e8;
        }
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #697a8d;
        }
        .form-password-toggle .form-control {
            padding-right: 2.5rem;
        }
        .password-requirements {
            font-size: 0.875rem;
            color: #697a8d;
            margin-top: 0.5rem;
        }
        .spinner-border {
            width: 1rem;
            height: 1rem;
        }
        .requirement-item {
            position: relative;
            padding-left: 1.5rem;
        }
        .requirement-item.valid:before {
            content: '✓';
            color: #71dd37;
            position: absolute;
            left: 0;
        }
        .requirement-item.invalid:before {
            content: '×';
            color: #ff3e1d;
            position: absolute;
            left: 0;
        }
        .text-primary {
            color: #696cff !important;
        }
    </style>
</head>

<body>
    <div class="authentication-wrapper">
        <div class="authentication-inner">
            <div class="card">
                <div class="card-body p-4">
                    <!-- Logo -->
                    <div class="app-brand justify-content-center text-center mb-4">
                        <img src="img/Logo/Cognitive Campus Logo.png" alt="Cognitive Campus" class="app-brand-logo rounded-circle">
                    </div>

                    <!-- Title -->
                    <h4 class="mb-2 text-center">Reset Password</h4>
                    <p class="mb-4 text-center text-muted">
                        Create a new password for your account
                    </p>

                    <!-- Error Messages -->
                    <?php if(isset($error)): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Form -->
                    <form id="formAuthentication" class="mb-3" action="" method="POST">
                        <div class="mb-3 form-password-toggle">
                            <label for="password" class="form-label">New Password</label>
                            <div class="position-relative">
                                <input type="password" 
                                       id="password" 
                                       class="form-control" 
                                       name="password" 
                                       placeholder="············"
                                       required>
                                <i class="bx bx-hide password-toggle" 
                                   onclick="togglePassword('password')"></i>
                            </div>
                            <div class="password-requirements mt-3">
                                <span>Password Requirements:</span>
                                <ul class="ps-3 mb-0">
                                    <li class="mb-1 requirement-item" id="length">Minimum 8 characters long - the more, the better</li>
                                    <li class="mb-1 requirement-item" id="lowercase">At least one lowercase character</li>
                                    <li class="requirement-item" id="special">At least one number, symbol, or whitespace character</li>
                                </ul>
                            </div>
                        </div>

                        <div class="mb-3 form-password-toggle">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <div class="position-relative">
                                <input type="password" 
                                       id="confirm_password" 
                                       class="form-control" 
                                       name="confirm_password" 
                                       placeholder="············"
                                       required>
                                <i class="bx bx-hide password-toggle" 
                                   onclick="togglePassword('confirm_password')"></i>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary d-grid w-100 mb-3" id="submitBtn">
                            Set New Password
                        </button>

                        <div class="text-center">
                            <a href="login.php" class="d-flex align-items-center justify-content-center text-decoration-none text-primary">
                                <i class="bx bx-chevron-left me-1 scaleX-n1-rtl"></i>
                                Back to login
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Core JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Toggle password visibility
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.nextElementSibling;
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bx-hide');
                icon.classList.add('bx-show');
            } else {
                input.type = 'password';
                icon.classList.remove('bx-show');
                icon.classList.add('bx-hide');
            }
        }

        // Real-time password validation
        document.getElementById('password').addEventListener('input', function(e) {
            const password = e.target.value;
            
            // Check length
            const lengthValid = password.length >= 8;
            document.getElementById('length').className = 
                'mb-1 requirement-item ' + (lengthValid ? 'valid' : 'invalid');

            // Check lowercase
            const lowercaseValid = /[a-z]/.test(password);
            document.getElementById('lowercase').className = 
                'mb-1 requirement-item ' + (lowercaseValid ? 'valid' : 'invalid');

            // Check special character
            const specialValid = /[\d\s\W]/.test(password);
            document.getElementById('special').className = 
                'requirement-item ' + (specialValid ? 'valid' : 'invalid');
        });

        // Form submission handling
        document.getElementById('formAuthentication').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const submitBtn = document.getElementById('submitBtn');

            // Validate password requirements
            if (password.length < 8 || 
                !/[a-z]/.test(password) || 
                !/[\d\s\W]/.test(password)) {
                e.preventDefault();
                alert('Please meet all password requirements');
                return;
            }

            // Validate passwords match
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match');
                return;
            }

            // Add loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<div class="d-flex justify-content-center align-items-center"><span class="spinner-border spinner-border-sm me-2"></span>Updating Password...</div>';
            
            // Enable button after 30 seconds in case of errors
            setTimeout(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Set New Password';
            }, 30000);
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.getElementsByClassName('alert');
            for(let alert of alerts) {
                alert.style.display = 'none';
            }
        }, 5000);
    </script>
</body>
</html>