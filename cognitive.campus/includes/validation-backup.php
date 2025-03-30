<?php
// added session-management.php to db-connet and validation (isAuth function as well)
require_once 'vendor/autoload.php';
require_once 'includes/db-connect.php';
require_once 'includes/stripe-config.php';

function isAuthenticated() {
    return isset($_SESSION['access_token']) || isset($_SESSION['user_id']);
}

function getUserInfo() {
    global $db, $classroomService;

    $classroomService = null;

    if (isset($_SESSION['access_token'])) {
        // Google Auth
        try {
            $client = new Google_Client();
            $client->setAccessToken($_SESSION['access_token']);
            
            if ($client->isAccessTokenExpired()) {
                if ($client->getRefreshToken()) {
                    $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                    $_SESSION['access_token'] = $client->getAccessToken();
                } else {
                    unset($_SESSION['access_token']);
                    return null;
                }
            }

            $classroomService = new Google_Service_Classroom($client);
            $oauth2 = new Google_Service_Oauth2($client);
            $userInfo = $oauth2->userinfo->get();

            $userEmail = $userInfo->getEmail();
            $userName = $userInfo->getName();
            $authType = 'google';
            
            // Save or update Google profile picture URL in database
            $googlePicture = $userInfo->getPicture();
            if ($googlePicture) {
                // Download and save the Google profile picture
                $pictureContent = file_get_contents($googlePicture);
                if ($pictureContent !== false) {
                    $fileName = md5($userEmail . time()) . '.jpg';
                    $picturePath = 'img/pfps/' . $fileName;
                    
                    if (file_put_contents($picturePath, $pictureContent)) {
                        // Update the database with the new picture path
                        $stmt = $db->prepare("INSERT INTO users (email, picture, name, auth_type) 
                                            VALUES (?, ?, ?, ?) 
                                            ON DUPLICATE KEY UPDATE 
                                            picture = VALUES(picture),
                                            name = VALUES(name)");
                        $stmt->bind_param("ssss", $userEmail, $fileName, $userName, $authType);
                        $stmt->execute();
                    }
                }
            }

        } catch (Exception $e) {
            error_log('Google Auth Error: ' . $e->getMessage());
            unset($_SESSION['access_token']);
        }
    }

    // Session-based or fallback auth
    if (!isset($userEmail) && isset($_SESSION['user_id'])) {
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user) {
            $userEmail = $user['email'];
            $userProfile = 'img/pfps/' . $user['picture'];
            $userName = $user['name'];
            $authType = 'session';
        } else {
            return null;
        }
    }

    if (!isset($userEmail)) {
        return null;
    }

    // Get user plan and picture from database
    $stmt = $db->prepare("SELECT plan, picture FROM users WHERE email = ?");
    $stmt->bind_param("s", $userEmail);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user_data = $result->fetch_assoc();
        $plan = $user_data['plan'];
        $userProfile = 'img/pfps/' . $user_data['picture'];
    } else {
        $plan = 'basic';
        $userProfile = 'img/pfps/default.jpg'; // Fallback default profile picture
    }

    return [
        'email' => $userEmail,
        'picture' => $userProfile,
        'name' => $userName,
        'plan' => $plan,
        'auth_type' => $authType,
        'classroomService' => $classroomService
    ];
}

if (!isAuthenticated()) {
    header('Location: index.php');
    exit();
}

$userInfo = getUserInfo();

// Make $classroomService available globally
$classroomService = $userInfo['classroomService'];






function check2FAStatus($email) {
    global $db;
    $stmt = $db->prepare("SELECT two_factor_enabled FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    return $user ? $user['two_factor_enabled'] : false;
}

function validateLogin($email, $password) {
    global $db;
    
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND auth_type = 'session'");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if ($user && password_verify($password, $user['password'])) {
        if ($user['two_factor_enabled']) {
            // Generate and store verification token
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+15 minutes'));
            
            $stmt = $db->prepare("INSERT INTO verification_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $user['id'], $token, $expires);
            $stmt->execute();
            
            return [
                'status' => 'needs_2fa',
                'user_id' => $user['id'],
                'token' => $token
            ];
        } else {
            return [
                'status' => 'success',
                'user_id' => $user['id']
            ];
        }
    }
    
    return ['status' => 'error'];
}

function verify2FAToken($token) {
    global $db;
    
    $stmt = $db->prepare("SELECT * FROM verification_tokens WHERE token = ? AND expires_at > NOW() LIMIT 1");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $token_data = $result->fetch_assoc();
    
    if ($token_data) {
        // Delete the used token
        $stmt = $db->prepare("DELETE FROM verification_tokens WHERE token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        
        return $token_data['user_id'];
    }
    
    return false;
}







