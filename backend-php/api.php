<?php
/**
 * API principal de la aplicación PHP
 */

require_once 'config.php';
require_once 'utils.php';
require_once 'Session.php';

// Inicializar base de datos
initializeDatabase();

// Obtener conexión a BD
$pdo = getPDO();

// Variables globales para almacenar datos del Excel (en producción usar Redis o caché)
$currentData = [];
$currentColumns = [];

// Rutas
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = str_replace('/backend-php/', '', $path);
$path = str_replace('/api/v1/', '', $path);

// Enrutador
switch ($path) {
    case 'file/login':
    case 'api/v1/file/login':
        handleLogin($pdo);
        break;
    
    case 'file/search':
    case 'api/v1/file/search':
        handleSearch();
        break;
    
    case 'file/first-data':
    case 'api/v1/file/first-data':
        handleFirstData();
        break;
    
    case 'file/upload':
    case 'api/v1/file/upload':
        handleUpload($pdo);
        break;
    
    case 'users':
    case 'api/v1/users':
        handleGetUsers($pdo);
        break;
    
    case 'register':
    case 'api/v1/register':
        if ($method === 'POST') {
            handleRegister($pdo);
        }
        break;
    
    case 'change-password':
    case 'api/v1/change-password':
        if ($method === 'POST') {
            handleChangePassword($pdo);
        }
        break;
    
    case 'delete-user':
    case 'api/v1/delete-user':
        if ($method === 'POST') {
            handleDeleteUser($pdo);
        }
        break;
    
    case 'upload-logs':
    case 'api/v1/upload-logs':
        handleGetUploadLogs($pdo);
        break;
    
    case 'logout':
        handleLogout();
        break;
    
    default:
        http_response_code(404);
        jsonResponse(false, 'Endpoint no encontrado');
}

/**
 * Maneja login
 */
function handleLogin($pdo) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = getJSON();
        $username = $data['username'] ?? null;
        $password = $data['password'] ?? null;
        
        if (!$username || !$password) {
            jsonResponse(false, 'Usuario y contraseña requeridos', null, 400);
        }
        
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                Session::set('user_id', $user['id']);
                Session::set('username', $user['username']);
                Session::set('is_admin', $user['role'] === 'admin');
                
                jsonResponse(true, 'Inicio de sesión exitoso', [
                    'is_admin' => $user['role'] === 'admin'
                ]);
            } else {
                jsonResponse(false, 'Usuario o contraseña incorrectos', null, 401);
            }
        } catch (Exception $e) {
            error_log("Error en login: " . $e->getMessage());
            jsonResponse(false, 'Error al iniciar sesión: ' . $e->getMessage(), null, 500);
        }
    }
}

/**
 * Maneja búsqueda
 */
function handleSearch() {
    if (!Session::isLoggedIn()) {
        jsonResponse(false, 'No autorizado', null, 401);
    }
    
    global $currentData, $currentColumns;
    
    $searchText = getQuery('searchText', '');
    $page = (int)(getQuery('page', 1));
    $perPage = (int)(getQuery('per_page', 20));
    
    if (!$searchText) {
        jsonResponse(false, 'Texto de búsqueda requerido', null, 400);
    }
    
    if ($page < 1) $page = 1;
    if ($perPage < 1 || $perPage > 100) $perPage = 20;
    
    // Verificar si hay datos cargados
    if (empty($currentData) || empty($currentColumns)) {
        jsonResponse(true, 'No hay datos cargados. Por favor sube un archivo Excel primero', [
            'data' => [],
            'total' => 0,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => 0
        ]);
    }
    
    try {
        $results = searchInExcelData($currentData, $currentColumns, $searchText);
        $formattedResults = formatSearchResults($currentColumns, $results);
        
        $headerRow = $formattedResults[0] ?? [];
        $dataRows = array_slice($formattedResults, 1);
        
        $totalRecords = count($dataRows);
        $totalPages = $totalRecords > 0 ? ceil($totalRecords / $perPage) : 0;
        
        if ($page > $totalPages && $totalPages > 0) {
            $page = $totalPages;
        }
        
        $startIdx = ($page - 1) * $perPage;
        $paginatedData = array_slice($dataRows, $startIdx, $perPage);
        $responseData = array_merge([$headerRow], $paginatedData);
        
        jsonResponse(true, 'Búsqueda exitosa', [
            'data' => $responseData,
            'total' => $totalRecords,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => $totalPages
        ]);
    } catch (Exception $e) {
        error_log("Error en búsqueda: " . $e->getMessage());
        jsonResponse(false, 'Error al buscar: ' . $e->getMessage(), null, 500);
    }
}

/**
 * Maneja obtener primeros datos
 */
