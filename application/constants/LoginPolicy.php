<?php

namespace App\Constants;

final class LoginPolicy
{
    // Account Creation and Management
    public const USERNAME_MIN_LENGTH = 3;
    public const USERNAME_MAX_LENGTH = 100;
    public const USERNAME_PATTERN = '/^[a-zA-Z0-9_-]+$/';
    public const USERNAME_PATTERN_MSG = 'Username must contain only letters, numbers, underscores, and hyphens.';
    public const EMAIL_VERIFICATION_REQUIRED = true;
    public const EMAIL_VERIFICATION_EXPIRY_HOURS = 24;

    // Login policies
    public const MAX_FAILED_ATTEMPTS = 5;

    // Default password
    public const DEFAULT_PASSWORD = '1234p@$$';
    public const FORCE_CHANGE_DEFAULT_PASSWORD = true;

    // Password length restrictions
    public const PASSWORD_MIN_LENGTH = 8;
    public const PASSWORD_MAX_LENGTH = 20;

    // Password complexity rules
    public const PASSWORD_COMPLEXITY_ENABLED = true;
    public const REQUIRE_UPPERCASE = true;
    public const REQUIRE_LOWERCASE = true;
    public const REQUIRE_NUMBER = true;
    public const REQUIRE_SPECIAL_CHAR = true;

