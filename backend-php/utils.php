<?php
/**
 * Funciones auxiliares para la aplicación PHP
 */

require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Valida que el archivo sea un Excel válido
 */
function validateExcelFile($filepath) {
    try {
        $spreadsheet = IOFactory::load($filepath);
        return true;
    } catch (Exception $e) {
        error_log("Error validando archivo: " . $e->getMessage());
        return false;
    }
}

/**
 * Lee un archivo Excel y retorna encabezados y datos
 */
function readExcelFile($filepath) {
    try {
        $spreadsheet = IOFactory::load($filepath);
        $worksheet = $spreadsheet->getActiveSheet();
        
        $columns = [];
        $data = [];
        
        // Obtener encabezados (primera fila)
        foreach ($worksheet->getRowIterator(1, 1) as $row) {
            foreach ($row->getCellIterator() as $cell) {
                $columns[] = $cell->getValue();
            }
        }
        
        // Obtener datos (desde la segunda fila)
        foreach ($worksheet->getRowIterator(2) as $row) {
            $rowData = [];
            foreach ($row->getCellIterator() as $cell) {
                $rowData[] = $cell->getValue();
            }
            
            // Solo agregar si no está vacía
            if (array_filter($rowData, function($val) { return $val !== null && $val !== ''; })) {
                $data[] = $rowData;
            }
        }
        
        return [$columns, $data];
    } catch (Exception $e) {
        error_log("Error leyendo archivo: " . $e->getMessage());
        return [[], []];
    }
}

/**
 * Normaliza texto para búsqueda
 */
function normalizeSearchText($text) {
    return strtolower(trim((string)$text));
}

/**
 * Busca en datos del Excel por cualquier columna
 * Requiere mínimo 3 caracteres y busca parcialmente
 */
function searchInExcelData($data, $columns, $searchText) {
    $searchNormalized = normalizeSearchText($searchText);
    
    // Validar mínimo de caracteres
    if (strlen($searchNormalized) < 3) {
        return [];
    }
    
    $results = [];
    
    foreach ($data as $row) {
        foreach ($row as $value) {
            if (strpos(normalizeSearchText($value), $searchNormalized) !== false) {
                $results[] = $row;
                break;
            }
        }
    }
    
    return $results;
}

/**
 * Formatea los resultados de búsqueda
 */
function formatSearchResults($columns, $results) {
    if (empty($results)) {
        return [];
    }
    
    // Encontrar índice de la columna LINK
    $linkColIndex = null;
    foreach ($columns as $i => $col) {
        if (strtoupper($col) === 'LINK') {
            $linkColIndex = $i;
            break;
        }
    }
    
    // Agregar encabezados como primera fila
    $formatted = [$columns];
    
    // Agregar datos
    foreach ($results as $row) {
        $formattedRow = [];
        foreach ($row as $i => $cell) {
            if ($i === $linkColIndex && $cell) {
                $formattedRow[] = "EVIDENCIA|" . (string)$cell;
            } else {
                $formattedRow[] = (string)($cell ?? '');
            }
        }
        $formatted[] = $formattedRow;
    }
    
    return $formatted;
}

/**
 * Verifica si la extensión del archivo está permitida
 */
function isAllowedFile($filename) {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($ext, ALLOWED_EXTENSIONS);
}

/**
 * Retorna respuesta JSON
 */
function jsonResponse($success, $message, $data = null, $httpCode = 200) {
    http_response_code($httpCode);
    
    $response = [
        'success' => $success,
        'message' => $message
    ];
    
    if ($data !== null) {
        $response = array_merge($response, $data);
    }
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit();
}

/**
 * Obtiene valor seguro de array
 */
function getPost($key, $default = null) {
    return $_POST[$key] ?? $default;
}

/**
 * Obtiene valor seguro de GET
 */
function getQuery($key, $default = null) {
    return $_GET[$key] ?? $default;
}

/**
 * Obtiene JSON del body de la request
 */
function getJSON() {
    $input = file_get_contents('php://input');
    return json_decode($input, true) ?? [];
}
