<?php
// includes/rate-limit.php
function checkRateLimit($email, $type = 'otp', $db) {
    // Configure limits 
    $limits = [
        'otp' => [
            'attempts' => 3,      // Maximum attempts
            'window' => 900,      // Time window in seconds (15 minutes)
            'lockout' => 1800     // Lockout period in seconds (30 minutes)
        ],
        'password_reset' => [     
            'attempts' => 3,
            'window' => 900,
            'lockout' => 1800
        ]
    ];
    
    // Create rate_limits table if it doesn't exist
    $db->query("CREATE TABLE IF NOT EXISTS rate_limits (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL,
        type VARCHAR(50) NOT NULL,
        attempts INT DEFAULT 1,
        last_attempt DATETIME DEFAULT CURRENT_TIMESTAMP,
        locked_until DATETIME NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX (email, type)
    )");
    
    // Check for existing rate limit record
    $stmt = $db->prepare("SELECT * FROM rate_limits WHERE email = ? AND type = ?");
    $stmt->bind_param("ss", $email, $type);
    $stmt->execute();
    $result = $stmt->get_result();
    $limit = $result->fetch_assoc();
    
    $now = new DateTime();
    
    if ($limit) {
        $lastAttempt = new DateTime($limit['last_attempt']);
        $timeDiff = $now->getTimestamp() - $lastAttempt->getTimestamp();
        
        // Check if user is locked out
        if ($limit['locked_until'] && new DateTime($limit['locked_until']) > $now) {
            $lockExpiry = new DateTime($limit['locked_until']);
            $waitTime = $lockExpiry->getTimestamp() - $now->getTimestamp();
            return [
                'allowed' => false,
                'message' => "Too many attempts. Please try again in " . ceil($waitTime / 60) . " minutes."
            ];
        }
        
        // Reset attempts if window has passed
        if ($timeDiff > $limits[$type]['window']) {
            $stmt = $db->prepare("UPDATE rate_limits SET attempts = 1, last_attempt = NOW(), locked_until = NULL WHERE email = ? AND type = ?");
            $stmt->bind_param("ss", $email, $type);
            $stmt->execute();
            return ['allowed' => true];
        }
        
        // Increment attempts and check if should be locked
        if ($limit['attempts'] >= $limits[$type]['attempts']) {
            $lockedUntil = $now->modify('+' . $limits[$type]['lockout'] . ' seconds');
            $stmt = $db->prepare("UPDATE rate_limits SET locked_until = ? WHERE email = ? AND type = ?");
            $stmt->bind_param("sss", $lockedUntil->format('Y-m-d H:i:s'), $email, $type);
            $stmt->execute();
            return [
                'allowed' => false,
                'message' => "Too many attempts. Please try again in " . ($limits[$type]['lockout'] / 60) . " minutes."
            ];
        }
        
        // Increment attempts
        $stmt = $db->prepare("UPDATE rate_limits SET attempts = attempts + 1, last_attempt = NOW() WHERE email = ? AND type = ?");
        $stmt->bind_param("ss", $email, $type);
        $stmt->execute();
    } else {
        // Create new rate limit record
        $stmt = $db->prepare("INSERT INTO rate_limits (email, type) VALUES (?, ?)");
        $stmt->bind_param("ss", $email, $type);
        $stmt->execute();
    }
    
    return ['allowed' => true];
}