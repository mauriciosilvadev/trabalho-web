<?php

/**
 * Authentication and session management
 */
class Auth
{
    private static $sessionStarted = false;

    /**
     * Start session if not already started
     */
    public static function startSession(): void
    {
        if (!self::$sessionStarted && session_status() === PHP_SESSION_NONE) {
            session_start();
            self::$sessionStarted = true;
        }
    }

    /**
     * Check if user is authenticated
     */
    public static function isAuthenticated(): bool
    {
        self::startSession();
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    /**
     * Get current authenticated user ID
     */
    public static function getUserId(): ?int
    {
        self::startSession();
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Get current authenticated user data
     */
    public static function getUser(): ?array
    {
        self::startSession();
        return $_SESSION['user'] ?? null;
    }

    /**
     * Login user
     */
    public static function login(array $user, bool $remember = false): void
    {
        self::startSession();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user'] = $user;

        if ($remember) {
            self::setRememberToken($user['id']);
        }

        session_regenerate_id(true);
    }

    /**
     * Logout user
     */
    public static function logout(): void
    {
        self::startSession();

        // Remove remember token from database
        if (isset($_SESSION['user_id'])) {
            self::clearRememberToken($_SESSION['user_id']);
        }

        // Clear session
        $_SESSION = [];

        // Destroy session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        // Clear remember cookie
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
        }

        session_destroy();
    }

    /**
     * Set remember token for user
     */
    private static function setRememberToken(int $userId): void
    {
        $token = bin2hex(random_bytes(32));
        $hashedToken = hash('sha256', $token);

        try {
            require_once __DIR__ . '/../dao/UsuarioDAO.php';
            $userDAO = new UsuarioDAO();
            $userDAO->updateRememberToken($userId, $hashedToken);

            // Set cookie for 30 days
            setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/');
        } catch (Exception $e) {
            error_log("Failed to set remember token: " . $e->getMessage());
        }
    }

    /**
     * Clear remember token for user
     */
    private static function clearRememberToken(int $userId): void
    {
        try {
            require_once __DIR__ . '/../dao/UsuarioDAO.php';
            $userDAO = new UsuarioDAO();
            $userDAO->clearRememberToken($userId);
        } catch (Exception $e) {
            error_log("Failed to clear remember token: " . $e->getMessage());
        }
    }

    /**
     * Check remember token and auto-login
     */
    public static function checkRememberToken(): void
    {
        if (self::isAuthenticated() || !isset($_COOKIE['remember_token'])) {
            return;
        }

        $token = $_COOKIE['remember_token'];
        $hashedToken = hash('sha256', $token);

        try {
            require_once __DIR__ . '/../dao/UsuarioDAO.php';
            $userDAO = new UsuarioDAO();
            $user = $userDAO->findByRememberToken($hashedToken);

            if ($user) {
                self::login($user, true);
            } else {
                // Invalid token, clear cookie
                setcookie('remember_token', '', time() - 3600, '/');
            }
        } catch (Exception $e) {
            error_log("Failed to check remember token: " . $e->getMessage());
        }
    }

    /**
     * Require authentication, redirect to login if not authenticated
     */
    public static function requireAuth(): void
    {
        self::checkRememberToken();

        if (!self::isAuthenticated()) {
            header('Location: /trabalho/index.php');
            exit;
        }
    }

    /**
     * Generate CSRF token
     */
    public static function generateCSRFToken(): string
    {
        self::startSession();
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Verify CSRF token
     */
    public static function verifyCSRFToken(string $token): bool
    {
        self::startSession();
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}
