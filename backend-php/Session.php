<?php
/**
 * Clase para manejar sesiones
 */
class Session {
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public static function set($key, $value) {
        self::start();
        $_SESSION[$key] = $value;
    }
    
    public static function get($key, $default = null) {
        self::start();
        return $_SESSION[$key] ?? $default;
    }
    
    public static function has($key) {
        self::start();
        return isset($_SESSION[$key]);
    }
    
    public static function remove($key) {
        self::start();
        unset($_SESSION[$key]);
    }
    
    public static function destroy() {
        self::start();
        $_SESSION = [];
        session_destroy();
    }
    
    public static function isLoggedIn() {
        return self::has('user_id');
    }
    
    public static function isAdmin() {
        return self::get('is_admin', false);
    }
    
    public static function getCurrentUser($pdo) {
        if (!self::has('user_id')) {
            return null;
        }
        
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([self::get('user_id')]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return null;
        }
    }
}
