<?php
// verify-2fa.php
require_once 'includes/db-connect.php';
require_once 'includes/rate-limit.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if no reset email in session
if (!isset($_SESSION['reset_email']) || !isset($_SESSION['reset_token'])) {
    header("Location: login.php");
    exit();
}

if (isset($_POST['otp'])) {
    $email = $_SESSION['reset_email'];
    $token = $_SESSION['reset_token'];
    $otp = $_POST['otp'];
    
    // Check rate limiting for OTP attempts
    $rateLimit = checkRateLimit($email, 'otp', $db);
    if (!$rateLimit['allowed']) {
        $error = $rateLimit['message'];
    } else {
        try {
            // Get user data and verify OTP
            $stmt = $db->prepare("SELECT id, email, name, reset_otp, reset_token_expiry FROM users WHERE email = ? AND auth_type = 'session'");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                
                // Check if token has expired
                if (strtotime($user['reset_token_expiry']) < time()) {
                    $error = "2FA code has expired. Please request a new one.";
                } 
                // Verify OTP
                else if (password_verify($otp, $user['reset_otp'])) {
                    // Clear 2FA verification data
                    $clearStmt = $db->prepare("UPDATE users SET reset_otp = NULL, reset_token_expiry = NULL WHERE id = ?");
                    $clearStmt->bind_param("i", $user['id']);
                    $clearStmt->execute();
                    
                    // Set user session data
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['auth_type'] = 'session';
                    $_SESSION['2fa_verified'] = true;
                    
                    // Clear sensitive session data
                    unset($_SESSION['reset_email']);
                    unset($_SESSION['reset_token']);
                    
                    // Redirect to dashboard
                    header("Location: dashboard.php");
                    exit();
                } else {
                    $error = "Invalid 2FA code. Please try again.";
                }
            } else {
                $error = "Invalid verification attempt. Please try again.";
            }
        } catch (Exception $e) {
            error_log("2FA verification error: " . $e->getMessage());
            $error = "An error occurred during verification. Please try again.";
        }
    }
}

// Rest of the HTML and JavaScript code remains the same...
?>

<!DOCTYPE html>
<html lang="en" class="light-style" dir="ltr" data-theme="theme-default">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>Verify 2FA - Cognitive Campus</title>

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
        .otp-inputs {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-bottom: 1.5rem;
        }
        .otp-inputs input {
            width: 40px;
            height: 40px;
            text-align: center;
            font-size: 1.2rem;
            border: 1px solid #d9dee3;
            border-radius: 6px;
        }
        .otp-inputs input:focus {
            border-color: #696cff;
            outline: none;
            box-shadow: 0 0 0 0.25rem rgba(105, 108, 255, 0.25);
        }
        #timer {
            color: #697a8d;
            font-size: 0.875rem;
            text-align: center;
            margin-bottom: 1rem;
        }
        .spinner-border {
            width: 1rem;
            height: 1rem;
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
                <div class="app-brand justify-content-center text-center mb-4">
                        <img src="img/Logo/Cognitive Campus Logo.png" alt="Cognitive Campus" class="app-brand-logo rounded-circle">
                    </div>

                    <!-- Title -->
                    <h4 class="mb-2 text-center">Verify 2FA </h4>
                    <p class="mb-4 text-center text-muted">
                        We sent a verification code to your email address: <br>
                        <strong><?php echo isset($_SESSION['reset_email']) ? htmlspecialchars($_SESSION['reset_email']) : ''; ?></strong>
                    </p>

                    <!-- Error Messages -->
                    <?php if(isset($error)): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Form -->
                    <form id="formAuthentication" class="mb-3" action="" method="POST">
                        <div class="otp-inputs mb-3">
                            <input type="text" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                            <input type="text" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                            <input type="text" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                            <input type="text" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                            <input type="text" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                            <input type="text" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                            <input type="hidden" name="otp" id="otpFinal">
                        </div>

                        <div id="timer" class="mb-3">
                            Code expires in: <span id="countdown">15:00</span>
                        </div>

                        <button type="submit" class="btn btn-primary d-grid w-100 mb-3" id="submitBtn">
                            Verify 2FA
                        </button>

                        <div class="text-center">
                            Didn't receive the code?
                            <a href="forget_password.php" class="text-primary">
                                Resend 2FA
                            </a>
                        </div>
                    </form>

                    <!-- Back to login -->
                    <div class="text-center">
                        <a href="login.php" class="d-flex align-items-center justify-content-center text-decoration-none text-primary">
                            <i class="bx bx-chevron-left me-1 scaleX-n1-rtl"></i>
                            Back to login
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Core JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // OTP Input Handling
            const inputs = document.querySelectorAll('.otp-inputs input[type="text"]');
            const form = document.getElementById('formAuthentication');
            const otpFinal = document.getElementById('otpFinal');

            // Auto-focus and input handling
            inputs.forEach((input, index) => {
                input.addEventListener('keyup', (e) => {
                    const currentInput = input;
                    const nextInput = input.nextElementSibling;
                    const prevInput = input.previousElementSibling;

                    // Validate input is numeric
                    if (isNaN(e.key)) {
                        currentInput.value = '';
                        return;
                    }

                    // Auto-focus next input
                    if (nextInput && currentInput.value !== '') {
                        nextInput.focus();
                    }

                    // Handle backspace
                    if (e.key === 'Backspace') {
                        inputs.forEach((input, index2) => {
                            if (index2 >= index) {
                                input.value = '';
                            }
                        });
                        if (prevInput) {
                            prevInput.focus();
                        }
                    }

                    // Combine OTP inputs on form submit
                    let otp = '';
                    inputs.forEach(input => {
                        otp += input.value;
                    });
                    otpFinal.value = otp;
                });
            });

            // Add loading state to submit button
            form.addEventListener('submit', function(e) {
                const submitBtn = document.getElementById('submitBtn');
                const originalText = submitBtn.innerHTML;
                
                // Validate all inputs are filled
                let isValid = true;
                inputs.forEach(input => {
                    if (input.value === '') {
                        isValid = false;
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    alert('Please fill in all 2FA digits');
                    return;
                }

                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Verifying...';
                
                // Enable button after 30 seconds in case of errors
                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }, 30000);
            });

            // Countdown Timer
            let timeLeft = 15 * 60; // 15 minutes in seconds
            const countdownDisplay = document.getElementById('countdown');
            
            const countdownTimer = setInterval(() => {
                const minutes = Math.floor(timeLeft / 60);
                const seconds = timeLeft % 60;
                countdownDisplay.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
                
                if (timeLeft <= 0) {
                    clearInterval(countdownTimer);
                    countdownDisplay.textContent = "Expired";
                    // Disable form submission
                    form.querySelector('button[type="submit"]').disabled = true;
                }
                timeLeft--;
            }, 1000);

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