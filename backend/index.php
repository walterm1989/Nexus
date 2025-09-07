<?php
// backend/index.php

// Las cabeceras CORS son manejadas por .htaccess, las removemos aquí para evitar redundancia.

require_once __DIR__ . '/app/configuracion/base_de_datos.php';
require_once __DIR__ . '/app/nucleo/Enrutador.php';
require_once __DIR__ . '/app/ayudantes/jwt_helper.php';
require_once __DIR__ . '/app/rutas.php';

// Manejar solicitudes preflight de CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}