function handleFirstData() {
    if (!Session::isLoggedIn()) {
        jsonResponse(false, 'No autorizado', null, 401);
    }
    
    global $currentData, $currentColumns;
    
    if (empty($currentData) || empty($currentColumns)) {
        jsonResponse(true, 'Sin datos', [
            'data' => [],
            'total' => 0,
            'page' => 1,
            'per_page' => 50,
            'total_pages' => 0
        ]);
    }
    
    $first50 = array_slice($currentData, 0, 50);
    $formattedResults = formatSearchResults($currentColumns, $first50);
    
    jsonResponse(true, 'Datos obtenidos', [
        'data' => $formattedResults,
        'total' => count($currentData),
        'page' => 1,
        'per_page' => 50,
        'total_pages' => ceil(count($currentData) / 50)
    ]);
}

/**
 * Maneja carga de archivos
 */
function handleUpload($pdo) {
    global $currentData, $currentColumns;
    
    if (!Session::isLoggedIn()) {
        jsonResponse(false, 'No autorizado', null, 401);
    }
    
    $user = Session::getCurrentUser($pdo);
    if (!$user || $user['role'] !== 'admin') {
        jsonResponse(false, 'Solo administradores pueden subir archivos', null, 403);
    }
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(false, 'Método no permitido', null, 405);
    }
    
    if (!isset($_FILES['file'])) {
        jsonResponse(false, 'No se seleccionó archivo', null, 400);
    }
    
    $file = $_FILES['file'];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        jsonResponse(false, 'Error al subir archivo', null, 400);
    }
    
    if (!isAllowedFile($file['name'])) {
        jsonResponse(false, 'Solo se aceptan archivos Excel', null, 400);
    }
    
    if ($file['size'] > MAX_UPLOAD_SIZE) {
        jsonResponse(false, 'El archivo excede el tamaño máximo permitido', null, 400);
    }
    
    try {
        // Limpiar datos anteriores
        $currentData = [];
        $currentColumns = [];
        
        // Guardar archivo
        $filename = basename($file['name']);
        $filepath = UPLOAD_FOLDER . '/' . $filename;
        
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            jsonResponse(false, 'Error al guardar el archivo', null, 500);
        }
        
        // Validar archivo
        if (!validateExcelFile($filepath)) {
            unlink($filepath);
            jsonResponse(false, 'El archivo no es un Excel válido', null, 400);
        }
        
        // Leer datos
        list($columns, $data) = readExcelFile($filepath);
        
        if (empty($columns) || empty($data)) {
            unlink($filepath);
            jsonResponse(false, 'El archivo no contiene datos válidos', null, 400);
        }
        
        // Validar duplicados (cédula + comparendo)
        $seenCombinations = [];
        $filteredData = [];
        $duplicatesFound = [];
        
        $cedulaIdx = null;
        $comparendoIdx = null;
        
        foreach ($columns as $i => $col) {
            $colUpper = strtoupper(trim($col));
            if (strpos($colUpper, 'CEDULA') !== false || strpos($colUpper, 'CÉDULA') !== false) {
                $cedulaIdx = $i;
            } elseif (strpos($colUpper, 'COMPARENDO') !== false) {
                $comparendoIdx = $i;
            }
        }
        
        $rowNum = 2;
        foreach ($data as $row) {
            if ($cedulaIdx !== null && $comparendoIdx !== null && isset($row[$cedulaIdx]) && isset($row[$comparendoIdx])) {
                $cedula = (string)$row[$cedulaIdx];
                $comparendo = (string)$row[$comparendoIdx];
                $combination = $cedula . '|' . $comparendo;
                
                if (in_array($combination, $seenCombinations)) {
                    $duplicatesFound[] = [
                        'row' => $rowNum,
                        'cedula' => $cedula,
                        'comparendo' => $comparendo
                    ];
                } else {
                    $seenCombinations[] = $combination;
                    $filteredData[] = $row;
                }
            } else {
                $filteredData[] = $row;
            }
            $rowNum++;
        }
        
        $currentData = $filteredData;
        $currentColumns = $columns;
        
        $recordsCount = count($currentData);
        
        // Guardar en log
        try {
            $stmt = $pdo->prepare("INSERT INTO upload_logs (filename, username, total_records) VALUES (?, ?, ?)");
            $stmt->execute([$filename, $user['username'], $recordsCount]);
        } catch (Exception $logError) {
            error_log("Error guardando log: " . $logError->getMessage());
        }
        
        $message = "Archivo subido y cargado exitosamente con $recordsCount registros únicos";
        jsonResponse(true, $message, [
            'records_loaded' => $recordsCount
        ]);
        
    } catch (Exception $e) {
        error_log("Error en upload: " . $e->getMessage());
        jsonResponse(false, 'Error al subir archivo: ' . $e->getMessage(), null, 500);
    }
}

/**
 * Maneja obtener usuarios
 */
function handleGetUsers($pdo) {
    if (!Session::isLoggedIn()) {
        jsonResponse(false, 'No autorizado', null, 401);
    }
    
    $user = Session::getCurrentUser($pdo);
    if (!$user || $user['role'] !== 'admin') {
        jsonResponse(false, 'Solo administradores pueden ver usuarios', null, 403);
    }
    
    try {
        $stmt = $pdo->query("SELECT id, username, role, created_at FROM users ORDER BY created_at DESC");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        jsonResponse(true, 'Usuarios obtenidos', [
            'users' => $users
        ]);
    } catch (Exception $e) {
        error_log("Error obteniendo usuarios: " . $e->getMessage());
        jsonResponse(false, 'Error al obtener usuarios: ' . $e->getMessage(), null, 500);
    }
}

