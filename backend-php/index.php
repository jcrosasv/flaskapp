<?php
/**
 * Archivo principal que sirve como index y enrutador
 */

require_once 'config.php';
require_once 'utils.php';
require_once 'Session.php';

// Redirigir solicitudes a api.php
if (strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
    require 'api.php';
} else {
    // Redirigir a index.html para SPA
    header('Location: ../front/index.html');
    exit();
}
