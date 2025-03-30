
<?php
// forget_password.php (main file)
require_once 'includes/db-connect.php';
require 'vendor/autoload.php';
require_once 'includes/rate-limit.php';
require_once 'forget-password-mail.php';



if(isset($_POST['email'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    
    // Check rate limiting
    $rateLimit = checkRateLimit($email, 'password_reset', $db);
    if (!$rateLimit['allowed']) {
        $error = $rateLimit['message'];
    } else {
        // Check if email exists
        $stmt = $db->prepare("SELECT id, name FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            // Generate OTP and token
            $otp = sprintf("%06d", mt_rand(0, 999999));
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));
            
            // Hash the OTP before storing
            $hashedOtp = password_hash($otp, PASSWORD_DEFAULT);
            
            // Store token, hashed OTP and expiry
            $stmt = $db->prepare("UPDATE users SET reset_token = ?, reset_otp = ?, reset_token_expiry = ? WHERE email = ?");
            $stmt->bind_param("ssss", $token, $hashedOtp, $expiry, $email);
            
            if($stmt->execute()) {
                // Send email
                $emailSent = sendPasswordResetEmail([
                    'email' => $email,
                    'name' => $user['name'],
                    'otp' => $otp
                ]);
                
                if($emailSent) {
                    $_SESSION['reset_email'] = $email;
                    $_SESSION['reset_token'] = $token;
                    header("Location: verify-otp.php");
                    exit();
                } else {
                    $error = "Failed to send reset email. Please try again later.";
                }
            } else {
                $error = "System error. Please try again later.";
            }
        } else {
            // Use a generic message for security
            $error = "If this email exists in our system, you will receive reset instructions.";
            // Still redirect to avoid email enumeration
            header("Location: forget_password.php?status=check_email");
            exit();
        }
    }
}

// Rest of your HTML remains the same...
?>

<!DOCTYPE html>
<html lang="en" class="light-style" dir="ltr" data-theme="theme-default">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>Forgot Password - Cognitive Campus</title>

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
        .spinner-border {
            width: 1rem;
            height: 1rem;
        }
        .alert {
            margin-bottom: 1rem;
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
                    <h4 class="mb-2 text-center">Forgot Password?</h4>
                    <p class="mb-4 text-center text-muted">Enter your email and we'll send you instructions to reset your password</p>

                    <!-- Error Messages -->
                    <?php if(isset($error)): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Success Messages (for when returning from other pages) -->
                    <?php if(isset($_GET['status']) && $_GET['status'] == 'email_sent'): ?>
                        <div class="alert alert-success" role="alert">
                            OTP has been sent to your email address.
                        </div>
                    <?php endif; ?>

                    <!-- Form -->
                    <form id="formAuthentication" class="mb-3" action="" method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input 
                                type="email" 
                                class="form-control" 
                                id="email" 
                                name="email" 
                                placeholder="Enter your email" 
                                autofocus 
                                required 
                            />
                        </div>
                        <button type="submit" class="btn btn-primary d-grid w-100" id="submitBtn">
                            Send Reset Link
                        </button>
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
        // Add loading state to submit button
        document.getElementById('formAuthentication').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<div class="d-flex justify-content-center align-items-center"><span class="spinner-border spinner-border-sm me-2"></span>Sending...</div>';
            
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
    </script>
</body>
</html>