/**
 * Maneja registro de usuarios
 */
function handleRegister($pdo) {
    if (!Session::isLoggedIn()) {
        jsonResponse(false, 'No autorizado', null, 401);
    }
    
    $user = Session::getCurrentUser($pdo);
    if (!$user || $user['role'] !== 'admin') {
        jsonResponse(false, 'Solo administradores pueden crear usuarios', null, 403);
    }
    
    $data = getJSON();
    $username = $data['username'] ?? null;
    $password = $data['password'] ?? null;
    $role = $data['role'] ?? 'user';
    
    if (!$username || !$password) {
        jsonResponse(false, 'Usuario y contraseña requeridos', null, 400);
    }
    
    try {
        // Verificar si existe
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            jsonResponse(false, 'El usuario ya existe', null, 400);
        }
        
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $stmt->execute([$username, $hashedPassword, $role]);
        
        jsonResponse(true, 'Usuario creado exitosamente', [], 201);
    } catch (Exception $e) {
        error_log("Error registrando usuario: " . $e->getMessage());
        jsonResponse(false, 'Error al crear usuario: ' . $e->getMessage(), null, 500);
    }
}

/**
 * Maneja cambio de contraseña
 */
function handleChangePassword($pdo) {
    if (!Session::isLoggedIn()) {
        jsonResponse(false, 'No autorizado', null, 401);
    }
    
    $user = Session::getCurrentUser($pdo);
    if (!$user || $user['role'] !== 'admin') {
        jsonResponse(false, 'Solo administradores pueden cambiar contraseñas', null, 403);
    }
    
    $data = getJSON();
    $username = $data['username'] ?? null;
    $newPassword = $data['password'] ?? null;
    
    if (!$username || !$newPassword) {
        jsonResponse(false, 'Usuario y contraseña requeridos', null, 400);
    }
    
    try {
        $targetUser = null;
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $targetUser = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$targetUser) {
            jsonResponse(false, 'Usuario no encontrado', null, 404);
        }
        
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = ?");
        $stmt->execute([$hashedPassword, $username]);
        
        jsonResponse(true, "Contraseña de $username cambiada exitosamente");
    } catch (Exception $e) {
        error_log("Error cambiando contraseña: " . $e->getMessage());
        jsonResponse(false, 'Error al cambiar contraseña: ' . $e->getMessage(), null, 500);
    }
}

/**
 * Maneja eliminación de usuarios
 */
function handleDeleteUser($pdo) {
    if (!Session::isLoggedIn()) {
        jsonResponse(false, 'No autorizado', null, 401);
    }
    
    $user = Session::getCurrentUser($pdo);
    if (!$user || $user['role'] !== 'admin') {
        jsonResponse(false, 'Solo administradores pueden eliminar usuarios', null, 403);
    }
    
    $data = getJSON();
    $username = $data['username'] ?? null;
    
    if (!$username) {
        jsonResponse(false, 'Usuario requerido', null, 400);
    }
    
    if ($username === 'admin') {
        jsonResponse(false, 'No se puede eliminar el usuario admin', null, 403);
    }
    
    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $targetUser = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$targetUser) {
            jsonResponse(false, 'Usuario no encontrado', null, 404);
        }
        
        $stmt = $pdo->prepare("DELETE FROM users WHERE username = ?");
        $stmt->execute([$username]);
        
        jsonResponse(true, "Usuario $username eliminado exitosamente");
    } catch (Exception $e) {
        error_log("Error eliminando usuario: " . $e->getMessage());
        jsonResponse(false, 'Error al eliminar usuario: ' . $e->getMessage(), null, 500);
    }
}

/**
 * Maneja obtener logs de carga
 */
function handleGetUploadLogs($pdo) {
    if (!Session::isLoggedIn()) {
        jsonResponse(false, 'No autorizado', null, 401);
    }
    
    $user = Session::getCurrentUser($pdo);
    if (!$user || $user['role'] !== 'admin') {
        jsonResponse(false, 'Solo administradores pueden ver logs', null, 403);
    }
    
    try {
        $stmt = $pdo->query("SELECT id, filename, username, upload_date, total_records FROM upload_logs ORDER BY upload_date DESC");
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        jsonResponse(true, 'Logs obtenidos', [
            'logs' => $logs
        ]);
    } catch (Exception $e) {
        error_log("Error obteniendo logs: " . $e->getMessage());
        jsonResponse(false, 'Error al obtener logs: ' . $e->getMessage(), null, 500);
    }
}

/**
 * Maneja logout
 */
function handleLogout() {
    Session::destroy();
    jsonResponse(true, 'Sesión cerrada');
}
