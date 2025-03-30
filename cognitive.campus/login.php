<?php
// login.php
require_once 'includes/db-connect.php';
require_once 'includes/rate-limit.php';
require_once 'two-factor-auth-mail.php';

// Start session at the beginning of the script
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check rate limiting for login attempts
    $rateLimit = checkRateLimit($email, 'login', $db);
    if (!$rateLimit['allowed']) {
        $error = $rateLimit['message'];
    }
    // Email validation
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } 
    // Password validation
    elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    }
    else {
        try {
            $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND auth_type = 'session'");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($user = $result->fetch_assoc()) {
                if (password_verify($password, $user['password'])) {
                    // Check if 2FA is enabled
                    if ($user['two_factor_enabled'] == 1) {
                        // Generate 6-digit OTP
                        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                        $hashedOtp = password_hash($otp, PASSWORD_DEFAULT);
                        
                        // Set OTP expiry (15 minutes from now)
                        $otpExpiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                        
                        // Update user record with new OTP and expiry
                        $updateStmt = $db->prepare("UPDATE users SET reset_otp = ?, reset_token_expiry = ? WHERE id = ?");
                        $updateStmt->bind_param("ssi", $hashedOtp, $otpExpiry, $user['id']);
                        
                        if ($updateStmt->execute()) {
                            // Store email and user info in session for verification
                            $_SESSION['reset_email'] = $user['email'];
                            $_SESSION['reset_token'] = bin2hex(random_bytes(32));
                            
                            // Prepare email data
                            $emailData = [
                                'email' => $user['email'],
                                'name' => $user['name'],
                                'otp' => $otp
                            ];
                            
                            if (sendTwoFactorAuthEmail($emailData)) {
                                header('Location: verify-2fa.php');
                                exit();
                            } else {
                                $error = "Failed to send verification email. Please try again.";
                            }
                        } else {
                            $error = "System error while setting up verification.";
                        }
                    } else {
                        // No 2FA required
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['auth_type'] = 'session';
                        
                        header('Location: dashboard.php');
                        exit();
                    }
                } else {
                    $error = "Invalid password.";
                }
            } else {
                $error = "No user found with this email.";
            }
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            $error = "An error occurred during login. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en" class="light-style" dir="ltr" data-theme="theme-default">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>Login - Cognitive Campus</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="img/Logo/Cognitive Campus Logo.png" class="favicon-icon" />

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
        .form-control {
            padding: 0.775rem 1rem;
            background-color: #fff;
            border: 1px solid #d9dee3;
            border-radius: 0.375rem;
        }
        .form-control:focus {
            border-color: #696cff;
            box-shadow: 0 0 0.25rem 0.05rem rgba(105, 108, 255, 0.1);
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
        .input-group-text {
            cursor: pointer;
            background-color: transparent;
        }
        .text-primary {
            color: #696cff !important;
        }
        .spinner-border {
            width: 1rem;
            height: 1rem;
        }
    </style>
</head>

<body>
    <div class="authentication-wrapper">
        <div class="authentication-inner">
            <div class="card">
                <div class="card-body p-4">
                    <div class="app-brand justify-content-center text-center mb-4">
                        <img src="img/Logo/Cognitive Campus Logo.png" alt="Cognitive Campus" class="app-brand-logo rounded-circle">
                    </div>

                    <!-- Welcome Text -->
                    <h4 class="mb-2 text-center">Welcome to Cognitive Campus</h4>
                    <p class="mb-4 text-center text-muted">Please sign-in to your account and start the adventure</p>

                    <!-- Error Messages -->
                    <?php if(isset($error)): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Login Form -->
                    <form id="formAuthentication" action="login.php" method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                placeholder="Enter your email" autofocus required />
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <label class="form-label" for="password">Password</label>
                                <a href="forget_password.php" class="text-decoration-none text-primary">
                                    Forgot Password?
                                </a>
                            </div>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password" 
                                    placeholder="············" required />
                                <span class="input-group-text">
                                    <i class="bx bx-hide password-toggle"></i>
                                </span>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary d-grid w-100 mb-3" id="submitBtn">
                            Sign in
                        </button>
                    </form>

                    <p class="text-center mt-2">
                        <span>New on our platform? </span>
                        <a href="register.php" class="text-decoration-none text-primary">Create an account</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Core JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('formAuthentication');
            const passwordToggle = document.querySelector('.password-toggle');
            const passwordInput = document.getElementById('password');
            
            // Password visibility toggle
            passwordToggle.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.classList.toggle('bx-hide');
                this.classList.toggle('bx-show');
            });
            
            // Form submission handling
            form.addEventListener('submit', function(e) {
                const submitBtn = document.getElementById('submitBtn');
                const originalText = submitBtn.innerHTML;
                
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<div class="d-flex justify-content-center align-items-center"><span class="spinner-border spinner-border-sm me-2"></span>Signing in...</div>';
                
                // Enable button after 30 seconds in case of errors
                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }, 30000);
            });

            // Auto-hide alerts after 5 seconds
            setTimeout(() => {
                const alerts = document.getElementsByClassName('alert');
                for(let alert of alerts) {
                    alert.style.display = 'none';
                }
            }, 5000);
        });
    </script>
</body>
</html>