<?php
// backend/publico/index.php

// Esto es CRUCIAL. Carga todas las librerías de Composer.
require_once __DIR__ . '/../vendor/autoload.php';

// Activar reporte de errores para desarrollo (quitar en producción)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Manejar las cabeceras CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Manejar peticiones preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Cargar las rutas de la aplicación
require_once __DIR__ . '/../app/rutas.php';

// Despachar la solicitud usando el enrutador definido en app/rutas.php
if (isset($enrutador)) {
    $enrutador->dispatch();
} else {
    http_response_code(500);
    echo json_encode(['estado' => 'error', 'mensaje' => 'Enrutador no inicializado']);
}