    // Password regex pattern and message
    public const PASSWORD_PATTERN = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,20}$/';
    public const PASSWORD_PATTERN_MSG = 'Password must be at least 8 characters, at most 20 characters, and must contain at least one lowercase letter, one uppercase letter, one number, and one special character (@$!%*?&).';

    // Disallow common patterns
    public const DISALLOW_USERNAME_IN_PASSWORD = true;
    public const DISALLOW_EMAIL_IN_PASSWORD = true;
    public const CHECK_AGAINST_COMMON_PASSWORDS = true;

    // Session Management
    public const SESSION_INACTIVITY_TIMEOUT_MINUTES = 30;
    public const SESSION_MAX_DURATION_HOURS = 8;
    public const ALLOW_MULTIPLE_SESSIONS = false;
    public const REGENERATE_SESSION_ID_ON_LOGIN = true;

    // Password expiration rules
    public const PASSWORD_CHANGE_URL = 'security/change-password';
    public const PASSWORD_EXPIRATION_DAYS = 180;
    public const PASSWORD_EXPIRATION_WARNING_DAYS = 14;
    public const PASSWORD_EXPIRATION_MSG = 'Password has expired, please change your password.';
    public const PASSWORD_EXPIRATION_WARNING_MSG = 'Your password will expire in {days} days. Please change it soon.';

    // Password history rules
    public const PASSWORD_HISTORY_LIMIT = 5;
    public const PASSWORD_HISTORY_MSG = 'Password has been used before, please use a new password.';

    // Password similarity rules
    public const PASSWORD_DIFFERENT_PREVIOUS = 3;
    public const PASSWORD_DIFFERENT_PREVIOUS_MSG = 'Password must be different from the previous password.';
    public const PASSWORD_DIFFERENT_CURRENT = 3;
    public const PASSWORD_DIFFERENT_CURRENT_MSG = 'New password must be different from the current password.';

    // Multi-factor authentication
    public const MFA_AVAILABLE = false;
    public const MFA_REQUIRED_FOR_ADMIN = true;
    public const MFA_METHODS = ['email', 'app', 'sms'];
    public const MFA_CODE_EXPIRY_MINUTES = 5;

    // Account Recovery
    public const RECOVERY_VIA_EMAIL = true;
    public const RECOVERY_LINK_EXPIRY_HOURS = 1;

    // Logging and Monitoring
    public const LOG_LOGIN_HISTORY = true;
    public const LOG_ALL_LOGIN_ATTEMPTS = true;
    public const LOG_RETENTION_DAYS = 90;

    // Security Notifications
    public const NOTIFY_NEW_DEVICE_LOGIN = true;
    public const NOTIFY_PASSWORD_CHANGE = false;
    public const NOTIFY_FAILED_ATTEMPTS = true;
    public const NOTIFY_ACCOUNT_LOCKOUT = true;
    public const NOTIFY_PASSWORD_RESET = true;

    // Common passwords to disallow
    private const COMMON_PASSWORDS = [
        // Top 50 most common passwords
        '123456', 'password', '12345678', 'qwerty', '123456789',
        '12345', '1234', '111111', '1234567', 'dragon',
        '123123', 'baseball', 'abc123', 'football', 'monkey',
        'letmein', '696969', 'shadow', 'master', '666666',
        'qwertyuiop', '123321', 'mustang', '1234567890', 'michael',
        '654321', 'superman', '1qaz2wsx', '7777777', 'trustno1',
        '121212', '000000', 'qazwsx', '123qwe', 'killer',
        'jordan', 'jennifer', 'zxcvbnm', 'asdfgh', 'hunter',
        'buster', 'soccer', 'harley', 'batman', 'andrew',

        // Numeric and sequential patterns
        '1111', '2222', '3333', '4444', '5555', '6666', '7777',
        '8888', '9999', '0000', '112233', '121212', '654321',
        '12345678910', '987654321', '13579', '246810', '09876',
        '102030', '111222', '222333', '333444', '444555', '555666',
        
        // Keyboard patterns
        'qwerty', 'asdfgh', 'zxcvbn', 'qazwsx', 'qwertyuiop',
        'asdfghjkl', 'zxcvbnm', '1qaz2wsx', 'qwaszx', 'zxcasd',
        'qweasdzxc', 'qazwsxedc', 'zaqxsw', 'xswzaq', 'poiuyt',
        'lkjhgf', 'mnbvcx', '1q2w3e4r5t', '0987poi', 'zaqwsxcde',
        
        // Common words and names
        'admin', 'administrator', 'root', 'login', 'welcome',
        'sample', 'test', 'secure', 'changeme', 'secret',
        'access', 'master', 'hello', 'user', 'pass',
        'system', 'computer', 'internet', 'network', 'server',
        'database', 'website', 'office', 'home', 'desktop',
        'laptop', 'tablet', 'phone', 'android', 'apple',
        'windows', 'linux', 'google', 'facebook', 'twitter',
        'instagram', 'snapchat', 'youtube', 'netflix', 'amazon',
        
        // Pop culture references
        'starwars', 'pokemon', 'superman', 'batman', 'spiderman',
        'ironman', 'marvel', 'avengers', 'harrypotter', 'hogwarts',
        'gandalf', 'frodo', 'legolas', 'gollum', 'naruto',
        'dragonball', 'matrix', 'terminator', 'startrek', 'skywalker',
        'vader', 'jedi', 'sith', 'yoda', 'pikachu',
        'charizard', 'zelda', 'mario', 'luigi', 'nintendo',
        'playstation', 'xbox', 'callofduty', 'minecraft', 'fortnite',
        'pubg', 'halo', 'godofwar', 'finalfantasy', 'kingdomhearts',
        
        // Emotional terms
        'iloveyou', 'loveyou', 'love', 'forever', 'always',
        'sunshine', 'angel', 'lovely', 'sweetheart', 'honey',
        'beautiful', 'baby', 'princess', 'prince', 'king',
        'queen', 'knight', 'warrior', 'hero', 'happy',
        'smile', 'friend', 'family', 'blessed', 'grateful',
        
        // Seasonal and event-based passwords
        'newyear', 'christmas', 'easter', 'halloween', 'thanksgiving',
        'valentine', 'summer2024', 'winter2024', 'spring2024', 'fall2024',
        'blackfriday', 'cybermonday', 'boxingday', 'independence', 'fireworks',
        
        // Sports and teams
        'football', 'basketball', 'baseball', 'soccer', 'hockey',
        'tennis', 'golf', 'cricket', 'formula1', 'nascar',
        'liverpool', 'barcelona', 'madrid', 'chelsea', 'manutd',
        'cowboys', 'yankees', 'lakers', 'heatnation', 'redsox',
        
        // Movie & TV references
        'breakingbad', 'gameofthrones', 'strangerthings', 'simpsons', 'futurama',
        'southpark', 'friends', 'sopranos', 'darkknight', 'joker2024',
        
        // Random common passwords
        'abcd1234', 'passw0rd', 'ncc1701', 'trustme', 'iamroot',
        'mypass', 'ninja', 'letmein123', 'notpassword', 'testpassword',
        
        // Hacker & IT related passwords
        'root123', 'hacker', 'l33th4x0r', '1337pass', 'p4ssw0rd!',
        'admin123', 'toor', 'cisco123', 'networkadmin', 'backdoor',
        
        // Car brands and models
        'ferrari', 'mustang', 'tesla', 'porsche911', 'camaro',
        'lamborghini', 'bmw2024', 'audiR8', 'mercedesAMG', 'corvette',
        
        // Anime and gaming passwords
        'animefan', 'otaku123', 'pokemonfan', 'finalfantasy7', 'cloudstrife',
        'onepiece', 'deathnote', 'attackontitan', 'jojo2024', 'evangelion'
    ];

    /**
     * Validate a username based on the defined rules,
     * including checks for SQL injection and XSS attack patterns.
     */
    public static function validateUsername(string $username): array
    {
        $errors = [];
        $username = trim($username); 

        // Check min and max length
        $length = strlen($username);
        if ($length < self::USERNAME_MIN_LENGTH) {
            $errors[] = "Username must be at least " . self::USERNAME_MIN_LENGTH . " characters long.";
        }

        if ($length > self::USERNAME_MAX_LENGTH) {
            $errors[] = "Username must not exceed " . self::USERNAME_MAX_LENGTH . " characters.";
        }

        // Check pattern
        if (!preg_match(self::USERNAME_PATTERN, $username)) {
            $errors[] = self::USERNAME_PATTERN_MSG;
        }

        // Check for SQL injection patterns
        $sqlInjectionPatterns = [
            '/--/',                    // SQL comment
            '/\/\*.*?\*\//',           // Block comments
            '/;/',                     // Semicolons
            '/drop\s+table/i',         // DROP TABLE
            '/alter\s+table/i',        // ALTER TABLE
            '/exec\s*\(/i',            // EXEC functions
            '/xp_cmdshell/i',          // xp_cmdshell
            '/select\s+.*?\s+from/i',  // SELECT FROM
            '/insert\s+into/i',        // INSERT INTO
            '/update\s+.*?\s+set/i',   // UPDATE SET
            '/delete\s+from/i'         // DELETE FROM
        ];

        foreach ($sqlInjectionPatterns as $pattern) {
            if (preg_match($pattern, $username)) {
                $errors[] = "Username contains potential SQL injection patterns.";
                break;
            }
        }

        // Check for XSS attack patterns
        $xssPatterns = [
            // Script and JavaScript related
            '/<script/i',              // <script tags
            '/<\/script/i',            // </script tags
            '/javascript:/i',          // javascript: protocol
            '/on\w+\s*=/i',            // event handlers like onclick=
            '/eval\s*\(/i',            // eval()
            '/expression\s*\(/i',      // expression()
            '/document\.cookie/i',     // document.cookie
            '/alert\s*\(/i',           // alert()
            '/document\.location/i',   // document.location
            '/document\.write/i',      // document.write
            '/window\./i',             // window object access
            '/setTimeout\s*\(/i',      // setTimeout
            '/setInterval\s*\(/i',     // setInterval
            '/Function\s*\(/i',        // Function constructor
            '/fromCharCode/i',         // String.fromCharCode
            
            // HTML tags - comprehensive list
            '/<a[^>]*>/i',             // <a> links
            '/<iframe/i',              // <iframe> tags
            '/<embed/i',               // <embed> tags
            '/<object/i',              // <object> tags
            '/<img/i',                 // <img> tags
            '/<svg/i',                 // <svg> tags
            '/<form/i',                // <form> tags
            '/<input/i',               // <input> tags
            '/<button/i',              // <button> tags
            '/<meta/i',                // <meta> tags
            '/<div/i',                 // <div> tags
            '/<span/i',                // <span> tags
            '/<body/i',                // <body> tags
            '/<style/i',               // <style> tags
            '/<link/i',                // <link> tags
            '/<frame/i',               // <frame> tags
            '/<frameset/i',            // <frameset> tags
            '/<applet/i',              // <applet> tags
            '/<audio/i',               // <audio> tags
            '/<video/i',               // <video> tags
            '/<source/i',              // <source> tags
            '/<base/i',                // <base> tags
            '/<canvas/i',              // <canvas> tags
            '/<marquee/i',             // <marquee> tags
            '/<table/i',               // <table> tags
            '/<textarea/i',            // <textarea> tags
            '/<select/i',              // <select> tags
            '/<noscript/i',            // <noscript> tags
            
            // Event attributes
            '/on(load|unload|click|dblclick|mousedown|mouseup|mouseover|mousemove|mouseout|focus|blur|change|submit|reset|select|keydown|keypress|keyup|error|drag|drop)/i',
            
            // Encoding and data schemes
            '/&#x/i',                  // hex encoding
            '/&#\d+;/i',               // decimal encoding
            '/base64/i',               // base64 encoding
            '/data:\s*[^,]*,/i',       // data URI scheme
            '/vbscript:/i',            // vbscript protocol
            '/about:/i',               // about protocol
            
            // Other potential XSS vectors
            '/<!\[CDATA\[/i',          // CDATA section
            '/<!\-\-/i',               // HTML comments
            '/\/\*.*?\*\//i',          // CSS/JS comments
            '/url\s*\(/i',             // CSS url()
            '/:expression\s*\(/i',     // CSS expressions
            '/behaviour:/i',           // CSS behavior
            '/import:/i',              // CSS import
            '/charset=/i'              // Charset specification
        ];

        foreach ($xssPatterns as $pattern) {
            if (preg_match($pattern, $username)) {
                $errors[] = "Username contains potential XSS attack patterns.";
                break;
            }
        }

        return $errors;
    }

    /**
     * Validate a password based on the defined rules.
     */
    public static function validatePassword(string $password, ?string $username = null, ?string $email = null): array
    {
        $errors = [];

        // Check min and max length
        $length = strlen($password);
        if ($length < self::PASSWORD_MIN_LENGTH) {
            $errors[] = "Password must be at least " . self::PASSWORD_MIN_LENGTH . " characters long.";
        }
        
        if ($length > self::PASSWORD_MAX_LENGTH) {
            $errors[] = "Password must not exceed " . self::PASSWORD_MAX_LENGTH . " characters.";
        }

        // Check complexity requirements only if enabled
        if (self::PASSWORD_COMPLEXITY_ENABLED) {
            $complexityErrors = [];

            // Check individual complexity requirements
            if (self::REQUIRE_UPPERCASE && !preg_match('/[A-Z]/', $password)) {
                $complexityErrors[] = "at least one uppercase letter";
            }

            if (self::REQUIRE_LOWERCASE && !preg_match('/[a-z]/', $password)) {
                $complexityErrors[] = "at least one lowercase letter";
            }

            if (self::REQUIRE_NUMBER && !preg_match('/[0-9]/', $password)) {
                $complexityErrors[] = "at least one number";
            }

            if (self::REQUIRE_SPECIAL_CHAR && !preg_match('/[@$!%*?&]/', $password)) {
                $complexityErrors[] = "at least one special character (@$!%*?&)";
            }

            if (!empty($complexityErrors)) {
                $errors[] = "Password must contain " . implode(", ", $complexityErrors) . ".";
            }
        }

        // Check if password contains username
        if (self::DISALLOW_USERNAME_IN_PASSWORD && $username && stripos($password, $username) !== false) {
            $errors[] = "Password cannot contain your username.";
        }

        // Check if password contains email
        if (self::DISALLOW_EMAIL_IN_PASSWORD && $email) {
            $emailParts = explode('@', $email);
            if (stripos($password, $emailParts[0]) !== false) {
                $errors[] = "Password cannot contain your email address.";
            }
        }

        // Check against common passwords
        if (self::CHECK_AGAINST_COMMON_PASSWORDS && in_array(strtolower($password), self::COMMON_PASSWORDS)) {
            $errors[] = "Password is too common and easily guessable. Please choose a stronger password.";
        }

        return $errors;
    }

    /**
     * Check if the account should be locked due to too many failed attempts.
     */
    public static function shouldLockAccount(int $failedAttempts): bool
    {
        return $failedAttempts >= self::MAX_FAILED_ATTEMPTS;
    }

    /**
     * Check if the password has expired.
     */
    public static function isPasswordExpired(int $lastChangedTimestamp): bool
    {
        $now = time();
        $daysSinceChange = ($now - $lastChangedTimestamp) / (60 * 60 * 24);
        return $daysSinceChange > self::PASSWORD_EXPIRATION_DAYS;
    }

    /**
     * Check if password is approaching expiration and should trigger a warning.
     */
    public static function shouldWarnPasswordExpiration(int $lastChangedTimestamp): array
    {
        $now = time();
        $daysSinceChange = ($now - $lastChangedTimestamp) / (60 * 60 * 24);
        $daysUntilExpiration = self::PASSWORD_EXPIRATION_DAYS - $daysSinceChange;

        if ($daysUntilExpiration <= self::PASSWORD_EXPIRATION_WARNING_DAYS && $daysUntilExpiration > 0) {
            return [
                'warning' => true,
                'days_remaining' => floor($daysUntilExpiration),
                'message' => str_replace('{days}', floor($daysUntilExpiration), self::PASSWORD_EXPIRATION_WARNING_MSG)
            ];
        }

        return ['warning' => false];
    }

    /**
     * Check if the new password is different from recent passwords.
     * @param string $newPassword The new password.
     * @param array $oldPasswords Array of previously used passwords (hashed).
     */
    public static function isPasswordInHistory(string $newPassword, array $oldPasswords): bool
    {
        foreach ($oldPasswords as $oldPasswordHash) {
            if (password_verify($newPassword, $oldPasswordHash)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if the new password is sufficiently different from the current password.
     * A simple implementation - in production, use more sophisticated comparison.
     */
    public static function isPasswordTooSimilar(string $newPassword, string $currentPassword): bool
    {
        // Count different characters
        $differentChars = 0;
        $maxLength = max(strlen($newPassword), strlen($currentPassword));

        for ($i = 0; $i < $maxLength; $i++) {
            $newChar = $i < strlen($newPassword) ? $newPassword[$i] : '';
            $currentChar = $i < strlen($currentPassword) ? $currentPassword[$i] : '';

            if ($newChar !== $currentChar) {
                $differentChars++;
            }
        }

        return $differentChars < self::PASSWORD_DIFFERENT_CURRENT;
    }

    /**
     * Check if the session has timed out due to inactivity.
     */
    public static function hasSessionTimedOut(int $lastActivityTimestamp): bool
    {
        $inactivityTimeout = self::SESSION_INACTIVITY_TIMEOUT_MINUTES * 60; // Convert to seconds
        return (time() - $lastActivityTimestamp) > $inactivityTimeout;
    }

    /**
     * Check if the session has exceeded its maximum duration.
     */
    public static function hasSessionExceededMaxDuration(int $sessionStartTimestamp): bool
    {
        $maxDuration = self::SESSION_MAX_DURATION_HOURS * 3600; // Convert to seconds
        return (time() - $sessionStartTimestamp) > $maxDuration;
    }

    /**
     * Determine if MFA is required for a user.
     */
    public static function isMFARequired(bool $isAdmin, bool $hasElevatedPrivileges): bool
    {
        if (!self::MFA_AVAILABLE) {
            return false;
        }

        if (self::MFA_REQUIRED_FOR_ADMIN && ($isAdmin || $hasElevatedPrivileges)) {
            return true;
        }

        return false;
    }

    /**
     * Run full password validation (length, complexity, expiration, history, similarity).
     * @param string $newPassword The new password.
     * @param string $username The user's username.
     * @param string $email The user's email.
     * @param int $lastChangedTimestamp Last password change timestamp.
     * @param array $oldPasswords List of previously used password hashes.
     * @param string $currentPassword The current password (unhashed).
     */
    public static function validateFullPassword(
        string $newPassword,
        ?string $username = null,
        ?string $email = null,
        int $lastChangedTimestamp = 0,
        array $oldPasswords = [],
        string $currentPassword = ''
    ): array {
        $errors = self::validatePassword($newPassword, $username, $email);

        // Check expiration
        if ($lastChangedTimestamp > 0 && self::isPasswordExpired($lastChangedTimestamp)) {
            $errors[] = self::PASSWORD_EXPIRATION_MSG;
        }

        // Check password history
        if (!empty($oldPasswords) && self::isPasswordInHistory($newPassword, $oldPasswords)) {
            $errors[] = self::PASSWORD_HISTORY_MSG;
        }

        // Check if password is too similar to the current one
        if (!empty($currentPassword) && self::isPasswordTooSimilar($newPassword, $currentPassword)) {
            $errors[] = self::PASSWORD_DIFFERENT_CURRENT_MSG;
        }

        return $errors;
    }

    /**
     * Check if a login attempt should trigger a security notification.
     * @param string $ipAddress The IP address of the login attempt.
     * @param array $knownDevices List of known device identifiers for this user.
     * @param int $failedAttempts Number of recent failed attempts.
     */
    public static function shouldSendSecurityNotification(
        string $ipAddress,
        array $knownDevices = [],
        int $failedAttempts = 0
    ): array {
        $notifications = [];

        // New device login
        if (self::NOTIFY_NEW_DEVICE_LOGIN && !empty($knownDevices) && !in_array($ipAddress, $knownDevices)) {
            $notifications[] = "new_device_login";
        }

        // Failed attempts notification
        if (self::NOTIFY_FAILED_ATTEMPTS && $failedAttempts >= 3) {
            $notifications[] = "failed_attempts";
        }

        // Account lockout notification
        if (self::NOTIFY_ACCOUNT_LOCKOUT && $failedAttempts >= self::MAX_FAILED_ATTEMPTS) {
            $notifications[] = "account_lockout";
        }

        return $notifications;
    }
}
