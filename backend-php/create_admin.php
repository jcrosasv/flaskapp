<?php
/**
 * Script para crear un usuario administrador
 */

require_once 'config.php';

initializeDatabase();
$pdo = getPDO();

// Crear usuario admin con contraseña predeterminada
$username = 'admin';
$password = password_hash('admin123', PASSWORD_BCRYPT);

try {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    
    if ($stmt->fetch()) {
        echo "El usuario 'admin' ya existe\n";
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $stmt->execute([$username, $password, 'admin']);
        echo "Usuario 'admin' creado exitosamente\n";
        echo "Username: admin\n";
        echo "Password: admin123\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
