<?php
require_once 'includes/db-connect.php';

// Create uploads directory if it doesn't exist
$upload_dir = __DIR__ . '/img/pfps/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$error = '';
$email_error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone_number = $_POST['phone_number'];
    $picture = null;

    // Check if email exists
    $check_email = $db->prepare("SELECT email FROM users WHERE email = ?");
    $check_email->bind_param("s", $email);
    $check_email->execute();
    $result = $check_email->get_result();
    
    if ($result->num_rows > 0) {
        $email_error = "User exists with this email";
    } else {
        // Password validation
        if ($password !== $confirm_password) {
            $error = "Passwords do not match.";
        } elseif (strlen($password) < 8 || !preg_match('/[a-z]/', $password) || !preg_match('/[\d\W\s]/', $password)) {
            $error = "Password must be at least 8 characters long, contain at least one lowercase letter, and one number, symbol, or whitespace character.";
        } else {
            $password = password_hash($password, PASSWORD_DEFAULT);

            // Handle file upload
            if (isset($_FILES['picture']) && $_FILES['picture']['error'] === UPLOAD_ERR_OK) {
                $file_info = getimagesize($_FILES['picture']['tmp_name']);
                $allowed_types = array(IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF);
                
                if ($file_info && in_array($file_info[2], $allowed_types)) {
                    $file_extension = pathinfo($_FILES['picture']['name'], PATHINFO_EXTENSION);
                    $unique_filename = uniqid('profile_', true) . '.' . $file_extension;
                    $upload_path = $upload_dir . $unique_filename;
                    
                    if (move_uploaded_file($_FILES['picture']['tmp_name'], $upload_path)) {
                        $picture = $unique_filename;
                    } else {
                        $error = "Failed to upload profile picture. Please try again.";
                    }
                } else {
                    $error = "Invalid file type. Please upload a JPG, PNG, or GIF image.";
                }
            }

            if (!$error) {
                $stmt = $db->prepare("INSERT INTO users (email, name, password, phone_number, picture, auth_type) VALUES (?, ?, ?, ?, ?, 'session')");
                $stmt->bind_param("sssss", $email, $name, $password, $phone_number, $picture);

                if ($stmt->execute()) {
                    header('Location: login.php');
                    exit();
                } else {
                    $error = "Registration failed. Please try again.";
                }
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
    <title>Sign up - Cognitive Campus</title>

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
            max-width: 500px;
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
        .app-brand-text {
            font-size: 1.75rem;
            letter-spacing: -0.5px;
            font-weight: 600;
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
        .btn-primary:disabled {
            background-color: #d9d9d9;
            border-color: #d9d9d9;
            color: #888;
        }
        .input-group-text {
            cursor: pointer;
            background-color: transparent;
        }
        .text-primary {
            color: #696cff !important;
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
        a {
            color: #696cff;
            text-decoration: none;
        }
        a:hover {
            color: #6062e8;
            text-decoration: underline;
        }

        .btn-loading {
            position: relative;
            color: transparent !important;
        }

        .btn-loading::after {
            content: "";
            position: absolute;
            width: 1rem;
            height: 1rem;
            top: 50%;
            left: 50%;
            margin-left: -0.5rem;
            margin-top: -0.5rem;
            border: 2px solid #fff;
            border-radius: 50%;
            border-right-color: transparent;
            animation: spin 0.75s infinite linear;
        }

        @keyframes spin {
            100% {
                transform: rotate(360deg);
            }
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

                    <!-- Welcome Text -->
                    <h4 class="mb-2 text-center">Adventure starts here</h4>
                    <p class="mb-4 text-center text-muted">Make your app management easy and fun!</p>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <!-- Registration Form -->
                    <form id="formAuthentication" action="register.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="name" 
                                placeholder="Enter your username" autofocus required />
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control <?php echo $email_error ? 'is-invalid' : ''; ?>" 
                                id="email" name="email" placeholder="Enter your email" required />
                            <?php if ($email_error): ?>
                                <div class="error-text text-danger mt-2"><?php echo htmlspecialchars($email_error); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="password">Password</label>
                            <div class="input-group">
                                <input type="password" id="password" class="form-control" name="password" 
                                    placeholder="············" required />
                                <span class="input-group-text">
                                    <i class="bx bx-hide password-toggle"></i>
                                </span>
                            </div>
                            <small class="text-muted">Minimum 8 characters, at least one lowercase letter, and one number, symbol, or whitespace character.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="confirm-password">Confirm Password</label>
                            <div class="input-group">
                                <input type="password" id="confirm-password" class="form-control" name="confirm_password" 
                                    placeholder="············" required />
                                <span class="input-group-text">
                                    <i class="bx bx-hide confirm-password-toggle"></i>
                                </span>
                            </div>
                            <div class="error-text" id="password-match-feedback" style="display: none;">
                                Passwords do not match.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone_number" 
                                placeholder="Enter your phone number" required />
                        </div>

                        <div class="mb-3">
                            <label for="picture" class="form-label">Profile Picture</label>
                            <input type="file" class="form-control" id="picture" name="picture" accept="image/*" />
                            <small class="text-muted">Allowed formats: JPG, PNG, GIF. Max size: 5MB</small>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="terms-conditions" name="terms" required />
                                <label class="form-check-label" for="terms-conditions">
                                    I agree to the
                                    <a href="#" class="text-primary" data-bs-toggle="modal" data-bs-target="#privacyPolicyModal">privacy policy & terms</a>
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mb-3" id="signup-btn" disabled>Sign up</button>
                    </form>

                    <p class="text-center mt-2">
                        <span>Already have an account? </span>
                        <a href="login.php">Sign in instead</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Core JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
        // Password visibility toggle for both password fields
        $('.password-toggle, .confirm-password-toggle').click(function() {
            const isConfirmPassword = $(this).hasClass('confirm-password-toggle');
            const passwordInput = isConfirmPassword ? $('#confirm-password') : $('#password');
            const icon = $(this);
            
            if (passwordInput.attr('type') === 'password') {
                passwordInput.attr('type', 'text');
                icon.removeClass('bx-hide').addClass('bx-show');
            } else {
                passwordInput.attr('type', 'password');
                icon.removeClass('bx-show').addClass('bx-hide');
            }
        });

        // Terms and conditions checkbox
        $('#terms-conditions').change(function() {
            validateForm();
        });

        // Password validation function
        function validatePassword(password) {
            return password.length >= 8 && /[a-z]/.test(password) && /[\d\W\s]/.test(password);
        }

        // Form validation function
        function validateForm() {
            const password = $('#password').val();
            const confirmPassword = $('#confirm-password').val();
            const termsChecked = $('#terms-conditions').is(':checked');
            const passwordsMatch = password === confirmPassword;
            const isPasswordValid = validatePassword(password);

            // Show/hide password match feedback
            if (confirmPassword) {
                if (passwordsMatch) {
                    $('#confirm-password').removeClass('is-invalid').addClass('is-valid');
                    $('#password-match-feedback').hide();
                } else {
                    $('#confirm-password').removeClass('is-valid').addClass('is-invalid');
                    $('#password-match-feedback').show();
                }
            }

            // Enable/disable submit button
            $('#signup-btn').prop('disabled', 
                !termsChecked || 
                !passwordsMatch || 
                !isPasswordValid || 
                !password || 
                !confirmPassword
            );
        }

        // Input event listeners
        $('#password, #confirm-password').on('input', function() {
            const password = $('#password').val();
            const isValid = validatePassword(password);
            
            if ($(this).attr('id') === 'password') {
                if (isValid) {
                    $(this).removeClass('is-invalid').addClass('is-valid');
                } else {
                    $(this).removeClass('is-valid').addClass('is-invalid');
                }
            }
            
            validateForm();
        });

        // File upload validation
        $('#picture').change(function(e) {
            const file = e.target.files[0];
            if (file) {
                const fileSize = file.size / 1024 / 1024; // Convert to MB
                const fileType = file.type;
                
                if (fileSize > 5) {
                    alert('File size must be less than 5MB');
                    this.value = '';
                    return;
                }
                
                if (!fileType.startsWith('image/')) {
                    alert('Please upload an image file');
                    this.value = '';
                    return;
                }
            }
        });

        // Form submission
        $('#formAuthentication').on('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = $('#signup-btn');
            submitBtn.addClass('btn-loading').prop('disabled', true);
            
            // Wait for 3 seconds then submit
            setTimeout(() => {
                this.submit();
            }, 3000);
        });
    });
    </script>
</body>
</html>