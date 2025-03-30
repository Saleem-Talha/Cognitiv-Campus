<?php

// Session configuration constants
define('SESSION_LIFETIME', 1800); // 30 minutes session lifetime
define('INACTIVITY_TIMEOUT', 900); // 15 seconds inactivity timeout

/**
 * Handles session management and timeouts
 * @return bool True if session is valid, false if session should be terminated
 */
function manageSessionTimeout() {
    // Check if user is authenticated
    if (!isset($_SESSION['user_id'])) {
        return true;
    }

    $current_time = time();

    // Check absolute session lifetime
    if (isset($_SESSION['session_start']) && 
        ($current_time - $_SESSION['session_start']) > SESSION_LIFETIME) {
        destroySession('Session expired');
        return false;
    }

    // Check inactivity timeout
    if (isset($_SESSION['last_activity']) && 
        ($current_time - $_SESSION['last_activity']) > INACTIVITY_TIMEOUT) {
        destroySession('Inactive for too long');
        return false;
    }

    // Update last activity time
    $_SESSION['last_activity'] = $current_time;
    return true;
}

/**
 * Destroys the current session and redirects
 * @param string $reason Reason for session destruction
 */
function destroySession($reason = 'Logged out') {
    // Log the logout reason (optional)
    error_log("Session destroyed: $reason for user " . ($_SESSION['user_id'] ?? 'Unknown'));

    // Unset all session variables
    $_SESSION = array();

    // Destroy the session
    session_destroy();

    // Delete the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    // Redirect to login page
    header('Location: index.php?logout_reason=' . urlencode($reason));
    exit();
}

/**
 * Regenerates session ID periodically to prevent session fixation
 */
function regenerateSessionID() {
    // Regenerate session ID every 30 minutes
    if (!isset($_SESSION['last_regeneration']) || 
        (time() - $_SESSION['last_regeneration']) > 1800) {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}

/**
 * Initializes session tracking
 */
function initializeSessionTracking() {
    // Set session start time if not already set
    if (!isset($_SESSION['session_start'])) {
        $_SESSION['session_start'] = time();
    }
}

/**
 * Comprehensive session validation middleware
 * @return bool True if session is valid, false otherwise
 */
function validateSession() {
    regenerateSessionID();
    return manageSessionTimeout();
}