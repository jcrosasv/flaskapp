<?php
/**
 * Archivo de configuración para la aplicación PHP
 */

// Configuración base
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'flask_app');
define('SECRET_KEY', getenv('SECRET_KEY') ?: 'your-secret-key-change-this-in-production');
define('UPLOAD_FOLDER', __DIR__ . '/../uploads');
define('MAX_UPLOAD_SIZE', 50 * 1024 * 1024); // 50MB
define('ALLOWED_EXTENSIONS', ['xlsx', 'xls']);

// Crear carpeta de uploads si no existe
if (!is_dir(UPLOAD_FOLDER)) {
    mkdir(UPLOAD_FOLDER, 0755, true);
}

// Configuración de base de datos
$config = [
    'development' => [
        'db_type' => 'sqlite',
        'db_path' => __DIR__ . '/../users.db',
        'debug' => true,
    ],
    'production' => [
        'db_type' => 'mysql',
        'db_host' => DB_HOST,
        'db_user' => DB_USER,
        'db_pass' => DB_PASS,
        'db_name' => DB_NAME,
        'debug' => false,
    ]
];

// Seleccionar configuración según ambiente
$environment = getenv('ENVIRONMENT') ?: 'development';
$current_config = $config[$environment] ?? $config['development'];

// Función para obtener PDO según el ambiente
function getPDO() {
    global $current_config;
    
    try {
        if ($current_config['db_type'] === 'sqlite') {
            $pdo = new PDO('sqlite:' . $current_config['db_path']);
        } else {
            $dsn = 'mysql:host=' . $current_config['db_host'] . ';dbname=' . $current_config['db_name'];
            $pdo = new PDO($dsn, $current_config['db_user'], $current_config['db_pass']);
        }
        
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        http_response_code(500);
        die(json_encode(['success' => false, 'message' => 'Error de conexión a base de datos: ' . $e->getMessage()]));
    }
}

// Función para inicializar base de datos
function initializeDatabase() {
    global $current_config;
    
    try {
        $pdo = getPDO();
        
        // Crear tabla de usuarios
        if ($current_config['db_type'] === 'sqlite') {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS users (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    username TEXT UNIQUE NOT NULL,
                    password TEXT NOT NULL,
                    role TEXT DEFAULT 'user',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )
            ");
        } else {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS users (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    username VARCHAR(80) UNIQUE NOT NULL,
                    password VARCHAR(255) NOT NULL,
                    role VARCHAR(20) DEFAULT 'user',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )
            ");
        }
        
        // Crear tabla de logs de subidas
        if ($current_config['db_type'] === 'sqlite') {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS upload_logs (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    filename TEXT NOT NULL,
                    username TEXT NOT NULL,
                    upload_date DATETIME DEFAULT CURRENT_TIMESTAMP,
                    total_records INTEGER NOT NULL
                )
            ");
        } else {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS upload_logs (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    filename VARCHAR(255) NOT NULL,
                    username VARCHAR(80) NOT NULL,
                    upload_date DATETIME DEFAULT CURRENT_TIMESTAMP,
                    total_records INT NOT NULL
                )
            ");
        }
        
        // Crear usuario admin por defecto si no existe
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = 'admin'");
        $stmt->execute();
        
        if (!$stmt->fetch()) {
            $hashedPassword = password_hash('admin123', PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            $stmt->execute(['admin', $hashedPassword, 'admin']);
        }
        
    } catch (Exception $e) {
        die("Error al inicializar la base de datos: " . $e->getMessage());
    }
}

// Función para validar si la extensión es permitida
function isAllowedFile($filename) {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($ext, ALLOWED_EXTENSIONS);
}

// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Manejo de preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
