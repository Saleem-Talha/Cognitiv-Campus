<?php
// Include Composer's autoload file to load the Google API client library and other dependencies
require_once 'vendor/autoload.php';

// Include the database connection file
require_once 'includes/db-connect.php';

// Start output buffering
ob_start();
$clientId = GOOGLE_CLIENT_ID;
    $clientSecret = GOOGLE_CLIENT_SECRET;
    $redirectUri = GOOGLE_REDIRECT_URI;
$client = new Google_Client();
    $client->setClientId($clientId);
    $client->setClientSecret($clientSecret);
    $client->setRedirectUri($redirectUri);
// Add scopes required for accessing Google Classroom and Google Drive APIs
$client->addScope(Google_Service_Classroom::CLASSROOM_COURSES);
$client->addScope(Google_Service_Classroom::CLASSROOM_ANNOUNCEMENTS);
$client->addScope(Google_Service_Classroom::CLASSROOM_COURSEWORK_STUDENTS);
$client->addScope(Google_Service_Classroom::CLASSROOM_COURSEWORK_ME);
$client->addScope(Google_Service_Classroom::CLASSROOM_ROSTERS);
$client->addScope(Google_Service_Classroom::CLASSROOM_PROFILE_EMAILS);
$client->addScope(Google_Service_Classroom::CLASSROOM_PROFILE_PHOTOS);
$client->addScope(Google_Service_Classroom::CLASSROOM_GUARDIANLINKS_STUDENTS);
$client->addScope(Google_Service_Classroom::CLASSROOM_COURSEWORKMATERIALS);
$client->addScope(Google_Service_Classroom::CLASSROOM_TOPICS);
$client->addScope(Google_Service_Classroom::CLASSROOM_STUDENT_SUBMISSIONS_ME_READONLY);
$client->addScope(Google_Service_Classroom::CLASSROOM_PUSH_NOTIFICATIONS);
$client->addScope(Google_Service_Drive::DRIVE_FILE);
$client->addScope('email');
$client->addScope('profile');
$client->addScope('openid');


try {
    // Check if the authorization code is present in the URL
    if (isset($_GET['code'])) {
        // Exchange the authorization code for an access token
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

        // Check if there was an error fetching the access token
        if (isset($token['error'])) {
            throw new Exception('Error fetching access token: ' . $token['error']);
        }

        // Set the access token to the client
        $client->setAccessToken($token);

        // Store the access token in the session
        $_SESSION['access_token'] = $token;

        // Create a new Google OAuth2 service object
        $oauth2 = new Google_Service_Oauth2($client);

        // Fetch user information
        $userInfo = $oauth2->userinfo->get();

        // Extract user data
        $email = $userInfo->getEmail();
        $name = $userInfo->getName();
        $picture = $userInfo->getPicture();

        // Prepare an SQL statement to insert or update user data in the database
        $stmt = $db->prepare("INSERT INTO users (email, name, picture, auth_type) VALUES (?, ?, ?, 'google') ON DUPLICATE KEY UPDATE name = ?, picture = ?, auth_type = 'google'");
        $stmt->bind_param("sssss", $email, $name, $picture, $name, $picture);

        // Execute the SQL statement
        $stmt->execute();

        // Redirect to the dashboard page
        header('Location: dashboard.php');
        exit();
    }
} catch (Exception $e) {
    // Display an error message if an exception occurs
    echo 'An error occurred: ' . $e->getMessage();
    // Log the error message to the server's error log
    error_log('An error occurred: ' . $e->getMessage());
}
