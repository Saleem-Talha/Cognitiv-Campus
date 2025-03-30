<?php
require_once 'validation.php';

// Initialize default values
$userInfo = [
    'email' => 'guest@example.com',
    'profile' => 'assets/img/avatars/1.png',
    'name' => 'Guest',
    'plan' => 'basic',
    'auth_type' => 'none'
];

// Attempt to get user info
$retrievedUserInfo = getUserInfo();

// If user info is retrieved successfully, update $userInfo
if ($retrievedUserInfo !== null) {
    $userInfo = array_merge($userInfo, $retrievedUserInfo);
}

// Create individual variables for easy access
$userEmail = $userInfo['email'];
$userProfile = $userInfo['picture'];
$userName = $userInfo['name'];
$userPlan = $userInfo['plan'];
$authType = $userInfo['auth_type'];


if (defined('DEBUG') && DEBUG) {
    error_log('User Info: ' . print_r($userInfo, true));
}